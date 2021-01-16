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
 * Denoted that user needs to be logged in to perform this action
 * @var bool
 */
define( 'REQUIRE_LOGIN', true );
/** Load blog bootstrap file and utilities */
require( dirname(__FILE__) . '/Load.php' );
require( ABSPATH . USER_UTIL . '/PostUtil.php' );
loadPostConstants();
/**
 * The action needed to be taken
 * @var object
 */
$action = request( 'action' );
/**
 * We we are returning to the user in json format
 * @var \Response $response
 */
$response = new Response();
/** Find what exactly we need */
switch( $action ) {
	/** We are needed to delete a post */
	case 'unlink':
		try {
			$postId = request( 'id' );
			$delete = $db->prepare( 'DELETE FROM Post WHERE id=?' );
			$delete->execute( [ $postId ] );
			$response->success = true;
		} catch( Exception $e ) {
			$response->addMessage( $e->getMessage() );
		}
		$response->determineSuccess();
		break;
	/** We are needed to create or edit a post */
	case 'modify':
	case 'create':
		/** @var object The user's form data */
		$post = request( 'title', 'permalink', 'excerpt', 'content', 'thumbnail', 'category', 'status', 'password' );
		$response->setFeedBacks( [ 'title', 'excerpt', 'content', 'password' ], true );
		if( ! preg_match( REGEX_POST_TITLE, $post->title ) ) {
			$response->setFeedBack( 'title', false );
			$response->addMessage( 'Invalid / too short title' );
		}
		if( ! $response->hasMessage() ) {
			if( ! $post->category )
				$post->category = 1;
			$post->excerpt = makeExcerpt( empty($post->excerpt) ? $post->content : $post->excerpt );
			$post->permalink = makePermalink( empty($post->permalink) ? $post->title : $post->permalink );
			if( ! in_array( $post->status, POST_STATUS_ENUM ) )
				$post->status = 'public';
			try {
				$db->beginTransaction();
				if( $action === 'modify' ) {
					$postId = request( 'id' );
					$tempId = Post::findId( $post->permalink );
					if( $tempId && $tempId != $postId )
						throw new Exception( 'Another post exits with the title' );
					$post->id = $postId;
					$post->modified = time();
					$insert = $db->prepare( 'UPDATE Post SET permalink=:permalink,title=:title,excerpt=:excerpt,content=:content,thumbnail=:thumbnail,category=:category,status=:status,modified=DATE(:modified),password=:password WHERE id=:id LIMIT 1' );
					$insert->execute( get_object_vars($post) );
				} elseif( $action === 'create' ) {
					if( Post::findId( $post->permalink ) )
						throw new Exception( 'There is a post with that title' );
					$post->posted = $post->modified = time();
					$post->author = $_login->userId;
					$post->rowType = 'post';
					$insert = $db->prepare( 'INSERT INTO Post (permalink,title,excerpt,content,thumbnail,category,author,posted,modified,status,password,rowType) VALUES (:permalink,:title,:excerpt,:content,:thumbnail,:category,:author,DATE(:posted),DATE(:modified),:status,:password,:rowType)' );
					$insert->execute( get_object_vars($post) );
					$post->id = parseInt( $db->lastInsertId() );
				}
				/** Commit attached tags to database */
				if( is_array( $labels = request( 'labels' ) ) ) {
					$labels = array_unique($labels);
					$insert = array_fill( 0, count($labels), '(?,?)' );
					$insert = implode( ', ', $insert );
					foreach( $labels as &$value )
						$value = [ $post->id, (int) $value ];
					if( isset($labels[1]) )
						$labels = call_user_func_array( 'array_merge', $labels );
					else
						$labels = $labels[0];
					$insert = $db->prepare( 'REPLACE INTO TermLink (postId, termId) VALUES ' . $insert );
					$insert->execute( $labels );
				}
				$db->commit();
			} catch(Exception $e) {
				if( $db->inTransaction() )
					$db->rollBack();
				$response->addMessage( $e->getMessage() );
			}
		}
		$response->determineSuccess();
		break;
	default:
		die();
}
jsonOutput( $response );
