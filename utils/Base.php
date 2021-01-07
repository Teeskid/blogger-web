<?php
/**
 * Base generic utilities, PHP5 compatible functions
 *
 * @package Sevida
 * @subpackage Utilities
 */

/**
 * Return the current server protocol
 * 
 * @return string
 */
function getProtocol() {
	$protocol = $_SERVER['SERVER_PROTOCOL'];
	if ( ! in_array( $protocol, array( 'HTTP/1.1', 'HTTP/2', 'HTTP/2.0' ) ) )
		$protocol = 'HTTP/1.0';
	return $protocol;
}
/**
 * Detects if we are browsing by https (secured)
 * 
 * @return bool
 */
function isHttps() {
	if( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'ON' )
		return true;
	if( isset($_SERVER['REQUEST_SHEME']) && $_SERVER['REQUEST_SHEME'] === 'https' )
		return true;
	return false;
}
/**
 * Replaces trailing slashes with forward slashes in $str
 * 
 * @param string $str
 * @return string
 */
function strNoBs( $str ) {
	return str_replace('\\', '/', $str);
}
/**
 * Guess the base / root path of the blog
 *
 * @return string
 */
function getBasePath() {
	$phpSelf = $_SERVER['PHP_SELF'];
	$srcFile = $_SERVER['SCRIPT_FILENAME'];
	do {
		$phpSelf = dirname( $phpSelf );
		$phpSelf = strNoBs( $phpSelf );
		$srcFile = dirname( $srcFile );
		$srcFile = strNoBs( $srcFile );
		if( $phpSelf === '/' )
			$phpSelf = '';
		if( file_exists( $srcFile . '/404.php' ) )
			break;
	} while ( ! empty($phpSelf) );
	return $phpSelf;
}
/**
 * Guess the base url without any path segment attached
 *
 * @return string
 */
function getBaseUrl() {
	$PROTOCOL = 'http' . ( isHttps() ? 's' : '' ) . '://';
	return  $PROTOCOL . $_SERVER['HTTP_HOST'];
}
/**
 * Sends a json header in ready to outputing a json data
 * @return void
 */
function jsonHeader() {
	header( 'Content-Type: application/json', true );
}
/**
 * Prints out a json-serialized version of an array
 * 
 * @param array $json The array to be printed out
 * @return void
 */
function jsonOutput( array $json ) {
	jsonHeader();
	@ob_end_clean();
	die( json_encode($json) );
}
/**
 * Tells the client not to cache the response we are sending
 *  
 * @return void
 */
function noCacheHeaders() {
	$headers = [
		'Expires' => 'Wed, 11 Jan 1984 05:00:00 GMT',
		'Cache-Control' => 'no-cache, must-revalidate, max-age=0',
	];
	if ( function_exists( 'header_remove' ) )
		@header_remove( 'Last-Modified' );
	else
		foreach ( headers_list() as $header ) {
			if ( 0 === stripos( $header, 'Last-Modified' ) ) {
				$headers['Last-Modified'] = '';
				break;
			}
		}
	foreach ( $headers as $name => $field_value )
		@header("{$name}: {$field_value}");
}
/**
 * Sends a 500 status code: Meaning there is an internal sever error, returns a message for
 * use with showError @see showError
 * 
 * @return string
 */
function internalServerError() {
	$message = '500 Internal Server Error';
	header( getProtocol() . $message, true, 500 );
	return $message;
}
/**
 * Sends a 404 response status: Meaning the requested object was not found on server
 * 
 * @return void
 */
function objectNotFound() {
	header( getProtocol() . '404 Page Not Found', true, 404 );
}
/**
 * Redirects to a target url, without caching
 * 
 * @param string $targetUrl The url to reditect to
 * @return void
 * @exits Halters the script execution
 */
function redirect( $targetUrl ) {
	noCacheHeaders();
	header( 'Location: ' . $targetUrl );
	exit;
}
/**
 * Start the global blog timer
 * 
 * @global $_TIME_BEG, $_TIME_END
 * @return void
 */
function startTimer() {
	global $_TIME_BEG;
	$_TIME_BEG = microtime(true);
}
/**
 * Stop the global blog timer
 * 
 * @global $_TIME_BEG, $_TIME_END, $_TIME_ELP
 * @return void
 */
