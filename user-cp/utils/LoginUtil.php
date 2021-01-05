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
function loginContants() {
	define( 'REGEX_EMAILADD', '#^[a-z0-9-_]{5,}@[a-z0-9\.-]{5,}$#i' );
	define( 'REGEX_USERNAME', '#^[a-z0-9-_]{5,30}$#i' );
	define( 'REGEX_PASSWORD', '#^[^<>{}\[\]].{8,30}$#i' );
}
function findPassword( int $personId ) : string {
	global $db;
	$password = $db->prepare( 'SELECT password FROM Person WHERE id=? LIMIT 1' );
	$password->execute( [ $personId ] );
	$password = $password->fetchColumn();
	$password = (string) $password;
	return $password;
}
function matchPassword( int $personId, string $password ) : bool {
	$thePassword = findPassword( $personId );
	if( print password_verify( $password, $thePassword ) )
		return true;
	return false;
}
