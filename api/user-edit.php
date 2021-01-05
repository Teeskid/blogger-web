<?php

define( 'REQUIRE_LOGIN', true );
$personId = @queryUserId();
$profile = request( 'first_name','last_name','nick_name','social_fb','social_tw','bio_info','mobile_no','password' );
if( ! empty( $profile->password ) ) {
	$profile->password = encryptPassword( $profile->password );
	$my_query = $db->prepare( 'UPDATE users SET password=? WHERE id=?' );
	$my_query->execute( [ $profile->password, $personId ] );
}
$my_query = $db->prepare( 'REPLACE INTO PersonMeta (personId,metaKey,val) VALUES (?,?,?),(?,?,?),(?,?,?),(?,?,?),(?,?,?),(?,?,?),(?,?,?)' );
$my_query->execute( [
	$personId, 'first_name', $profile->first_name,
	$personId, 'last_name', $profile->last_name,
	$personId, 'nick_name', $profile->nick_name,
	$personId, 'bio_info', $profile->bio_info,
	$personId, 'mobile_no', $profile->mobile_no,
	$personId, 'social_fb', $profile->social_fb,
	$personId, 'social_tw', $profile->social_tw
] );
try {
	$stmt = $db->prepare( 'UPDATE users SET email=? WHERE id=?' );
	$stmt->execute( [ $profile->email, $personId ] );
}
catch( Exception $e ) {
	$error[] = $e->getMessage();
}
if( isset($errors[0]) ) {
}
