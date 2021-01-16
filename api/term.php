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
require( dirname(__FILE__) . '/Load.php' );

/** Collect response options */
$option = request( 'id', 'rowType' );
if( ! ( $option->id || $option->rowType ) )
	die();

$response = new Response();

// Where clause for our query
$where = [ 'rowType=' . $db->quote( $option->rowType ) ];
// Incase a single item is requested
if( $option->id )
	$where[] = 'id=' . $db->quote( $option->id );
$where = implode( ' AND ', $where );

// Sorting phrase
$order = 'childCount ASC, title ASC';

// Limit the query by the optiobs requested
$limit = '0,5';

/** Fetch the terms */
$mTerms = $db->query( "SELECT id, master, title, about, childCount FROM Term WHERE $where ORDER BY $order LIMIT $limit" );

$response->setMessage( $mTerms->fetchAll() );
$response->determineSuccess();
unset( $mTerms );

/** Send back the response */
jsonOutput( $response );
