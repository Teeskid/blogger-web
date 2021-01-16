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

if( ! LOGGED_IN )
	die();

$errors = [];
$response = [];

$action = request( 'action', 'id' );
$action->id = (int) $action->id;
switch( $action->action ) {
	case 'unlink':
		$delete = $db->prepare( 'DELETE FROM Post WHERE id=?' );
		$delete->execute( [ $action->id ] );
		break;
	case 'modify':
	case 'create':
		$page = request( 'permalink', 'title', 'content', 'status', 'password' );
		if( ! $page->status )
			$page->status = 'public';
		if( ! $page->password )
			$page->password = null;
		$page->permalink = $page->permalink ?? makePermalink( $page->title );
		try {
			$db->beginTransaction();
			$tempId = Post::findId( $page->permalink );
			if( $action->action === 'modify' ) {
				if( $tempId && $tempId !== $action->id )
					throw new Exception('Another post exits with the title');
				$page->id = $action->id;
				$insert = $db->prepare( 'UPDATE Post SET permalink=:permalink,title=:title,content=:content,status=:status,password=:password,modified=DEFAULT WHERE id=:id LIMIT 1' );
				$insert->execute( get_object_vars($page) );
			} elseif( $action->action === 'create' ) {
				if( $tempId )
					throw new Exception('Duplicate title or permalink.');
				$page->author = $_login->userId;
				$page->rowType = 'page';
				$insert = $db->prepare( 'INSERT INTO Post (permalink,title,content,author,posted,modified,status,password,rowType) VALUES (:permalink,:title,:content,:author,DEFAULT,DEFAULT,:status,:password,:rowType)' );
				$insert->execute( get_object_vars($page) );
				$page->id = $db->lastInsertId();
			}
			$values = request( 'meta' ) ?? [];
			$values = array_unique($values);
			if( ! empty($values) ) {
				$insert = array_fill( 0, count($values), '(?,?,?)' );
				$insert = implode( ',', $insert );
				foreach( $values AS $index => &$entry )
					$entry = [ $page->id, $index, $entry ];
				$values = array_values($values);
				if( isset($values[1]) )
					$values = call_user_func_array( 'array_merge', $values );
				else
					$values = $values[0];
				$insert = $db->prepare( 'REPLACE INTO PostMeta (postId, metaKey, metaValue) VALUES ' . $insert );
				$insert->execute( $values );
			}
			$db->commit();
		} catch(Exception $e) {
			if( $db->inTransaction() )
				$db->rollBack();
			$errors[] =  $e->getMessage();
		}
	break;
	default:
		die();
}
if( isset($errors[0]) ) {
	$response['success'] = false;
	$response['message'] = implode( PHP_EOL, $errors );
} else {
	$response['success'] = true;
	$response['redirect'] = 'page.php';
}
jsonOutput( $response );