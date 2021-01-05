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
function getLogin() {
	$payLoad = $_SESSION['__LOGIN__'] ?? $_REQUEST['jwt'] ?? false;
	if( ! $payLoad )
		return $payLoad;
	try {
		$session = \Firebase\JWT\JWT::decode( $payLoad, LOGGED_SALT, [ 'HS256' ] );
		if( ! $session )
			throw new Exception();
		$session = (object) [ 'userId' => $session->uid, 'session' => $session->sid, 'token' => $payLoad ];
	} catch( Exception $e ) {
		$session = false;
	}
	return $session;
}
function getReturnUrl( string $default = null ) : string {
	if( ! $default )
		$default = 'index.php';
	$ref = $_REQUEST['redirect'] ?? $_REQUEST['_ref'] ?? $default ;
	$ref = trim( $ref );
	return $ref;
}
function isGetRequest() : bool {
	if( $_SERVER['REQUEST_METHOD'] === 'GET' )
		return true;
	return false;
}
function isLocalServer() : bool {
	$HOST = $_SERVER['HTTP_HOST'];
	$ADDR = $_SERVER['SERVER_ADDR'];
	if( $ADDR === '::1' || true === strpos( $HOST, 'localhost' ) )
		return true;
	return false;
}
function isLoggedIn() : bool {
	global $_login;
	$_login = getLogin(); 
	if( $_login && password_verify( session_id(), $_login->session ) && $_login->userId !== 0 )
		return true;
	return false;
}
function isPostRequest() : bool {
	if( $_SERVER['REQUEST_METHOD'] === 'POST' )
		return true;
	return false;
}
function notEmpty( $xxx ) : bool{
	return ! empty( $xxx );
}
function parseInt( $int ) : int {
	return ( (int ) $int );
}
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
			} else if( $entry === 'true' || $entry === 'false' )
				$entry = (bool) ( $entry === 'true' );
		}
		$request[$index] = $entry;
	}
	unset( $entries, $index, $entry );
	return $request;
}
function makeExcerpt( string $str ) : string {
	$last = strpos( $str, '[' );
	if( $last > 300 ) $last = 250;
	if( $last < 250 ) $last = 250;
	$str = substr( $str, 0, $last );
	return $str;
}
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
function makePermalink( string $permalink ) : string {
	$permalink = preg_replace( '#\s*?\&\s*?#', ' and ', $permalink);
	$permalink = preg_replace( '#\s*?\+\s*?#', ' plus ', $permalink );
	$permalink = preg_replace( '#[^a-z0-9]+#i', '-', $permalink );
	$permalink = preg_replace( '#\-{2,}#', '-', $permalink );
	$permalink = preg_replace( '#(^-|-$)#', '', $permalink );
	$permalink = strtolower( $permalink );
	return $permalink;
}
function washValue( $value ) {
	$value = trim($value);
	if( is_numeric($value) && strpos( '+', $value ) === false )
		$value = (int) $value;
	return $value;
}
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
function testEmail( string $email ) : bool {
	if( preg_match('/^[a-z0-9\._]{3,}\@[a-z]{3,10}(\.[a-z]{3,10})?$/i', $email) )
		return true;
	return false;
}
function parseDate( string $theDate ) : object {
	$theDate = (object) date_parse($theDate);
	$theDate->year = str_pad( $theDate->year, 2, "0", STR_PAD_LEFT );
	$theDate->month = str_pad( $theDate->month, 2, "0", STR_PAD_LEFT );
	$theDate->day = str_pad( $theDate->day, 2, "0", STR_PAD_LEFT );
	return $theDate;
}