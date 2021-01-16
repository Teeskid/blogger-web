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
require( dirname(__FILE__) . '/Load.php' );

$response = new Response();
$action = request( 'action' );

switch( $action ) {
	case 'tools':
		$option = request( 'minify', 'sitemap' );
		if( $option->minify ) {
			require( ABSPATH . USER_UTIL . '/ResUtil.php' );
			minifyFiles('js');
			minifyFiles('css');
		}
		if( $option->sitemap ) {
			set_error_handler(function( $errCode, $errText ) use(&$errors) {
				throw new Exception();
			});
			try {
				$ping = fopen( 'https://google.com/sitemap?url=' . rawurlencode( BASE_URL . '/sitemap.xml' ), 'r' );
				$ping = $ping && fgetc($ping);
				if( ! $ping )
					throw new Exception();
			} catch( Exception $e ) {
				$errors[] = 'Sitemap failed, network error.';
			}
		}
		break;
	case 'modify':
		$options = request( 'blogName', 'blogDesc', 'blogDate', 'permalink', 'blogEmail', 'searchable' );
		$options->searchable = json_encode( $options->searchable === "on" );
		$response->setFeedBacks( [ 'blogName', 'blogEmail', 'blogDesc', 'blogDate', 'permalink' ], true );
		if( ! preg_match( '#^[\w\d\s_-]{5,15}$#', $options->blogName ) ) {
			$response->setFeedBack( 'blogName', false );
			$response->addMessage( 'invalid blog name' );
		}
		if( ! testEmail( $options->blogEmail ) ) {
			$response->setFeedBack( 'blogEmail', false );
			$response->addMessage( 'invalid email address' );
		}
		if( ! $response->hasMessage() ) {
			try {
				$options = get_object_vars($options);
				$holders = [];
				foreach( $options as $index => $entry )
					array_push( $holders, '(' . $db->quote( $index ) . ', :' . $index . ')' );
				$holders = implode( ',', $holders );
				$db->beginTransaction();
				$replace = $db->prepare( 'REPLACE INTO Config (metaKey, metaValue) VALUES ' . $holders );
				$replace->execute( $options );
				$db->commit();
			} catch(Exception $e) {
				if( $db->inTransaction() )
					$db->rollBack();
				$response->addMessage( $e->getMessage() );
			}
		}
		$response->determineSuccess();
		break;
	case 'resets':
		require( ABSPATH . USER_UTIL . '/LoginUtil.php' );
		require( ABSPATH . USER_UTIL . '/SetupUtil.php' );
		require( ABSPATH . USER_UTIL . '/InstallUtil.php' );
		$options = request( 'noFiles', 'password' );
		$options->noFiles = ! empty( $options->noFiles );
		$response->setFeedBacks( [ 'password' ], false );
		try {
			if( ! matchPassword( $_login->userId, $options->password ) ) {
				$response->setFeedBack( "password", false );
				throw new Exception( 'incorrect password' );
			}
			dropAllTables();
			unlinkUserFiles( ! $options->noFiles );
			session_destroy();
		} catch( Exception $e ) {
			if( $db->inTransaction() )
				$db->rollBack();
			$response->addMessage( $e->getMessage() );
		}
		$response->determineSuccess();
		break;
	default:
		die();
}
jsonOutput( $response );
