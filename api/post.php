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

$option = request( 'id', 'sortBy', 'maximum', 'page' );

if( $option->id ) {
	$postList = $db->prepare( 'SELECT id, master, title, about, objects FROM Term WHERE id=? LIMIT 1' );
	$postList->execute( [ $option->id ] );
	$postList = $postList->fetch(PDO::FETCH_ASSOC);
} else {
	$postList = 'SELECT a.id, a.title, a.excerpt, a.image FROM Post a LEFT JOIN PostMeta b ON b.postId=a.thumbnail WHERE subject=? ORDER BY %s LIMIT %s';
	$postList = $db->prepare( sprintf( $postList, 'a.views', '0,5' ) );
	$postList->execute( ['post'] );
	$postList = $postList->fetchAll();
}
jsonOutput( $postList );