function stopTimer() {
	global $_TIME_BEG, $_TIME_END, $_TIME_ELP;
	$_TIME_END = microtime(true);
	$_TIME_ELP = $_TIME_END - $_TIME_BEG;
}
/**
 * Pretty print error messages with an appropriate response status header
 * 
 * @param string $title An Optional error page title. An empty title defaults 'Unknown Error'
 * @param mixed|array|object $message The error page body html or plain text
 * @return void
 */
function showError( $title, $message = false ) {
	if( ! $message ) {
		$message = $title;
		$title = 'Unknown Error';
	}
	if( ! is_string($message) ) {
		$title = 'JSON Dump';
		$message = json_encode( $message );
	}
	ob_clean();
	if( defined('SE_JSON') && SE_JSON ) {
		jsonOutput( [ 'success' => false, 'message' => $message ] );
		exit;
	}
	header( 'Content-Type: text/html; charset=utf-8' );
	echo <<<EOS
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="favicon.png" rel="shortcut icon" type="image/png" />
<title>$title</title>
<style type="text/css">
html {
	background: #f1f1f1;
}
body {
	background: #fff;
	color: #444;
	font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
	margin: 2em auto;
	padding: 1em 2em;
	max-width: 700px;
	-webkit-box-shadow: 0 1px 3px rgba( 0,0,0,0.13 );
	box-shadow: 0 1px 3px rgba( 0,0,0,0.13 );
}
h1 {
	border-bottom: 1px solid #dadada;
	clear: both;
	color: #666;
	font-size: 24px;
	margin: 30px 0 0 0;
	padding: 0;
	padding-bottom: 7px;
}
#error-page {
	margin-top: 50px;
}
#error-page p {
	font-size: 14px;
	line-height: 1.5;
	margin: 25px 0 20px;
}
#error-page code {
	font-family: Consolas, Monaco, monospace;
}
ul li {
	margin-bottom: 10px;
	font-size: 14px ;
}
a {
	color: #0073aa;
}
a:hover,
a:active {
	color: #00a0d2;
}
a:focus {
	color: #124964;
	-webkit-box-shadow:
		0 0 0 1px #5b9dd9,
		0 0 2px 1px rgba( 30, 140, 190, .8 );
	box-shadow:
		0 0 0 1px #5b9dd9,
		0 0 2px 1px rgba( 30, 140, 190, .8 );
	outline: none;
}
.btn {
	background: #f7f7f7;
	border: 1px solid #ccc;
	color: #555;
	display: inline-block;
	text-decoration: none;
	font-size: 13px;
	line-height: 26px;
	height: 28px;
	margin: 0;
	padding: 0 10px 1px;
	cursor: pointer;
	-webkit-border-radius: 3px;
	-webkit-appearance: none;
	border-radius: 3px;
	white-space: nowrap;
	-webkit-box-sizing: border-box;
	-moz-box-sizing:    border-box;
	box-sizing:         border-box;
	-webkit-box-shadow: 0 1px 0 #ccc;
	box-shadow: 0 1px 0 #ccc;
	vertical-align: top;
}
.btn.btn-large {
	height: 30px;
	line-height: 28px;
	padding: 0 12px 2px;
}
.btn:hover,
.btn:focus {
	background: #fafafa;
	border-color: #999;
	color: #23282d;
}
.btn:focus  {
	border-color: #5b9dd9;
	-webkit-box-shadow: 0 0 3px rgba( 0, 115, 170, .8 );
	box-shadow: 0 0 3px rgba( 0, 115, 170, .8 );
	outline: none;
}
.btn:active {
	background: #eee;
	border-color: #999;
	-webkit-box-shadow: inset 0 2px 5px -3px rgba( 0, 0, 0, 0.5 );
	box-shadow: inset 0 2px 5px -3px rgba( 0, 0, 0, 0.5 );
	-webkit-transform: translateY( 1px );
	-ms-transform: translateY( 1px );
	transform: translateY( 1px );
}
</style>
</head>
<body id="error-page">
	<h1>$title</h1>
	<p>$message</p>
</body>
</html>
EOS;
	exit;
}
