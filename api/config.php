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

$errors = [];
$action = request('action');
switch( $action ) {
	case 'tools':
		$options = request( 'minify', 'sitemap' );
		if( $options->minify ) {
			require( ABSPATH . USER_UTIL . '/ResUtil.php' );
			minifyFiles('js');
			minifyFiles('css');
		}
		if( $options->sitemap ) {
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
		try {
			$db->beginTransaction();
			$replace = $db->prepare( 'REPLACE INTO Config (metaKey, metaValue) VALUES (?,?),(?,?),(?,?),(?,?),(?,?),(?,?)' );
			$replace->execute([
				'blogName', $options->blogName,
				'blogDesc', $options->blogDesc,
				'blogDate', $options->blogDate,
				'permalink', $options->permalink,
				'blogEmail', $options->blogEmail,
				'searchable', $options->searchable
			]);
			$db->commit();
			$response['message'] = 'Changes Saved';
		} catch(Exception $e) {
			if( $db->inTransaction() )
				$db->rollBack();
			$errors[] = $e->getMessage();
		}
		break;
	case 'reset':
		require( ABSPATH . USER_UTIL . '/LoginUtil.php' );
		require( ABSPATH . USER_UTIL . '/ConfigUtil.php' );
		require( ABSPATH . USER_UTIL . '/InstallUtil.php' );
		$options = request( 'noFiles', 'password' );
		$options->noFiles = ! empty( $options->noFiles );
		try {
			if( ! matchPassword( $_login->userId, $options->password ) )
				throw new Exception( 'Your password is incorrect.' );
			dropTables();
			unlinkUserFiles( ! $options->noFiles );
			session_destroy();
		} catch( Exception $e ) {
			$errors[] = $e->getMessage();
		}
		break;
	default:
		die();
}
$response = [];
if( isset($errors[0]) ){
	$response['success'] = false;
	$response['message'] = implode( ', ', $errors );
	$response['redirect'] = '';
} else {
	$response['success'] = true;
	$response['message'] = 'Done successfully';
}
jsonOutput( $response );