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
define( 'SE_REWRITE', true );
require( dirname(__FILE__) . '/Load.php' );

if( 'GET' === $_SERVER['REQUEST_METHOD'] && ! LOGGED_IN ) {
	$cacheKey = ABSPATH . DIR_CACHES . md5($_SERVER['REQUEST_URI']) . '.html';
	if( file_exists($cacheKey) ) {
		$cacheUse = time() - filemtime($cacheKey);
		if( $cacheUse <= 10 /*86400*/ ) {
			readfile($cacheKey);
			exit;
		} else {
			unlink($cacheKey);
			unset($cacheKey);
		}
	}
}
require( ABSPATH . '/Rewrite.php' );
if( isset($cacheKey) ) {
	$cacheValue = ob_get_contents();
	$cacheValue = str_replace( "\t", "", $cacheValue );
	file_put_contents( $cacheKey, $cacheValue );
	unset( $cacheKey, $cacheValue );
}