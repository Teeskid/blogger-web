<?php
/**
 * 404 Error Page
 * @package Sevida
 */
/**
 * We want only basic functions
 * @var bool
 */
define( 'SHORT_INIT', false );
// load the blog bootstrap file
require( __DIR__ . '/Load.php' );
// Time to show the error message in full
sendError(
    'The requested URL was not found on this server. ' .
    'If you entered the URL manually please check your spelling and try again.<br>' .
    'If you think this is a server error, please contact the Site Admin',
    'Object Not Found', 404
);