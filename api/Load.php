<?php
/**
 * Bootstrsap file to to load configuration and do additional
 * setup for the api
 *
 * @package Sevida
 * @subpackage Api
 */
/**
 * Tells that the output of the request is going to be json
 *
 * @var bool
 */
define( 'SE_JSON', true );
// Load base configuration file to setup the environment
require( dirname(__DIR__) . '/Load.php' );
/**
 * Checks that the requested page needs login status being true
 *
 * If no login found, the page return an empty string which is equivalent to 404 status
 * in must client softwares
 *
 * @global REQUIRE_LOGIN
 */
// Show a blank page if user is not logged in
if( defined('REQUIRE_LOGIN') && REQUIRE_LOGIN )
	if( ! LOGGED_IN )
		die();
