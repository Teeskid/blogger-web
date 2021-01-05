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
function termExists( int $id ) : bool {
	global $db;
	$exists = $db->prepare( 'SELECT COUNT(id) FROM Term WHERE id=? LIMIT 1' );
	$exists->execute( [ $id ] );
	$exists = parseInt( $exists->fetchColumn() );
	if( $exists === 0 )
		return false;
	return true;
}
function updateTermCount() {
	global $db;
	$my_query = $db->prepare( 'SELECT id,name,permalink FROM Term WHERE id IN (SELECT termId FROM TermInfo WHERE subject=?) ORDER BY id ASC' );
	$my_query->execute( [ $subject ] );
	$my_query = $my_query->fetchAll();

	if( $subject === 'cat' ) {
		foreach( $my_query AS &$term ) {
			$term_count = $db->quote( $term->id );
			$term_count = $db->query( 'SELECT COUNT(*) FROM %s WHERE category=%s LIMiT 1', posts, $term_count );
			$term_count = (int) $term_count->fetchColumn();
			$term->count = $term_count;
			$term_count = $db->prepare( 'UPDATE TermInfo SET count=? WHERE termId=?' );
			$term_count->execute( [ $term->count, $term->id ] );
		}	
	} else if($subject === 'tag') {
		foreach( $my_query AS &$term ) {
			$term_count = $db->quote( $term->id );
			$term_count = $db->query( 'SELECT COUNT(*) FROM %s WHERE folderInfoId=(SELECT id FROM %s WHERE termId=%s LIMiT 1)', $db->term_relationships, TermInfo, $term_count );
			$term_count = (int) $term_count->fetchColumn();
			$term->count = $term_count;
			$term_count = $db->prepare( 'UPDATE TermInfo SET count=? WHERE termId=?' );
			$term_count->execute( [ $term->count, $term->id ] );
		}
	}

}
