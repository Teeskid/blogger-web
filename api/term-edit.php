<?php
/**
 * Term Management Api
 * 
 * Handles request to add, edit or delete term in database
 * 
 * @package Sevida
 * @subpackage Api
 */
/**
 * Denoted that user needs to be logged in to perform this action
 * @var bool
 */
define( 'REQUIRE_LOGIN', true );

/** Load blog bootstrap files */
require( dirname(__FILE__) . '/Load.php' );
require( ABSPATH . USER_UTIL . '/TermUtil.php' );
// Initialize a response
$response = new Response();
$action = request( 'action' );
switch( $action ) {
	case 'unlink':
		try {
			$db->beginTransaction();
			$delete = $db->prepare( 'DELETE FROM Term WHERE id=? LIMIT 1' );
			$delete->execute( [ $action->id ] );
			$db->commit();
		} catch( Exception $e ) {
			if( $db->inTransaction() )
				$db->rollBack();
			$errors[] = $e->getMessage();
		}
		break;
	case 'modify':
	case 'create':
		$term = request( 'master', 'title', 'permalink', 'about', 'rowType' );
		$response->setFeedBacks( [ 'title', 'permalink', 'about', 'rowType', 'master' ], true );
		if( 3 > strlen($term->title) ) {
			$response->addMessage('Invalid title');
			$response->setFeedBack( 'title', false );
		}
		if( ! $response->hasMessage() ) {
			$term->permalink = makePermalink( empty($term->permalink) ? $term->title : $term->permalink );
			try {
				$db->beginTransaction();
				if( $action === 'modify' ) {
					$termId = (int) request( 'id' );
					if( ! termExists( $termId ) )
						throw new Exception( 'Object not found.' );
					$term->id = $termId;
					$term = get_object_vars($term);
					$update = $db->prepare( 'UPDATE Term SET master=:master, title=:title, permalink=:permalink, rowType=:rowType, about=:about WHERE id=:id' );
					$update->execute( $term );
				} else {
					if( Term::findId( $term->permalink ) )
						throw new Exception( 'Duplicate term permalink.' );
					$insert = $db->prepare( 'INSERT INTO Term (master, title, permalink, rowType, about) VALUES (:master, :title, :permalink, :rowType, :about)' );
					$insert->execute( get_object_vars($term) );
					$term->id = $db->lastInsertId();
					$response->success = true;
				} 
				$response->setMessage( [ $term ] );
				$db->commit();
			} catch( Exception $e ) {
				if( $db->inTransaction() )
					$db->rollBack();
				$response->addMessage( $e->getMessage() );
			}
		}
		break;
	default:
		die();
}
jsonOutput( $response );
