<?php
/**
 * Login Utilities
 * 
 * User login utility functions 
 *
 * @package Sevida
 * @subpackage Administration
 */
/**
 * Loads login-based contants
 */
function loginContants() {
	/** @var string Valid syntax for matching email addresses */
	define( 'REGEX_EMAILADD', '#^[a-z0-9-_]{5,}@[a-z0-9\.-]{5,}$#i' );
	/** @var string Valid syntax for matching usernames */
	define( 'REGEX_USERNAME', '#^[a-z0-9-_]{5,30}$#i' );
	/** @var string Valid syntax for matching user passwords */
	define( 'REGEX_PASSWORD', '#^[^<>{}\[\]]{5,20}$#i' );
}
/**
 * Fetches authentication key from database
 * @param int $userId The id of the user whose authentication key we are looking for
 * @return string An authentication key or a NULL if none was found
 */
function findAuthKey( int $userId ) : string {
	global $db;
	$authKey = $db->prepare( 'SELECT metaValue FROM PersonMeta WHERE metaKey=? AND userId=? LIMIT 1' );
	$authKey->execute( [ 'authKey', $userId ] );
	if( $authKey->rowCount() === 0 )
		return '';
	$authKey = $authKey->fetchColumn();
	return $authKey;
}
/**
 * Finds password of the user requested
 * @param int $userId The id of the user whose password we are looking for
 * @return string
 */
function findPassword( int $userId ) : string {
	global $db;
	$password = $db->prepare( 'SELECT password FROM Person WHERE id=? LIMIT 1' );
	$password->execute( [ $userId ] );
	if( $password->rowCount() === 0 )
		return '';
	$password =  $password->fetchColumn();
	return $password;
}
/**
 * Matches a password of a user with his/her password in database 
 * @param int $userId The id of the user
 * @param string $password The password we are verifying
 * @return bool
 */
function matchPassword( int $userId, string $password ) : bool {
	$passwordHash = findPassword( $userId );
	if( password_verify( $password, $passwordHash ) )
		return true;
	return false;
}
/**
 * Matches an authentication token against the one in dabase of the user specified 
 * @param int $userId The id of the user
 * @param string $authToken The token we are verifying
 * @return bool
 */
function matchAuthToken( int $userId, string $authToken ) : bool {
	$authTokenHash = findAuthKey( $userId );
	if( password_verify( $authToken, $authTokenHash ) )
		return true;
	return false;
}
