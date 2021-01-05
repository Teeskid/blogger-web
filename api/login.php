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
require( dirname(__FILE__) . '/Load.php' );
require( ABSPATH . USER_UTIL . '/LoginUtil.php' );

loginContants();

$action = request( 'action', 'client' );
$response = [ 'success' => false ];
switch( $action->action ) {
	case 'recover':
		$login = request( 'userId', 'authKey', 'password' );
		$valid = [ 'password' => false ];
		$error = '';
		if( ! preg_match( REGEX_PASSWORD, $login->password) )
			$error = 'Password too short or invalid';
		else
			$valid['password'] = true;
		if( empty($error) )
		try {
			$authKey = $db->prepare( 'SELECT metaValue FROM PersonMeta WHERE metaKey=? AND personId=? LIMIT 1' );
			$authKey->execute( [ 'authKey', $login->userId ] );
			$authKey = $authKey->fetchColumn();
			if( $authKey !== $login->authKey )
				throw new Exception( 'Code mismatch, consider resending the code.' );
			
			$login->password = password_hash( $login->password, PASSWORD_BCRYPT );
			
			$delete = $db->prepare( 'DELETE FROM PersonMeta WHERE metaKey=? AND personId=? LIMIT 1' )->execute( [ 'authKey', $login->userId ] );
			$update = $db->prepare( 'UPDATE Person SET password=? WHERE id=? LIMIT 1' )->execute( [ $login->password, $login->userId ] );
		} catch( Exception $e ) {
			$error = $e->getMessage();
		}
		$response['uiValid'] = $valid;
		if( empty($error) ) {
			$response['success'] = true;
			$response['message'] = 'Reset success';
		} else {
			$response['message'] = $error;
		}
		unset( $login, $valid, $error );
		break;
	case 'lostpass':
		$login = request( 'id', 'userName' );
		$valid = [ 'userName' => false ];
		$error = '';
		if( ! preg_match( REGEX_USERNAME, $login->userName) && ! preg_match( REGEX_USERNAME, $login->userName) )
			$error = 'Invalid username or email';
		if( empty($error) )
		try {
			$login->id = User::findId( $login->userName );
			if( $login->id )
				$valid['userName'] = true;
			else
				throw new Exception( 'Username or email not found' );
			$authKey = rand( 100000, 999999 );
			$authKey = password_hash( $authKey, PASSWORD_DEFAULT );
			$payLoad = [
				"iss" => BASE_URL,
				"aud" => BASE_URL,
				"iat" => time(),
				"nbf" => time(),
				'uid' => $login->id,
				'cod' => $authKey
			];
			$payLoad = \Firebase\JWT\JWT::encode( $payLoad, AUTH_SALT );
			$replace = $db->prepare( 'REPLACE INTO PersonMeta (personId, metaKey, metaValue) VALUES (?, ?, ?)' );
			$replace->execute( [ $login->id, 'authKey', $authKey ] );
			$payLoad = BASE_URL . USERPATH . '/login.php?action=recover&token=' . urlencode($payLoad);
			$response['resLink'] = $payLoad;
		} catch( Exception $e ) {
			$error = $e->getMessage();
		}
		$response['uiValid'] = $valid;
		if( empty($error) ) {
			$response['success'] = true;
		} else {
			$response['message'] = $error;
		}
		break;
	case 'login':
		$login = request( 'userName', 'password', 'remember' );
		$login->remember = isset( $login->remember );
		$error = [];
		$valid = array_fill_keys( [ 'userName', 'password' ], false );
		if( ! preg_match( REGEX_USERNAME, $login->userName) && ! preg_match( REGEX_USERNAME, $login->userName) )
			$error[] = 'Invalid username or email';
		if( ! preg_match( REGEX_PASSWORD, $login->password) )
			$error[] = 'Invalid password';
		if( empty($error) )
		try {
			if( $login->id = User::findId( $login->userName ) )
				$valid['userName'] = true;
			else
				throw new Exception( 'The username you provided was not found' );
			if( matchPassword( $login->id, $login->password ) )
				$valid['password'] = true;
			else
				throw new Exception( 'The password you provided is incorrect' );
			$payLoad = password_hash( session_id(), PASSWORD_DEFAULT );
			$payLoad = [
				"iss" => BASE_URL,
				"aud" => BASE_URL,
				"iat" => time(),
				"nbf" => time(),
				'uid' => $login->id,
				'sid' => $payLoad
			];
			$payLoad = \Firebase\JWT\JWT::encode( $payLoad, LOGGED_SALT );
			$response['session'] = $payLoad;
			$_SESSION['__LOGIN__'] = $payLoad;
			unset($payLoad);
		} catch( Exception $e ) {
			$error[] = $e->getMessage();
		}
		$response['uiValid'] = $valid;
		if( empty($error) ) {
			$response['success'] = true;
		} else {
			$response['message'] = implode( PHP_EOL, $error );
		}
		unset( $login, $valid, $error );
		break;
	default:
		objectNotFound();
		exit;
}
jsonOutput( $response );
