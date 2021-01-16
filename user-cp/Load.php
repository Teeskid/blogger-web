<?php
/**
 * Admin Bootstrap File
 * 
 * Loads the root Load.php and checks for login status, if not logged,
 * redirects to login.php unless it's already in the login page
 *  
 * @package Sevida
 * @subpackage Administration
 */
/**
 * Tells that this is a html page
 * @var bool
 */
define( 'SE_HTML', true );
require( dirname(__DIR__) . '/Load.php' );

/** If the user is not logged in and we are not installing the blog */
if( ! LOGGED_IN && ! defined('SE_LOGIN') && ! defined('SE_INSTALL') ) {
	$redirect = USERPATH . '/login.php';
	// resume if any outstanding job
	if( false === strpos( $_SERVER['PHP_SELF'], 'index.php' ) )
		$redirect .= '?redirect=' . rawurlencode($_SERVER['REQUEST_URI']);
	redirect( $redirect );
}