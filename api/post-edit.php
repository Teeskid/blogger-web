<?php
/**
 * Post Management Api
 * 
 * Handles requests to create, edit and delete posts
 * 
 * @package Sevida
 * @subpackage Api
 */
/**
 * @var bool
 */
define( 'REQUIRE_LOGIN', true );

/** Load blog bootstrap file and utilities */
require( __DIR__ . '/Load.php' );
require( ABSPATH . USER_UTIL . '/PostUtil.php' );

loadPostConstants();

$payLoad = getPayLoad();
fillPayLoad( $payLoad, 'action' );
$json = new Json();
switch( $payLoad->action ) {
	/** We are needed to delete a post */
	case 'unlink':
		try {
			$postId = request( 'id' );
			$delete = $_db->prepare( 'DELETE FROM Post WHERE id=?' );
			$delete->execute( [ $postId ] );
			$json->success = true;
		} catch( Exception $e ) {
			$json->addMessage( $e->getMessage() );
		}
		$json->determineSuccess();
		break;
	/** We are needed to create or edit a post */
	case 'modify':
	case 'create':
		fillPayLoad( $payLoad, 'id', 'title', 'permalink', 'excerpt', 'content', 'thumbnail', 'category', 'labels', 'status', 'password' );
		if( ! preg_match( REGEX_POST_TITLE, $payLoad->title ) ) {
			$json->setFeedBack( 'title', false );
			$json->addMessage( 'Invalid / too short title' );
		}
		if( 60 > strlen($payLoad->content) ) {
			$json->setFeedBack( 'content', false );
			$json->addMessage( 'Content too scanty' );
		}
		if( ! $json->hasMessage() ) {
			$payLoad->category = parseInt( $payLoad->category ? $payLoad->category : 1 );
			$payLoad->excerpt = makeExcerpt( empty($payLoad->excerpt) ? $payLoad->content : $payLoad->excerpt );
			$payLoad->permalink = makePermalink( empty($payLoad->permalink) ? $payLoad->title : $payLoad->permalink );
			if( ! in_array( $payLoad->status, POST_STATUS_ENUM ) )
				$payLoad->status = 'public';
			try {
				$_db->beginTransaction();
				if( $payLoad->action === 'modify' ) {
					$tempId = Post::findId( $payLoad->permalink );
					if( $tempId && $tempId !== $payLoad->id )
						throw new Exception( 'Another post exits with the title' );
					$payLoad->lastEdited = time();
					$update = $_db->prepare( 'UPDATE Post SET permalink=?,title=?,excerpt=?,content=?,thumbnail=?,category=?,status=?,lastEdited=DATE(?),password=? WHERE id=? LIMIT 1' );
					$update->execute( [
						$payLoad->permalink,
						$payLoad->title,
						$payLoad->excerpt,
						$payLoad->content,
						$payLoad->thumbnail,
						$payLoad->category,
						$payLoad->status,
						$payLoad->lastEdited,
						$payLoad->password,
						$payLoad->id,
					] );
				} elseif( $payLoad->action === 'create' ) {
					if( Post::findId( $payLoad->permalink ) )
						throw new Exception( 'There is a post with the title' );
					$payLoad->datePosted = $payLoad->lastEdited = time();
					$payLoad->author = $_usr->id;
					$payLoad->rowType = 'post';
					$insert = $_db->prepare( 'INSERT INTO Post (permalink,title,excerpt,content,thumbnail,category,status,author,datePosted,lastEdited,password,rowType) VALUES (?,?,?,?,?,?,?,DATE(?),DATE(?),?,?,?)' );
					$insert->execute( [
						$payLoad->permalink,
						$payLoad->title,
						$payLoad->excerpt,
						$payLoad->content,
						$payLoad->thumbnail,
						$payLoad->category,
						$payLoad->status,
						$payLoad->author,
						$payLoad->datePosted,
						$payLoad->lastEdited,
						$payLoad->password,
						$payLoad->rowType,
					] );
					$payLoad->id = parseInt( $_db->lastInsertId() );
				}
				/** Commit attached tags to database */
				if( is_array($payLoad->labels) ) {
					$payLoad->labels = array_unique($payLoad->labels);
					$insert = array_fill( 0, count($payLoad->labels), '(?,?)' );
					$insert = implode( ', ', $insert );
					foreach( $payLoad->labels as &$value )
						$value = [ $payLoad->id, (int) $value ];
					if( isset($payLoad->labels[1]) )
						$payLoad->labels = call_user_func_array( 'array_merge', $payLoad->labels );
					else
						$payLoad->labels = $payLoad->labels[0];
					$insert = $_db->prepare( 'REPLACE INTO TermLink (postId, termId) VALUES ' . $insert );
					$insert->execute( $payLoad->labels );
				}
				$_db->commit();
			} catch(Exception $e) {
				if( $_db->inTransaction() )
					$_db->rollBack();
				$json->addMessage( $e->getMessage() );
			}
		}
		$json->determineSuccess();
		break;
	default:
		die();
}
closeJson( $json );
