<?php
/**
 * Helper Functions
 * 
 * Functions for performing simple convertion and manipuulation
 * @package Sevida
 * @subpackage Utilities
 */
/**
 * Collects login data from either session or as a request child variable
 * @return object|bool Returns a valid session or false which means there is no valid login data
 */
function getLogin() {
	$payLoad = $_SESSION['__LOGIN__'] ?? $_REQUEST['jwt'] ?? false;
	if( ! $payLoad )
		return $payLoad;
	try {
		$session = \Firebase\JWT\JWT::decode( $payLoad, LOGIN_KEY, [ 'HS256' ] );
		if( ! $session )
			throw new Exception();
		$session = (object) [ 'userId' => $session->uid, 'session' => $session->sid, 'token' => $payLoad ];
	} catch( Exception $e ) {
		$session = false;
	}
	return $session;
}
/**
 * Collects a redirect url via the _GET request params
 * 
 * The url indects where we are heading to after executing what we are asked to do
 * @param string $defalt [optional] Provide a default url to return if no redirect url was set
 * 		which defaults to index.php of the directory of the requested file
 * @return string The redir 
 */
function getReturnUrl( string $default = null ) : string {
	if( ! $default )
		$default = 'index.php';
	$ref = $_REQUEST['redirect'] ?? $_REQUEST['_ref'] ?? $default ;
	$ref = trim( $ref );
	return $ref;
}
/**
 * Tells whether the request sent is sent via GET method
 * @return bool true if it is a GET request or false if otherwise
 */
function isGetRequest() : bool {
	if( $_SERVER['REQUEST_METHOD'] === 'GET' )
		return true;
	return false;
}
/**
 * Detects whether we are running this script locally or not
 * @return bool true if we are running locally or false if otherwise
 */
function isLocalServer() : bool {
	$HOST = $_SERVER['HTTP_HOST'];
	$ADDR = $_SERVER['SERVER_ADDR'];
	if( $ADDR === '::1' || true === strpos( $HOST, 'localhost' ) )
		return true;
	return false;
}
/**
 * Checks user login status
 * @return bool true if the user is logged in as false if otherwise
 */
function isLoggedIn() : bool {
	global $_login;
	$_login = getLogin(); 
	if( $_login && password_verify( session_id(), $_login->session ) && $_login->userId !== 0 )
		return true;
	return false;
}
/**
 * Tells whether the request sent is sent via POST method
 * @return bool true if it is a POST request or false if otherwise
 */
function isPostRequest() : bool {
	if( $_SERVER['REQUEST_METHOD'] === 'POST' )
		return true;
	return false;
}
/**
 * Tells if the item provided is an empty string, null or a boolean false
 * @return true if the item is empty or a false
 */
function notEmpty( $item ) : bool{
	return ! empty( $item );
}
/**
 * Parses number or string to interger
 * This is mostly useful when using array_map functions, where a casts can not be used
 * @param $int What we want to cast (int)
 * @return int The casted integer value of the number / string
 */
function parseInt( $int ) : int {
	return ( (int ) $int );
}
/*
function getParams( string $request ) : array {
	$entries = parse_url($request);
	$entries = trim( $entries['query'] ?? '' );
	$entries = preg_split( '#&#', $entries );
	$entries = array_filter( $entries, 'notEmpty' );
	$request = [];
	foreach( $entries as $index => $entry ) {
		if( empty($entry) )
			continue;
		$entry = explode( '=', $entry, 2 );
		$index = trim($entry[0]);
		if( ! preg_match( '#^[\w_]+$#i', $index ) )
			continue;
		$entry = trim( $entry[1] ?? '' );
		if( preg_match( '#%2F#i', $entry ) ) {
			$entry = rawurldecode($entry);
		} else {
			$entry = urldecode($entry);
			if( preg_match( '#[\#\\\']#i', $entry ) )
				showError( 'Mod_Security', 'Malicious request detected' );
			if( is_numeric($entry) && ! preg_match( '#^(year|month|day)$#', $index ) ) {
				$entry = (int) $entry;
			} elseif( $entry === 'true' || $entry === 'false' )
				$entry = (bool) ( $entry === 'true' );
		}
		$request[$index] = $entry;
	}
	unset( $entries, $index, $entry );
	return $request;
}
*/
/**
 * Escapes any html tag in texts for security reasons
 * @param mixed $text The text to be purified
 * @return string
 */
