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
 * Defines ontants for post base based works
 */
function loadPostConstants() {
	define( 'REGEX_POST_TITLE', '#^[\w\d\s_]+$#' );
	define( 'POST_STATUS_ENUM', [ 'public', 'draft' ] );
}
function postExists( int $postId ) : bool {
	global $_db;
	$exists = $_db->prepare( 'SELECT COUNT(id) FROM Post WHERE id=? AND rowType=? LIMIT 1' );
	$exists->execute( [ $postId, 'post' ] );
	$exists = parseInt( $exists->fetchColumn() );
	if( $exists === 0 )
		return false;
	return true;
}