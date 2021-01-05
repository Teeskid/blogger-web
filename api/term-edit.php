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
require( ABSPATH . USER_UTIL . '/TermUtil.php' );

$errors = [];
$response = [];

$action = request( 'action', 'id', 'subject' );
switch( $action->action ) {
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
		$term = request( 'master', 'title', 'permalink', 'about' );
		if( 3 > strlen($term->title) ) {

		} 
		$term->subject = $action->subject;
		$term->permalink = makePermalink( $term->title );
		try {
			$db->beginTransaction();
			if( $action->action === 'modify' ) {
				if( ! termExists( $action->id ) )
					throw new Exception( 'Object not found.' );
				$insert = $db->prepare( 'UPDATE Term SET master=?, title=?, permalink=?, subject=?, about=? WHERE id=?' );
				$insert->execute( [ $term->master, $term->title, $term->permalink, $term->subject, $term->about, $action->id ] );
			} else {
				if( Term::findId( $term->permalink ) )
					throw new Exception( 'Duplicate term permalink.' );
				$insert = $db->prepare( 'INSERT INTO Term (master, title, permalink, subject, about) VALUES (:master, :title, :permalink, :subject, :about)' );
				$insert->execute( get_object_vars($term) );
				$term->id = $db->lastInsertId();
				$response['id'] = $term->id;
			} 
			$db->commit();
		} catch( Exception $e ) {
			if( $db->inTransaction() )
				$db->rollBack();
			$errors[] = $e->getMessage();
		}
		break;
	default:
		die();
}
if( isset($errors[0]) ) {
	$response['success'] = false;
	$response['message'] = implode( ', ', $errors );
} else {
	$response['success'] = true;
	$response['redirect'] = 'term.php?subject=' . $action->subject;
}
jsonOutput( $response );