function escHtml( $text ) : string {
	$text = htmlspecialchars( $text, ENT_HTML5 );
	return $text;
}
/**
 * Truncates the length of the provided string to 250 if longer than that
 * making it eligible for use as post excerpt
 * @param string $str
 * @return string
 */
function makeExcerpt( string $str ) : string {
	$last = strpos( $str, '[' );
	if( $last > 300 ) $last = 250;
	if( $last < 250 ) $last = 250;
	$str = substr( $str, 0, $last );
	return $str;
}
/**
 * Creates a file name body from a string by replacing any unsupported character
 * @param string $name
 * @return string
 */
function makeNameBody( string $name ) : string {
	$name = preg_replace( '#\s*?\&\s*?#', ' and ', $name);
	$name = preg_replace( '#\s*?\+\s*?#', ' plus ', $name );
	$name = preg_replace( '#[^a-z0-9\.]+#i', '_', $name );
	$name = preg_replace( '#\_{2,}#', '_', $name );
	$name = preg_replace( '#(^_|_$)#', '', $name );
	if( empty($name) )
		$name = md5(rand(1000,2000));
	return $name;
}
/**
 * Generates a permalink from a name or title
 * @param string $permalink The seed
 * @return string
 */
function makePermalink( string $permalink ) : string {
	$permalink = preg_replace( '#\s*?\&\s*?#', ' and ', $permalink);
	$permalink = preg_replace( '#\s*?\+\s*?#', ' plus ', $permalink );
	$permalink = preg_replace( '#[^a-z0-9]+#i', '-', $permalink );
	$permalink = preg_replace( '#\-{2,}#', '-', $permalink );
	$permalink = preg_replace( '#(^-|-$)#', '', $permalink );
	$permalink = strtolower( $permalink );
	return $permalink;
}
/**
 * Trims a pure string input, while casting an number string input
 * @param string $value
 * @return mixed
 */
function washValue( $value ) {
	$value = trim($value);
	if( is_numeric($value) && strpos( '+', $value ) === false )
		$value = (int) $value;
	return $value;
}
/**
 * Collects form entities and files send through any request method
 * @param array $indexes list of keys of items to be returned
 * @return array|mixed a single item or an object, depending on the input
 */
function request( string ...$indexes ) {
	$request = new Class(){};
	foreach( $indexes as $index ) {
		$value = $_REQUEST[$index] ?? $_FILES[$index] ?? false;
		if( ! $value ) {
			$request->$index = null;
			continue;
		}
		if( is_array($value) ) {
			if( isset($value['tmp_name']) ) {
				if( is_array($value['name']) ) {
					$max = count($value['name']);
					$key = array_keys($value);
					$xmp = array_fill( 0, $max, [] );
					for( $x=0; $x<$max; $x++ )
						foreach( $key as $val )
							$xmp[$x][$val] = $value[$val][$x] ?? null;
					$value = $xmp;
					unset($xmp);
				}
			} else {
				$value = array_map('washValue', $value);
			}
		} else {
			$value = washValue($value);
		}
		$request->$index = $value;
		unset( $_GET[$index], $_POST[$index], $_REQUEST[$index], $_FILES[$index], $index, $value );
	}
	if( ! isset($indexes[1]) ) {
		$indexes = $indexes[0];
		$request = $request->$indexes;
	}
	return $request;
}
/**
 * Parses a date string input to a map of year, month...
 * @return string $theDate
 * @return object
 */
function parseDate( string $theDate ) : object {
	$theDate = (object) date_parse($theDate);
	$theDate->year = str_pad( $theDate->year, 2, "0", STR_PAD_LEFT );
	$theDate->month = str_pad( $theDate->month, 2, "0", STR_PAD_LEFT );
	$theDate->day = str_pad( $theDate->day, 2, "0", STR_PAD_LEFT );
	return $theDate;
}
