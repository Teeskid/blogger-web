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
define( 'ABC_COMBINED', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' );
define( 'abc_combined', 'abcdefghijklmnopqrstuvwxyz' );
define( 'ABC_MAX_LEN', 26 );


function minify_css( &$content ) {
	$content = preg_replace( '#/\*.+?\*/#is', '', $content );
	$content = preg_replace( '#[\n\r\t\b]+#i', '', $content );
	$content = preg_replace( '#\s+\+\s+#i', '+', $content );
	$content = preg_replace( '#\s+>+\s+#i', '>', $content );
	$content = preg_replace( '#\s+~+\s+#i', '~', $content );
	$content = preg_replace( '#\;\s*}#i', '}', $content );
	$content = preg_replace( '#\;\s+#i', ';', $content );
	$content = preg_replace( '#\s+@#i', '@', $content );
	$content = preg_replace( '#\:\s+#i', ':', $content );
	$content = preg_replace( '#\,\s+#i', ',', $content );
	$content = preg_replace( '#(\w)\s+(\{)#i', '$1$2', $content );
	$content = preg_replace( '#(\{)\s+(\w)#i', '$1$2', $content );
	$content = preg_replace( '#\s+;\s+#', ';', $content );
	$content = preg_replace( '#\s+\!#', '!', $content );
	$content = preg_replace( '#\)\s+#', ')', $content );
}

function generateName( int $i ) : string {
	$x = 0;
	$y = 0;
	while( $i >= 26 ) {
		$i -= 26;
		$x++;
	}
	$y = $i;
	$index = abc_combined[$x] . abc_combined[$y];
	return $index;
}
function minifyCss( string $content ) : string {
	$content = preg_replace( '#/\*.+?\*/#is', '', $content );
	$content = preg_replace( '#[\n\r\t\b]+#i', '', $content );
	$content = preg_replace( '#\s+\+\s+#i', '+', $content );
	$content = preg_replace( '#\s+>+\s+#i', '>', $content );
	$content = preg_replace( '#\s+~+\s+#i', '~', $content );
	$content = preg_replace( '#\;}#i', '}', $content );
	$content = preg_replace( '#\:\s+#i', ':', $content );
	$content = preg_replace( '#\,\s+#i', ',', $content );
	$content = preg_replace( '#(\w)\s+\{#i', '$1{', $content );
	$content = preg_replace( '#\{\s+(\w)#i', '{$1', $content );
	return $content;
}
function minifyFiles( string $type ) : bool {
	$files = array_merge(
		glob( sprintf( '%1s/%2s/*.%3s', ABSPATH, $type, $type ) ),
		glob( sprintf( '%1s/user-cp/%2s/*.%3s', ABSPATH, $type, $type ) )
	);
	$files = array_filter( $files, 'notMinified' );
	foreach( $files as &$index ) {
		$entry = file_get_contents($index);
		if( $type === 'js' ) {
			$index = str_replace( '.js', '.mix.js', $index );
			$entry = file_put_contents( $index, minifyJs($entry) );
		} else {
			$index = str_replace( '.css', '.mix.css', $index );
			$entry = file_put_contents( $index, minifyCss($entry) );
		}
	}
	return true;
}
function minifyJs( string $content ) : string {
	// strip out comments
	$content = preg_replace( '#//.*#i', '', $content );
	$content = preg_replace( '#/\*.+?\*/#is', '', $content );
	$content = preg_replace( '#[\n\r\t]#i', '', $content );
	$content = preg_replace( '#\s*([\+\-\*\=/\%]+)\s*#i', '$1', $content );
	$content = preg_replace( '#\s*([\(\)\{\}<>~:;,])\s*#i', '$1', $content );
	$content = preg_replace( '#;\}#i', '}', $content );
	$content = preg_replace( '#(if|else|while|for|forach)\((.+?)\)\{([^;\}]+)\}#', '$1($2)$3;', $content, -1, $c );
	$content = obfuscateJs( $content );
	return $content;
}
function notMinified( string $entry ) : bool {
	if( strpos( $entry, '.min.' ) === false || strpos( $entry, '.mix.' ) === false )
		return true;
	return false;
}
function obfuscateJs( string $content ) : string {
	preg_match_all( '#(var|function)\s([a-z0-9\$]+)#is', $content, $funcs );
	$funcs = $funcs[1];
	foreach( $funcs as $index => &$entry ) {
		$index = generateName( $index );
		$index = '$1'.$index.'$2';
		$entry = '([^\w])'.preg_quote( $entry, '#' ).'(\()';
		$entry = '#' . $entry . '#';
		$content = preg_replace( $entry, $index, $content );
		$index = null;
		$entry = null;
	}
	unset( $funcs );
	return $content;
}