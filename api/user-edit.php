<?php
/**
 * User Management Api
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
require( ABSPATH . USER_UTIL . '/LoginUtil.php' );

// Initialize a response
$json = new Json();
$payLoad = getPayLoad();
fillPayLoad( $payLoad, 'action' );
switch( $payLoad->action ) {
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
	case 'setname':
		fillPayLoad( $payLoad, 'userName' );
		if( ! preg_match( User::REGEX_USERNAME, $payLoad->userName ) ) {
			$json->setFeedBack( 'userName', false );
			$json->addMessage( 'Username is invalid' );
		}
		if( ! $json->hasMessage() ) {
			try {
				$_db->beginTransaction();
				$update = $_db->prepare( 'UPDATE Uzer SET userName=? WHERE id=?' );
				$update = $update->execute( [ $payLoad->userName, $_usr->id ] );
				if( ! $update )
					throw new Exception( 'Username might have been set already' );
				$_db->commit();
				$json->setSuccess();
			} catch( Exception $e ) {
				if( $_db->inTransaction() )
					$_db->rollBack();
				$json->addMessage( $e->getMessage() );
			}
		}
		break;
	case 'modify':
	case 'create':
		$userId = @queryUserId();
		$profile = request( 'first_name','last_name','nick_name','social_fb','social_tw','bio_info','mobile_no','password' );
		if( ! empty( $profile->password ) ) {
			$profile->password = encryptPassword( $profile->password );
			$my_query = $_db->prepare( 'UPDATE users SET password=? WHERE id=?' );
			$my_query->execute( [ $profile->password, $userId ] );
		}
		$my_query = $_db->prepare( 'REPLACE INTO UzerMeta (userId,metaKey,val) VALUES (?,?,?),(?,?,?),(?,?,?),(?,?,?),(?,?,?),(?,?,?),(?,?,?)' );
		$my_query->execute( [
			$userId, 'first_name', $profile->first_name,
			$userId, 'last_name', $profile->last_name,
			$userId, 'nick_name', $profile->nick_name,
			$userId, 'bio_info', $profile->bio_info,
			$userId, 'mobile_no', $profile->mobile_no,
			$userId, 'social_fb', $profile->social_fb,
			$userId, 'social_tw', $profile->social_tw
		] );
		try {
			$stmt = $_db->prepare( 'UPDATE users SET email=? WHERE id=?' );
			$stmt->execute( [ $profile->email, $userId ] );
		}
		catch( Exception $e ) {
			$error[] = $e->getMessage();
		}
		if( isset($errors[0]) ) {
		}



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
