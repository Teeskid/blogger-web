<?php
/**
 * 404 Error Page
 *
 * @package Sevida
 */
// we want only basic functions
define( 'SE_NO_DB', false );
// load the blog bootstrap file
require( dirname(__FILE__) . '/Load.php' );
// Time to show the error message in full
showError(
    'Object Not Found',
    'The requested URL was not found on this server. ' .
    'If you entered the URL manually please check your spelling and try again.<br>' .
    'If you think this is a server error, please contact the Site Admin'
);