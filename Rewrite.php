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
define( 'HANDLERS',  '/handlers' );
require( ABSPATH . BASE_UTIL . '/RewriteUtil.php' );

global $rewrite;
$rewrite = new Rewrite();
$GLOBALS['rewrite'] = $rewrite;

rewriteConstants();
loadRewriteRules();

$requestUri = $_SERVER['REQUEST_URI'];
if( requestRewrite( $requestUri, $ENDPOINT, $_VARS ) ) {
	switch( $ENDPOINT ) {
		case EP_BLOG :
			include( ABSPATH . HANDLERS . '/blog.php' );
			break;
		case EP_POST :
			include( ABSPATH . HANDLERS . '/post.php' );
			break;
		case EP_USER :
			include( ABSPATH . HANDLERS . '/user.php' );
			break;
		case EP_MISC :
			include( ABSPATH . HANDLERS . '/misc.php' );
			break;
		default:
			die();
	}
} else {
	if( preg_match( '#\.(map|css|jpg|png|js|json|mp3)$#i', $_SERVER['REQUEST_URI'] ) ) {
		pageNotFound();
		die();
	}
	redirect( BASEPATH . '/404.php' );
}
