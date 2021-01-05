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
function generateToken( int $maxlen = 1, int $size = 32 ) : array {
	$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	$secret_key = [];
	$max = strlen( $chars ) - 1;
	for ( $i = 0; $i < $maxlen; $i++ ) {
		$key = '';
		for ( $j = 0; $j < $size; $j++ ) {
			$key .= substr( $chars, rand( 0, $max ), 1 );
		}
		$secret_key[] = $key;
	}
	return $secret_key;
}
function generatePrefix() : string {
	$chars = 'abcdefghijklmnopqrstuvwxyz';
	$max = strlen( $chars ) - 1;
	$pre = '';
	for( $x = 0; $x < 2; $x++ ) {
		$pre .= substr( $chars, rand( 0, $max ), 1 );
	}
	return $pre;
}
function unlinkUserFiles( bool $withUpload = false ) {
	$files = [ ABSPATH . '/.htaccess.php', ABSPATH . '/.lighttpd.php', ABSPATH . '/Manifest.php' ];
	if( $withUpload )
		$files = array_merge( glob( ABSPATH . DIR_UPLOAD . '*.*' ), $files );
	foreach( $files as $file ) {
		if( file_exists($file) )
			unlink($file);
	}
}