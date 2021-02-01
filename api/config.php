<?php
/**
 * Configuration Requests Handler
 *
 * Handles requests to configure the blog
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
require( ABSPATH . USER_UTIL . '/LoginUtil.php' );

$payLoad = getPayLoad();
fillPayLoad( $payLoad, 'action' );
$json = new Json();

switch( $payLoad->action ) {
	case 'modify':
		fillPayLoad( $payLoad, 'blogName', 'blogEmail', 'blogDesc', 'blogDate', 'permalink', 'searchable' );
		if( ! preg_match( Config::REGEX_USERNAME, $payLoad->blogName ) ) {
			$json->setFeedBack( 'blogName', false );
			$json->addMessage( 'invalid blog name' );
		}
		if( ! preg_match( User::REGEX_EMAIL, $payLoad->blogEmail ) ) {
			$json->setFeedBack( 'blogEmail', false );
			$json->addMessage( 'invalid email address' );
		}
		if( ! $json->hasMessage() ) {
			try {
				$_db->beginTransaction();
				$replace = $_db->prepare( 'REPLACE INTO Config (metaKey, metaValue) VALUES (?,?),(?,?),(?,?),(?,?),(?,?),(?,?)' );
				$replace->execute( [
					'blogName', $payLoad->blogName,
					'blogEmail', $payLoad->blogEmail,
					'blogDesc', $payLoad->blogDesc,
					'blogDate', $payLoad->blogDate,
					'permalink', $payLoad->permalink,
					'searchable', $payLoad->searchable,
				] );
				$_db->commit();
				$json->setSuccess();
				$json->setMessage("Success");
			} catch(Exception $e) {
				if( $_db->inTransaction() )
					$_db->rollBack();
				$json->addMessage( $e->getMessage() );
			}
		}
		break;
	case 'reset':
		require( ABSPATH . USER_UTIL . '/SetupUtil.php' );
		require( ABSPATH . USER_UTIL . '/InstallUtil.php' );
		fillPayLoad( $payLoad, 'noFiles', 'password' );
		$payLoad->noFiles = ! empty($payLoad->noFiles);
		try {
			if( ! matchPassword( $_usr->id, $payLoad->password ) ) {
				$json->setFeedBack( "password", false );
				throw new Exception( 'incorrect password' );
			}
			dropAllTables();
			unlinkUserFiles( ! $payLoad->noFiles );
			if( session_id() )
				session_destroy();
			$json->setSuccess();
		} catch( Exception $e ) {
			if( $_db->inTransaction() )
				$_db->rollBack();
			$json->setMessage( $e->getMessage() );
		}
		break;
}
closeJson( $json );
