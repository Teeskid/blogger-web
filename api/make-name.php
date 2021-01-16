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
define( 'SE_NO_DB', true );
require( dirname(__FILE__) . '/Load.php' );

$theText = request( 'text' );
$theText = makePermalink( $theText );

jsonOutput( [ 'success' => true, 'text' => $theText ] );
