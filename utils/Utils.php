<?php
/**
 * Helper Functions
 * @package Sevida
 * @subpackage Utilities
 */
/**
 * Gets request object from php://input stream
 * @param array $coreKeys
 * @return object
 */
function getPayLoad() : object {
	$payLoad = file_get_contents('php://input');
	if( empty($payLoad) || ! is_object( $payLoad = json_decode($payLoad) ) )
		$payLoad = (object) [];
	return $payLoad;
}
/**
 * Fills missing keys for object payload
 * @param mixed $payLoad
 * @param string... $coreKeys
 */
function fillPayLoad( &$payLoad, string ...$coreKeys ) {
	foreach( $coreKeys as $key ) {
		if( ! isset( $payLoad->$key ) ) {
			$payLoad->$key = null;
		}
	}
}
/**
 * Collects login data from either session or as a request child variable
 * @return object|bool Returns a valid session or false which means there is no valid login data
 */
function getUserLogin() {
	if( ! defined('LOGIN_KEY') )
		return false;
	if( session_id() ) {
		$userLogin = $_SESSION['__LOGIN__'] ?? $_COOKIE['__LOGIN__'] ?? false;
	} elseif( defined('SE_JSON') ) {
		$allHeaders = getallheaders();
		if( isset($allHeaders['Authorization']) ) {
			$userLogin = $allHeaders['Authorization'];
			$userLogin = sscanf( $userLogin, 'Token %s' );
			$userLogin = $userLogin[0] ?? false;
		} else {
			$userLogin = false;
		}
		unset($allHeaders);
	}
	if( $userLogin !== false ) {
		try {
			$userLogin = \Firebase\JWT\JWT::decode( $userLogin, LOGIN_KEY, [ 'HS256' ] );
			if( ! is_object($userLogin) )
				throw new Exception( 'Authorizarion Failed' );
		} catch( Exception $e ) {
			$userLogin = false;
		}
	}
	return $userLogin;
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
	return $_REQUEST['redirect'] ?? $default;
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
function notEmpty( $item ) : bool {
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
 * Strips out single quote using PDO::quote, useful when using arrays in query
 * @param string $text
 * @return string
 */
function escQuote( string $text ) : string {
	global $_db;
	$text = $_db->quote( $text );
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
	$request = [];
	foreach( $indexes as $index ) {
		if( isset($_REQUEST[$index]) ) {
			$value = $_REQUEST[$index];
			$value = is_array($value) ? array_map( 'washValue', $value ) : washValue($value);
		} elseif( isset($_FILES[$index]) ) {
			$value = $_FILES[$index];
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
				} else {
					$value = array_map( 'washValue', $value );
				}
			}
		} else {
			$value = null;
		}
		$request[$index] = $value;
		unset( $_GET[$index], $_POST[$index], $_REQUEST[$index], $_FILES[$index], $index, $value );
	}
	if( ! isset($indexes[1]) ) {
		$indexes = $indexes[0];
		$request = $request[$indexes];
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
/**
 * Prints out a json-serialized response of the object
 * @param Json $json
 */
function closeJson( Json $json ) {
	jsonHeader();
	@ob_clean();
	echo json_encode($json);
	exit;
}
