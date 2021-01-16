<?php
/**
 * Login and Password Request Handler
 *
 * Handles login and password request from web or app.
 *
 * @package Sevida
 * @subpackage Api
 */
/** Load bootstrap file and a utilities */
require( dirname(__FILE__) . '/Load.php' );
require( ABSPATH . USER_UTIL . '/LoginUtil.php' );
loginContants();
/**
 * @var string
 */
$action = request( 'action', 'client' );
/**
 * @var \Response
 */
$response = new Response();
switch( $action->action ) {
	/** Requests to recover / reset the password */
	case 'recover':
		/** Collect the request form data */
		$login = request( 'userId', 'authKey', 'password' );
		$response->setFeedBacks( [ 'password' ] );
		if( ! preg_match( REGEX_PASSWORD, $login->password) )
			$response->addMessage( 'Password too short or invalid' );
		if( ! $response->hasMessage() ) {
			$response->setFeedBack( 'password', true );
			try {
				/** Do some match, no match no recovery */
				if( $login->authKey !== findAuthKey( $login->userId ) )
					throw new Exception( 'Code mismatch, consider resending the code.' );
				/** All good, encrypt his new password and update */
				$login->password = password_hash( $login->password, PASSWORD_BCRYPT );
				$update = $db->prepare( 'UPDATE Person SET password=? WHERE id=? LIMIT 1' )->execute( [ $login->password, $login->userId ] );
				/** The auth token is called done with */
				$delete = $db->prepare( 'DELETE FROM PersonMeta WHERE metaKey=? AND userId=? LIMIT 1' )->execute( [ 'authKey', $login->userId ] );
			} catch( Exception $e ) {
				$response->addMessage( $e->getMessage() );
			}
		}
		$response->determineSuccess();
		unset($login);
		break;
	case 'lostpass':
		$login = request( 'id', 'userName' );
		$response->setFeedBacks( [ 'userName' ] );
		if( ! preg_match( REGEX_USERNAME, $login->userName) && ! preg_match( REGEX_USERNAME, $login->userName) )
			$response->addMessage( 'Invalid username or email' );
		if( ! $response->hasMessage() ) {
			try {
				// Validate the username from database
				if( ! ( $login->id = User::findId( $login->userName ) ) )
					throw new Exception( 'Username or email not found' );
				$response->setFeedBack( 'userName', true );
				// Generate randown token
				$login->mSecret = rand( 100000, 999999 );
				$login->authKey = password_hash( $login->mSecret, PASSWORD_BCRYPT );
				// Save the auth. key in database, it's more like a one time password
				$replace = $db->prepare( 'REPLACE INTO PersonMeta (userId, metaKey, metaValue) VALUES (?, ?, ?)' );
				$replace->execute( [ $login->id, 'authKey', $login->authKey ] );
				// The user comes back with this to change his password
				$login->authToken = [
					'iss' => BASE_URL,
					'aud' => BASE_URL,
					'iat' => time(),
					'uid' => $login->id,
					'aut' => $login->authKey
				];
				/**
				 * Encode the payload using JWT
				 * JWT is a third party plugin for encrypting json responses between server and client
				 */
				$login->authToken = \Firebase\JWT\JWT::encode( $login->authToken, AUTH_KEY );
				// Message to reset the password
				$login = sprintf(
					'<div class="d-block">Use this code [%s] to reset your password, or follow the click <a href="%s" class="alert-link">here</a></div>',
					$login->mSecret, BASE_URL . USERPATH . '/login.php?action=recover&token=' . urlencode($login->authToken)
				);
				// Send back the data needed
				$response->setMessage( [ $login ] );
			} catch( Exception $e ) {
				$response->addMessage( $e->getMessage() );
			}
		}
		unset($login);
		$response->determineSuccess();
		break;
	case 'login':
		// Collect the form data
		$login = request( 'userName', 'password', 'remember' );
		$login->remember = $login->remember !== null;
		$response->setFeedBacks( [ 'userName', 'password' ] );
		/**
		 * Let's do a little validation of the data to prevent database overload
		 * You know some people are really gonna try something stupid with the login form
		 * Think about DDOs
		 */
		if( ! preg_match( REGEX_USERNAME, $login->userName) && ! preg_match( REGEX_EMAILADD, $login->userName) )
			$response->addMessage( 'Invalid username or email' );
		if( ! preg_match( REGEX_PASSWORD, $login->password) )
			$response->addMessage( 'Invalid password--' );
		if( ! $response->hasMessage() ) {
			try {
				// Validate the username from database
				if( ! ( $login->id = User::findId( $login->userName ) ) )
					throw new Exception( 'Invalid username' );
				$response->setFeedBack( 'userName', true );
				// Validate the password input by the used
				if( ! matchPassword( $login->id, $login->password ) )
					throw new Exception( 'Invalid password' );
				$response->setFeedBack( 'password', true );
				// Generate an encrypted token with a regenerated the session id
				session_regenerate_id(true);
				$login->session = session_id();
				if( ! $login->session )
					throw new Exception( 'An unknown error occured. Please enable using caches and cookies in your browser settings' );
				$login->session = password_hash( $login->session, PASSWORD_BCRYPT );
				// Create a JWT payLoad for encrytion
				$login = [
					'iss' => BASE_URL,
					'aud' => BASE_URL,
					'iat' => time(),
					'uid' => $login->id,
					'sid' => $login->session
				];
				// Encrypt to a string using a key generated during setup
				$login = \Firebase\JWT\JWT::encode( $login, LOGIN_KEY );
				// Send back and log the response session level
				$response->setMessage( [ $login ] );
				$_SESSION['__LOGIN__'] = $login;
			} catch( Exception $e ) {
				$response->addMessage( $e->getMessage() );
			}
		}
		unset($login);
		$response->determineSuccess();
		break;
	default:
		/** No action matched the ones we handle */
		objectNotFound();
		exit;
}
// Finally say what happened
jsonOutput( $response );
