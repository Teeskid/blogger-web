<?php
/**
 * Base and Basic Functions
 * @package Sevida
 * @subpackage Utilities
 */
/**
 * Binds a custom error and exception handlers to pretty-print errors
 */
function bindErrorHandlers() {
	$errCall = function( $errCode, $errText, $errFile, $errLine ) {
		$errText = sprintf( '%s<br>Location: <code>%s</code> at line <code>%s</code>', $errText, $errFile, $errLine );
		sendError( $errText );
	};
	set_error_handler( $errCall );
	set_exception_handler( function( $exception ) use( $errCall ) {
		$errCall( $exception->getCode(), $exception->getMessage(), $exception->getFile(), $exception->getLine() );
	} );
}
/**
 * Checks globals and standardize them due to server software difference
 * Prohibits global variable override
 */
function fixRequestVars() {
	if( ini_get( 'register_globals' ) ) {
		if ( isset( $_REQUEST['GLOBALS'] ) )
			sendError( 'GLOBALS overwrite attempt detected.' );
		$noUnset = array( 'GLOBALS', '_GET', '_POST', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES' );
		$input = array_merge( $_GET, $_POST, $_COOKIE, $_SERVER, $_ENV, $_FILES, (isset( $_SESSION ) && is_array( $_SESSION ) ? $_SESSION : [] ) );
		foreach ( $input as $k => $v )
			if ( !in_array( $k, $noUnset ) && isset( $GLOBALS[$k] ) ) {
				unset( $GLOBALS[$k] );
			}
	}
}
/**
 * PHP and mysql version check : If they do not meet the requiremnt, and error page
 * displays with an appropriate response header
 */
function checkVersions() {
	global $_phpVersion, $_blogVersion;
	$phpVersion = phpversion();
	if ( version_compare( $_phpVersion, $phpVersion, '>' ) )
		sendError( sprintf( 'Your server is running PHP version %1$s but Blog Software %2$s requires at least %3$s.', $phpVersion, $_blogVersion, $_phpVersion ) );
	if ( ! extension_loaded( 'mysql' ) && ! extension_loaded( 'mysqli' ) && ! extension_loaded( 'mysqlnd' ) )
		sendError( 'Your PHP installation appears to be missing the MySQL extension which is required by Blog.' );
}
/**
 * Does some work around to fix $_SERVER variable to be almost same on all servers 
 */
function fixServerVars() {
	$defaultValues = array(
		'SERVER_SOFTWARE' => '',
		'REQUEST_URI' => '',
	);
	$_SERVER = array_merge( $defaultValues, $_SERVER );
	// Fix for IIS when running with PHP ISAPI
	if ( empty( $_SERVER['REQUEST_URI'] ) || ( PHP_SAPI != 'cgi-fcgi' && preg_match( '/^Microsoft-IIS\//', $_SERVER['SERVER_SOFTWARE'] ) ) ) {
		// IIS Mod-Rewrite
		if ( isset( $_SERVER['HTTP_X_ORIGINAL_URL'] ) ) {
			$_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_ORIGINAL_URL'];
		}
		// IIS Isapi_Rewrite
		elseif ( isset( $_SERVER['HTTP_X_REWRITE_URL'] ) ) {
			$_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_REWRITE_URL'];
		} else {
			// Use ORIG_PATH_INFO if there is no PATH_INFO
			if ( !isset( $_SERVER['PATH_INFO'] ) && isset( $_SERVER['ORIG_PATH_INFO'] ) )
				$_SERVER['PATH_INFO'] = $_SERVER['ORIG_PATH_INFO'];
			// Some IIS + PHP configurations puts the script-name in the path-info (No need to append it twice)
			if ( isset( $_SERVER['PATH_INFO'] ) ) {
				if ( $_SERVER['PATH_INFO'] == $_SERVER['SCRIPT_NAME'] )
					$_SERVER['REQUEST_URI'] = $_SERVER['PATH_INFO'];
				else
					$_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'] . $_SERVER['PATH_INFO'];
			}
			// Append the query if it exists and isn't null
			if ( ! empty( $_SERVER['QUERY_STRING'] ) ) {
				$_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
			}
		}
	}
	// Fix for PHP AS CGI hosts that set SCRIPT_FILENAME to something ending in php.cgi for all requests
	if ( isset( $_SERVER['SCRIPT_FILENAME'] ) && ( strpos( $_SERVER['SCRIPT_FILENAME'], 'php.cgi' ) == strlen( $_SERVER['SCRIPT_FILENAME'] ) - 7 ) )
		$_SERVER['SCRIPT_FILENAME'] = $_SERVER['PATH_TRANSLATED'];
	// Fix for Dreamhost and other PHP AS CGI hosts
	if ( strpos( $_SERVER['SCRIPT_NAME'], 'php.cgi' ) !== false )
		unset( $_SERVER['PATH_INFO'] );
	// Fix empty PHP_SELF
	$PHP_SELF = $_SERVER['PHP_SELF'];
	if ( empty( $PHP_SELF ) )
		$_SERVER['PHP_SELF'] = $PHP_SELF = preg_replace( '/(\?.*)?$/', '', $_SERVER["REQUEST_URI"] );
	unset($defaultValues);
}
/**
 * Loads class file by the class name
 * @param string $className
 */
function classLoader( $className ) {
	$className = strNoBs( $className );
	require( ABSPATH . BASE_UTIL . DIRECTORY_SEPARATOR . $className . '.php' );
}
/**
 * Return the current server protocol
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
 * Replaces trailing slashes with forward slashes in text
 * @param string $str
 * @return string
 */
function strNoBs( $str ) {
	return str_replace('\\', '/', $str);
}
/**
 * Guess the base / root path of the blog
 * @return string
 */
function getBaseUri() {
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
 * @return string
 */
function getOrigin() {
	$SCHEME = 'http' . ( isHttps() ? 's' : '' ) . '://';
	return  $SCHEME . $_SERVER['HTTP_HOST'];
}
/**
 * Sends a json header in ready to outputing a json data
 */
function jsonHeader() {
	header( 'Content-Type: application/json', true );
}
/**
 * Tells the client not to cache the response we are sending
 */
function noCacheHeaders() {
	$headers = [
		'Expires' => 'Wed, 11 Jan 1984 05:00:00 GMT',
		'Cache-Control' => 'no-cache, must-revalidate, max-age=0',
	];
	if ( function_exists( 'header_remove' ) )
		@header_remove( 'Last-Modified' );
	else {
		$allHeaders = headers_list();
		foreach ( $allHeaders as $header ) {
			if ( 0 === stripos( $header, 'Last-Modified' ) ) {
				$headers['Last-Modified'] = '';
				break;
			}
		}
	}
	foreach ( $headers as $name => $field_value )
		@header("{$name}: {$field_value}");
}
/**
 * Send a HTTP response header
 * @param int $code
 * @param string $text
 */
function headStatus( $code, $text ) {
	header( getProtocol() . " $code $text", true, $code );
}
/**
 * Sends a 500 status code: Meaning there is an internal sever error, returns a message for
 * use with respond
 * @return string
 */
function internalServerError() {
	headStatus( 500, 'Internal Server Error' );
}
/**
 * Sends a 404 status code: Meaning the requested object was not found on server
 */
function objectNotFound() {
	headStatus( 404, 'Page Not Found' );
}
/**
 * Redirects to a target url, without caching
 * @param string $targetUrl The url to reditect to
 */
function redirect( $targetUrl ) {
	noCacheHeaders();
	if( defined('SE_JSON') && SE_JSON )
		die( $targetUrl );
	header( 'Location: ' . $targetUrl );
	exit;
}
/**
 * Start the global blog timer
 * @global $_TIME_BEG, $_TIME_END
 */
function startTimer() {
	global $_TIME_BEG;
	$_TIME_BEG = microtime(true);
	$GLOBALS['_TIME_BEG'] = $_TIME_BEG;
}
/**
 * Stop the global blog timer
 * @global $_TIME_BEG, $_TIME_END, $_TIME_ELP
 */
function stopTimer() {
	global $_TIME_BEG, $_TIME_END, $_TIME_ELP;
	$_TIME_END = microtime(true);
	$_TIME_ELP = $_TIME_END - $_TIME_BEG;
}
/**
 * Pretty print a message as a page or a json response
 * @param string $message Error message body
 * @param int $statusCode [option] defaults to 500
 * @param string $statusText [optional] defaults to interal server error 
 */
function sendError( $message, $statusText = 'Internal Server Error', $statusCode = 500 ) {
	ob_end_clean();
	headStatus( $statusCode, $statusText );
	if( defined('SE_JSON') && SE_JSON ) {
		jsonHeader();
		$message = [ 'success' => false, 'message' => $message ];
		$message = json_encode($message);
		die($message);
	}
	header( 'Content-Type: text/html; charset=utf-8' );
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="favicon.png" rel="shortcut icon" type="image/png" />
<title><?=$statusText?></title>
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
	margin-top: 50px;
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
p {
	font-size: 14px;
	line-height: 1.5;
	margin: 25px 0 20px;
}
code {
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
<body>
	<h1><?=( $statusCode . ' ' . $statusText )?></h1>
	<p><?=$message?></p>
</body>
</html>
<?php
	exit;
}
