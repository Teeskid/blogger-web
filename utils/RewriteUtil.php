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
/**
 */
function rewriteConstants() {
	define( 'EP_FILE', 1 );
	define( 'EP_BLOG', 2 );
	define( 'EP_POST', 3 );
	define( 'EP_PAGE', 4 );
	define( 'EP_USER', 5 );
	define( 'EP_MISC', 6 );
}
function loadRewriteRules() {
	global $rewrite;
	$rewrite->rules = [
		[ '/(robots\.txt|sitemap\.xml)/?', '/index.php?misc=$1' ],
		[ '/(search|author|category|tag)/([a-zA-Z0-9-]+)/?', '/index.php?blog=$1&name=$2' ],
		[ '/([0-9]{4})/?', '/index.php?blog=year&value=$2' ],
		[ '/([0-9]{4})/([0-9]{1,2})/?', '/index.php?blog=month&year=$2&month=$3' ],
		[ '/([0-9]{4})/([0-9]{2})/([a-zA-Z0-9-]+)/?', '/index.php?year=$1&month=$2&post=$3' ],
		[ '/post/([a-zA-Z0-9-]+)/?', '/index.php?post=$1' ],
		[ '/user/([a-zA-Z0-9-]+)/?', '/index.php?user=$1' ],
		[ '/([a-zA-Z0-9-]+)/?', '/index.php?name=$1' ],
		[ '(/index\.php)?/?', '/index.php?blog=latest' ],
	];
}
function rewriteTheRequest() : bool {
	global $rewrite;
	$request = $_SERVER['REQUEST_URI'];
	$request = explode( '?', $request );
	if( isset($request[1]) ) {
		parse_str( $request[1], $_GET );
		if( ! is_array($_GET) )
			$_GET = [];
	}
	$request = $request[0];
	foreach( $rewrite->rules as $rule ) {
		$pattern = $rule[0];
		$replace = $rule[1];
		$pattern = preg_quote( BASEURI, '#' ) . $pattern;
		$pattern = str_replace( '#', '\\#', $pattern );
		$pattern = '#^' . $pattern . '$#';
		if ( preg_match( $pattern, $request ) ) {
			$pageUri = preg_replace( $pattern, $replace, $request );
			break;
		}
		$pageUri = null;
	}
	if( $pageUri === null )
		return false;
	$request = explode( '?', $pageUri );
	parse_str( $request[1], $payLoad );
	if( ! is_array($payLoad) )
		$payLoad = [];
	$_GET = array_merge( $payLoad, $_GET );
	return true;
}
