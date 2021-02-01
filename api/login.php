<?php
/**
 * Login and Password Request Handler
 * @package Sevida
 * @subpackage Api
 */
/** Load bootstrap file and a utilities */
require( __DIR__ . '/Load.php' );
require( ABSPATH . USER_UTIL . '/LoginUtil.php' );

$payLoad = getPayLoad();
fillPayLoad( $payLoad, 'action', 'client' );
$json = new Json();
switch( $payLoad->action ) {
	case 'recover':
		fillPayLoad( $payLoad, 'userId', 'authCode', 'password' );
		$payLoad->userId = (int) $payLoad->userId;
		$payLoad->authCode = (int) $payLoad->authCode;
		if( ! preg_match( '#^[0-9]{6}$#', $payLoad->authCode ) )
			$json->addMessage( 'invalid authorization code' );
		if( ! preg_match( User::REGEX_PASSWORD, $payLoad->password) ) {
			$json->setFeedBack( 'password', false );
			$json->addMessage( 'password too short or invalid' );
		}
		if( ! $json->hasMessage() ) {
			try {
				/**
				 * Fetch authentication key
				 */
				$userToken = $_db->prepare( 'SELECT metaKey, metaValue FROM UzerMeta WHERE userId=? AND metaKey IN(?,?) LIMIT 2' );
				$userToken->execute( [ $payLoad->userId, 'authCode', 'authTime' ] );
				if( 2 !== $userToken->rowCount() )
					throw new Exception( 'Token mismatched' );
				$userToken = $userToken->fetchAll( PDO::FETCH_KEY_PAIR );
				$userToken = (object) $userToken; 
				$userToken->authTime = (int) $userToken->authTime;
				$userToken->authCode = (int) $userToken->authCode;
				if( $userToken->authCode !== $payLoad->authCode )
					throw new Exception( 'You are using an expired link to reset an account password. Please consider resending new link.' );
				/** Commit new password to database */
				$update = $_db->prepare( 'UPDATE Uzer SET password=? WHERE id=? LIMIT 1' );
				$update->execute( [ password_hash( $payLoad->password, PASSWORD_BCRYPT ), $payLoad->userId ] );
				/** Clear old token to avoid re-use */
				$delete = $_db->prepare( 'DELETE FROM UzerMeta WHERE metaKey=? AND userId=? LIMIT 1' );
				$delete->execute( [ 'authToken', $payLoad->userId ] );
				// Pretty done
				$json->setFeedBack( 'password', true );
				$json->setSuccess(true);
			} catch( Exception $e ) {
				$json->addMessage( $e->getMessage() );
			}
		}
		break;
	case 'lostpass':
		fillPayLoad( $payLoad,'userName', 'notRobot' );
		if( ! preg_match( User::REGEX_USERNAME, $payLoad->userName ) && ! preg_match( User::REGEX_EMAIL, $payLoad->userName ) ) {
			$json->setFeedBack( 'userName', false );
			$json->addMessage( 'invalid username or email' );
		}
		if( ! $payLoad->notRobot ) {
			$json->setFeedBack( 'notRobot', false );
			$json->addMessage( 'please fill the captcha correctly' );
		} else {
			$json->setFeedBack( 'notRobot', true );
		}
		if( ! $json->hasMessage() ) {
			try {
				/** Validate User  */
				$userInfo = $_db->prepare( 'SELECT id, fullName, email, userName FROM Uzer WHERE userName=:checkValue OR email=:checkValue LIMIT 1' );
				$userInfo->execute( [ 'checkValue' => $payLoad->userName ] );
				if( 0 === $userInfo->rowCount() ) {
					$json->setFeedBack( 'userName', false );
					throw new Exception( 'username or email not found' );
				}
				$json->setFeedBack( 'userName', true );
				$userInfo = $userInfo->fetch();
				/**
				 * Fetch authentication key
				 */
				$userToken = $_db->prepare( 'SELECT metaKey, metaValue FROM UzerMeta WHERE userId=? AND metaKey IN(?,?) LIMIT 2' );
				$userToken->execute( [ $userInfo->id, 'authCode', 'authTime' ] );
				if( 2 === $userToken->rowCount() ) {
					$userToken = $userToken->fetchAll( PDO::FETCH_KEY_PAIR );
					$userToken = (object) $userToken; 
					$userToken->authTime = (int) $userToken->authTime;
					$userToken->authCode = (int) $userToken->authCode;
					if( 300 > ( time() - $userToken->authTime ) )
						$userToken = $userToken->authCode;
					else
						$userToken = false;
				} else {
					$userToken = false;
				}
				/** Needs to generate new randown token */
				if( false === $userToken )
					$userToken = rand( 100000, 999999 );
				$userInfo->authCode = $userToken;
				$userInfo->authTime = time();
				$userToken = [
					'iss' => ROOTURL,
					'aud' => ROOTURL,
					'iat' => $userInfo->authTime,
					'aut' => $userInfo->authCode,
					'userId' => $userInfo->id,
				];
				/** Commit auth key to database */
				$replace = $_db->prepare( 'REPLACE INTO UzerMeta (userId, metaKey, metaValue) VALUES (?,?,?),(?,?,?)' );
				$replace->execute( [
					$userInfo->id, 'authCode', $userInfo->authCode,
					$userInfo->id, 'authTime', $userInfo->authTime,
				] );
				/** Encode the payload with JWT */
				$jwtToken = \Firebase\JWT\JWT::encode( $userToken, AUTH_KEY );
				$jwtToken = urlencode($jwtToken);
				$jwtToken = ROOTURL . USERURI . '/login.php?action=recover&token=' . $jwtToken;
				// -> mail() the user with the token
				$response = sprintf(
					'<p class="d-block">Follow the link below to reset your password. This link expires in two minutes.<br> <a href="%s" class="alert-link">Reset my password</a><br> Please ignore this message if you did not request for it.</p>',
					$jwtToken
				);
				if( SE_DEBUG )
					file_put_contents( ABSPATH . '/mail.txt', $jwtToken );
				// -> mail( $userInfo->email );
				$response = preg_replace( '#^(.).+?(.\@.).+?$#', '$1****$2**', $userInfo->email );
				$response = 'A password reset link has been sent to your email address <strong>' . $response . '</strong>. Please follow the link to reset your password. The link expires in two minutes.<br>Thank You.';
				$json->setMessage( $response );
				$json->setSuccess();
			} catch( Exception $e ) {
				$json->addMessage( $e->getMessage() );
			}
		}
		break;
	case 'login':
		fillPayLoad( $payLoad, 'userName', 'password', 'remember' );
		$payLoad->remember = $payLoad->remember === 'true';
		if( ! preg_match( User::REGEX_USERNAME, $payLoad->userName) && ! preg_match( User::REGEX_EMAIL, $payLoad->userName) ) {
			$json->setFeedBack( 'userName', false );
			$json->addMessage( 'invalid username or email' );
		}
		if( ! preg_match( User::REGEX_PASSWORD, $payLoad->password ) ) {
			$json->setFeedBack( 'password', false );
			$json->addMessage( 'Invalid password' );
		}
		if( ! $json->hasMessage() ) {
			try {
				$dbUserId = User::findId( $payLoad->userName );
				if( 0 === $dbUserId ) {
					$json->setFeedBack( 'userName', false );
					throw new Exception( 'Invalid username' );
				}
				$json->setFeedBack( 'userName', true );

				if( false === matchPassword( $dbUserId, $payLoad->password ) ) {
					$json->setFeedBack( 'password', false );
					throw new Exception( 'Invalid password' );
				}
				$json->setFeedBack( 'password', true );

				/** Create an encoded JWT payload to save user login */
				$jwtToken = [
					'iss' => ROOTURL,
					'aud' => ROOTURL,
					'iat' => time(),
					'userId' => $dbUserId,
				];
				$jwtToken = \Firebase\JWT\JWT::encode( $jwtToken, LOGIN_KEY );
				
				/** Save the login status */
				if( $payLoad->client === 'web' ) {
					session_start();
					$_SESSION['__LOGIN__'] = $jwtToken;
					if( $payLoad->remember )
						setcookie( '__LOGIN__', $jwtToken, time() + (60 * 60 * 60 * 24 * 30), BASEURI, ROOTURL );
				}
				$json->setMessage( [ 'authToken' => $jwtToken ] );
				$json->setSuccess();
			} catch( Exception $e ) {
				$json->addMessage( $e->getMessage() );
			}
		}
		break;
	default:
		die();
		exit;
}
closeJson( $json );
