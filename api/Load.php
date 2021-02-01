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
 * @var bool
 */
define( 'SE_JSON', true );
/**
 * Disable using session
 * @var bool
 */
define( 'USE_SESSION', false );

/** Load blog bootstrap file */
require( dirname(__DIR__) . '/Load.php' );

if( defined('REQUIRE_LOGIN') && REQUIRE_LOGIN )
    if( ! isset($_usr) )
        sendError( 'You are not signed in', 501, 'Unauthorized Access' );
