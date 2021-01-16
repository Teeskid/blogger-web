<?php

define( 'REQUIRE_LOGIN', true );
$userId = @queryUserId();
$profile = request( 'first_name','last_name','nick_name','social_fb','social_tw','bio_info','mobile_no','password' );
if( ! empty( $profile->password ) ) {
	$profile->password = encryptPassword( $profile->password );
	$my_query = $db->prepare( 'UPDATE users SET password=? WHERE id=?' );
	$my_query->execute( [ $profile->password, $userId ] );
}
$my_query = $db->prepare( 'REPLACE INTO PersonMeta (userId,metaKey,val) VALUES (?,?,?),(?,?,?),(?,?,?),(?,?,?),(?,?,?),(?,?,?),(?,?,?)' );
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
	$stmt = $db->prepare( 'UPDATE users SET email=? WHERE id=?' );
	$stmt->execute( [ $profile->email, $userId ] );
}
catch( Exception $e ) {
	$error[] = $e->getMessage();
}
if( isset($errors[0]) ) {
}
