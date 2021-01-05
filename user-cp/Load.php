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
define( 'SE_HTML', true );
require( dirname(__DIR__) . '/Load.php' );
if( ! LOGGED_IN && ! defined('SE_LOGIN') && ! defined('SE_INSTALL') ) {
	$redirect = USERPATH . '/login.php';
	if( false === strpos( $_SERVER['PHP_SELF'], 'index.php' ) )
		$redirect .= '?redirect=' . rawurlencode($_SERVER['REQUEST_URI']);
	redirect( $redirect );
}