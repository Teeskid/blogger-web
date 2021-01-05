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
function postExists( int $postId ) : bool {
	global $db;
	$exists = $db->prepare( 'SELECT COUNT(id) FROM Post WHERE id=? AND subject=? LIMIT 1' );
	$exists->execute( [ $postId, 'post' ] );
	$exists = parseInt( $exists->fetchColumn() );
	if( $exists === 0 )
		return false;
	return true;
}