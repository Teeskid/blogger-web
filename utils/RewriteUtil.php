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
function loadRules() {
	global $rewrite;
	$rewrite->addToRules( new Rule( EP_MISC, '/(robots\.txt|sitemap\.xml)/?', ['file'] ) );

	$rewrite->addToRules( new Rule( EP_POST, '/([0-9]{4})/([0-9]{2})/([a-zA-Z0-9-]+)/?', [ 'year', 'month', 'name' ] ) );
	$rewrite->addToRules( new Rule( EP_POST, '/post/([a-zA-Z0-9-]+)/?', ['name'] ) );
	$rewrite->addToRules( new Rule( EP_POST, '/([a-zA-Z0-9-]+)/?', [ 'name' ] ) );

	$rewrite->addToRules( new Rule( EP_USER, '/user/([a-zA-Z0-9-]+)/?', [ 'value' ] ) );
	
	$rewrite->addToRules( new Rule( EP_FILE, '/[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[a-zA-Z0-9-]+/([a-zA-Z0-9-]+)/?', ['attachment'] ) );
	$rewrite->addToRules( new Rule( EP_FILE, '/[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[a-zA-Z0-9-]+/attachment/([a-zA-Z0-9-]+)/?', ['attachment'] ) );
	$rewrite->addToRules( new Rule( EP_FILE, '/.?.+?/attachment/([a-zA-Z0-9-]+)/?', ['attachment'] ) );

	$rewrite->addToRules( new Rule( EP_BLOG, '/(search|author|category|tag)/([a-zA-Z0-9-]+)/?', [ 'trend', 'value'] ) );
	$rewrite->addToRules( new Rule( EP_BLOG, '/([0-9]{4})/([0-9]{1,2})/?', [ 'trend=month', 'year', 'month' ] ) );
	$rewrite->addToRules( new Rule( EP_BLOG, '/([0-9]{4})/?', [ 'trend=year', 'value' ] ) );
	$rewrite->addToRules( new Rule( EP_BLOG, '(?:/|/index\.php)', [ 'trend=latest' ] ) );
}
function requestRewrite( string $request, &$endPoint, &$query  ) : bool {
	global $rewrite;
	foreach( $rewrite->rules as $rule ) {
		if ( preg_match( $rule->pattern, $request, $values ) ){
			$endPoint = $rule->endPoint;
			$varName = $rule->varName;
			break;
		}
	}
	if( ! isset($values[0]) )
		return false;
	unset($values[0]);
	$values = array_values($values);
	$query = [];
	$values = array_values($values);
	foreach( $varName as $index => $varName ) {
		if( strpos( $varName, '=' ) !== false ) {
			$varName = explode( '=', $varName, 2 );
			$exValue = $varName[1] ?? null;
			$varName = $varName[0];
			$query[$varName] = $exValue;
			$values = array_merge( [NULL], $values );
			unset($exValue);
 		} else {
			$query[$varName] = $values[$index];
			unset($values[$index]);
		}
	}
	$query = array_merge( getParams($request), $query );
	return true;
}
