<?php
/**
 * Terms Fetcher
 * 
 * Fetches terms from the database and return a json response
 * 
 * Paging and sizing is supported
 * 
 * @package Sevida
 * @subpackage Api
 */
/** Load blog bootstrap file */
require( __DIR__ . '/Load.php' );

/** Collect response options */
$option = request( 'id', 'rowType' );
if( ! ( $option->id || $option->rowType ) )
	die();

$json = new Json();

// Where clause for our query
$where = [ 'rowType=' . $_db->quote( $option->rowType ) ];
// Incase a single item is requested
if( $option->id )
	$where[] = 'id=' . $_db->quote( $option->id );
$where = implode( ' AND ', $where );

// Sorting phrase
$order = 'childCount ASC, title ASC';

// Limit the query by the optiobs requested
$limit = '0,5';

/** Fetch the terms */
$mTerms = $_db->query( "SELECT id, term, title, about, childCount FROM Term WHERE $where ORDER BY $order LIMIT $limit" );

$json->setMessage( $mTerms->fetchAll() );
$json->determineSuccess();
unset( $mTerms );

/** Send back the response */
closeJson( $json );
