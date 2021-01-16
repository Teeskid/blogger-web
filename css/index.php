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
define( 'SE_NO_DB', false );
require( dirname(__DIR__) . '/Load.php' );

$colorLight = '#f4af52';
// Dark Primary As BG
$colorDark = '#e08000';
// White Background Color
$bgLight = '#ffffff';
// Dark Background Color
$bgDark = '#585858';
// Accent Background
$bgAccent = '#f5f5f5';
// White Text
$textLight = '#ffffff';
// Darker
$textDark = '#585858';
// Body Text
$textAccent = '#757575';
// Borders Accent White BG
$strokeAccent = '#d7d7d7';
// Dark Shadows
$darkShadow = 'rgba(88, 88, 88, 0.63)';

$styles = request('load') ?? '';
$styles = explode( ',', $styles );
$styles = array_merge( $styles, [ 'styles', 'feeds' ] );
$styles = array_unique($styles);

noCacheHeaders();
header( 'Content-Type: text/css;charset=utf-8', true );
foreach( $styles as $entry ) {
	$entry = str_replace( '/', '', $entry );
	$entry = str_replace( '\\', '', $entry );
	$entry = ABSPATH . '/css/_' . $entry . '.php';
	if( ! file_exists($entry) )
		continue;
	include( $entry );
	print(PHP_EOL);
}
$content = ob_get_contents();
exit;