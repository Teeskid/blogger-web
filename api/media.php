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
require( dirname(__FILE__) . '/Load.php' );
require( ABSPATH . USER_UTIL . '/MediaUtil.php' );

mediaConstants();

$option = request( 'format', 'order', 'page', 'limit' );
if( ! $option->format )
	die();
$option->limit = (int) $option->limit;
$option->limit = $option->limit < 1 ? 1 : $option->limit;
$option->page = (int) $option->page;
$option->page = $option->page < 1 ? 1 : $option->page;

$where = [ 'a.subject=?' ];
switch( $option->format ) {
	case 'image':
		$where[] = 'a.mimeType IN(' . $db->quoteList(['image/jpeg','image/png']) . ')';
		break;
	default:
		$option->format = 'all';
}
$where = implode( ' AND ', $where );
switch( $option->order ) {
	case 'name':
		$order = 'a.title DESC';
		break;
	default:
		$order = 'a.posted DESC';
}

$limit = ( --$option->page . ', ' . $option->limit );


$response = $db->prepare( "SELECT a.id, a.title, a.mimeType, b.metaValue FROM Post a LEFT JOIN PostMeta b ON b.postId=a.id AND b.metaKey=? WHERE $where ORDER BY $order LIMIT $limit" );
$response->execute( [ 'media_metadata', 'media' ] );
$response = $response->fetchAll();

foreach( $response as $index => &$entry ) {
	$metaValue = json_decode($entry->metaValue);
	$entry->fileName = $metaValue->fileName ?? 'NULL';
	$entry->thumbnail = Media::getImage( $metaValue, 'small' );
	unset( $metaValue, $entry->metaValue );
}
$response = [ 'success' => true, 'data' => $response ];
jsonOutput( $response );
