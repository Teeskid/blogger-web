<?php
/**
 * Blog Requests Handler
 * 
 * Any unhandled request is processed by this file
 * URL rewriting is done here on this page
 * Page caching is indirectly handled by this file
 *
 * @package Sevida
 */

/**
 * Toggles rewriting unhandled URLs: Off means a 404 page always
 * 
 * @var bool SE_REWRITE
 */
define( 'SE_REWRITE', true );

// Load the base component loader file
require( dirname(__FILE__) . '/Load.php' );

// URLs rewrite is handed downtown
require( ABSPATH . '/Rewrite.php' );
// PHP_EOL
