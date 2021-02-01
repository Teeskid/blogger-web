<?php
/**
 * Blog Index
 * - Handles unhandled request
 * - Handles URL rewriting
 * - Handles page caching
 * @package Sevida
 */
/**
 * Toggles rewriting unhandled request URLs
 * @var bool
 */
define( 'SE_REWRITE', true );

// Load the base component loader file
require( __DIR__ . '/Load.php' );

// URLs rewrite is handled downtown
require( __DIR__ . '/Handler.php' );
