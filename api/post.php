<?php
/**
 * Post Query Api
 * @package sevida
 * @subpackage Api
 */
require( __DIR__ . '/Load.php' );

$option = request( 'id', 'sortBy', 'maximum', 'page' );

if( $option->id ) {
	$postList = $_db->prepare( 'SELECT id, post, title, about, childCount FROM Term WHERE id=? LIMIT 1' );
	$postList->execute( [ $option->id ] );
	$postList = $postList->fetch(PDO::FETCH_ASSOC);
} else {
	$postList = 'SELECT a.id, a.title, a.excerpt, a.image FROM Post a LEFT JOIN PostMeta b ON b.postId=a.thumbnail WHERE rowType=? ORDER BY %s LIMIT %s';
	$postList = $_db->prepare( sprintf( $postList, 'a.viewCount', '0,5' ) );
	$postList->execute( ['post'] );
	$postList = $postList->fetchAll();
}
closeJson( $postList );
