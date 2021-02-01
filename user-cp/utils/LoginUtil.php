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
 * Fetches authentication key from database
 * @param int $userId The id of the user whose authentication key we are looking for
 * @return string An authentication key or a NULL if none was found
 */
function findAuthToken( int $userId ) : string {
	global $_db;
	$authToken = $_db->prepare( 'SELECT metaValue FROM UzerMeta WHERE metaKey=? AND userId=? LIMIT 1' );
	$authToken->execute( [ 'authToken', $userId ] );
	if( 0 === $authToken->rowCount() )
		return '';
	$authToken = $authToken->fetchColumn();
	return $authToken;
}
/**
 * Finds password of the user requested
 * @param int $userId The id of the user whose password we are looking for
 * @return string
 */
function findPassword( int $userId ) : string {
	global $_db;
	$password = $_db->prepare( 'SELECT password FROM Uzer WHERE id=? LIMIT 1' );
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
	$authTokenHash = findAuthToken( $userId );
	if( password_verify( $authToken, $authTokenHash ) )
		return true;
	return false;
}
