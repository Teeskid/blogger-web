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
define( 'SHORT_INIT', true );
require( __DIR__ . '/Load.php' );

$theText = request( 'text' );
$theText = makePermalink( $theText );

closeJson( [ 'success' => true, 'text' => $theText ] );
