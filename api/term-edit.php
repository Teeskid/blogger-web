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
 * @var bool
 */
define( 'REQUIRE_LOGIN', true );

/** Load blog bootstrap files */
require( __DIR__ . '/Load.php' );
require( ABSPATH . USER_UTIL . '/TermUtil.php' );
// Initialize a response
$json = new Json();
$action = request( 'action' );
switch( $action ) {
	case 'unlink':
		try {
			$_db->beginTransaction();
			$delete = $_db->prepare( 'DELETE FROM Term WHERE id=? LIMIT 1' );
			$delete->execute( [ $action->id ] );
			$_db->commit();
		} catch( Exception $e ) {
			if( $_db->inTransaction() )
				$_db->rollBack();
			$errors[] = $e->getMessage();
		}
		break;
	case 'modify':
	case 'create':
		$term = request( 'term', 'title', 'permalink', 'about', 'rowType' );
		$json->setFeedBacks( [ 'title', 'permalink', 'about', 'rowType', 'term' ], true );
		if( 3 > strlen($term->title) ) {
			$json->addMessage('Invalid title');
			$json->setFeedBack( 'title', false );
		}
		if( ! $json->hasMessage() ) {
			$term->permalink = makePermalink( empty($term->permalink) ? $term->title : $term->permalink );
			try {
				$_db->beginTransaction();
				if( $action === 'modify' ) {
					$termId = (int) request( 'id' );
					if( ! termExists( $termId ) )
						throw new Exception( 'Object not found.' );
					$term->id = $termId;
					$term = get_object_vars($term);
					$update = $_db->prepare( 'UPDATE Term SET term=:term, title=:title, permalink=:permalink, rowType=:rowType, about=:about WHERE id=:id' );
					$update->execute( $term );
				} else {
					if( Term::findId( $term->permalink ) )
						throw new Exception( 'Duplicate term permalink.' );
					$insert = $_db->prepare( 'INSERT INTO Term (term, title, permalink, rowType, about) VALUES (:term, :title, :permalink, :rowType, :about)' );
					$insert->execute( get_object_vars($term) );
					$term->id = $_db->lastInsertId();
					$json->success = true;
				} 
				$json->setMessage( [ $term ] );
				$_db->commit();
			} catch( Exception $e ) {
				if( $_db->inTransaction() )
					$_db->rollBack();
				$json->addMessage( $e->getMessage() );
			}
		}
		break;
	default:
		die();
}
closeJson( $json );
