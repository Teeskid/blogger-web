<?php
/**
 * Project: Blog Management System With Sevida-Like UI
 * Developed By: Ahmad Tukur Jikamshi
 *
 * @facebook: amaedyteeskid
 * @twitter: amaedyteeskid
 * @instagram: amaedyteeskid
 * @whatsapp: +2348145737179
 */
define( 'REQUIRE_LOGIN', true );
require( dirname(__FILE__) . '/Load.php' );
require( ABSPATH . USER_UTIL . '/PostUtil.php' );

$action = request( 'action', 'id' );
switch( $action->action ) {
	case 'unlink':
		$delete = $db->prepare( 'DELETE FROM Post WHERE id=?' );
		$delete->execute( [ $action->id ] );
		redirect( 'post.php?post-deleted' );
		break;
	case 'modify':
	case 'create':
		$post = request( 'title', 'permalink', 'excerpt', 'content', 'thumbnail', 'category', 'status', 'password' );
		$messages = $elements = [];
		try {
			if( ! $post->title ) {
				$elements['title'] = false;
				throw new Exception( 'Invalid / too short title' );
			}
			if( ! $post->permalink )
				$post->permalink = $post->title;
			if( ! $post->category )
				$post->category = 1;
			if( ! $post->excerpt )
				$post->excerpt = makeExcerpt( $post->content ?? '' );
			if( ! $post->status )
				$post->status = 'public';
			$post->permalink = makePermalink( $post->permalink );
			$db->beginTransaction();
			if( $action->action === 'modify' ) {
				$tempId = Post::findId( $post->permalink );
				if( $tempId && $tempId != $action->id )
					throw new Exception( 'Another post exits with the title' );
				$post->id = $action->id;
				$post->modified = time();
				$insert = $db->prepare( 'UPDATE Post SET permalink=:permalink,title=:title,excerpt=:excerpt,content=:content,thumbnail=:thumbnail,category=:category,status=:status,modified=DATE(:modified),password=:password WHERE id=:id LIMIT 1' );
				$insert->execute( get_object_vars($post) );
			} else if( $action->action === 'create' ) {
				if( Post::findId( $post->permalink ) )
					throw new Exception( 'There is a post with that title' );
				$post->posted = $post->modified = time();
				$post->author = $_login->userId;
				$post->subject = 'post';
				$insert = $db->prepare( 'INSERT INTO Post (permalink,title,excerpt,content,thumbnail,category,author,posted,modified,status,password,subject) VALUES (:permalink,:title,:excerpt,:content,:thumbnail,:category,:author,DATE(:posted),DATE(:modified),:status,:password,:subject)' );
				$insert->execute( get_object_vars($post) );
				$post->id = parseInt( $db->lastInsertId() );
			}
			$values = request( 'labels' );
			if( is_array($values) ) {
				$values = array_unique($values);
				$insert = array_fill( 0, count($values), '(?,?)' );
				$insert = implode( ', ', $insert );
				foreach( $values AS &$value )
					$value = [ $post->id, (int) $value ];
				if( isset($values[1]) )
					$values = call_user_func_array( 'array_merge', $values );
				else
					$values = $values[0];
				$insert = $db->prepare( 'REPLACE INTO TermLink (postId, termId) VALUES ' . $insert );
				$insert->execute( $values );
			}
			$db->commit();
		} catch(Exception $e) {
			if( $db->inTransaction() )
				$db->rollBack();
			$messages[] =  $e->getMessage();
		}
		$response = [ 'uiValid' => $elements ];
		if( isset($messages[0]) ) {
			$response['success'] = false;
			$response['message'] = implode( PHP_EOL, $messages );
		} else {
			$response['success'] = true;
		}
		unset( $messages, $elements );
		jsonOutput( $response );
		break;
	default:
		die();
}
