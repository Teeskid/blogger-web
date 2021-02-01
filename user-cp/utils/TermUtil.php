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
	global $_db;
	$exists = $_db->prepare( 'SELECT COUNT(id) FROM Term WHERE id=? LIMIT 1' );
	$exists->execute( [ $id ] );
	$exists = parseInt( $exists->fetchColumn() );
	if( $exists === 0 )
		return false;
	return true;
}
function updateTermCount() {
	global $_db;
	$my_query = $_db->prepare( 'SELECT id,name,permalink FROM Term WHERE id IN (SELECT termId FROM TermInfo WHERE rowType=?) ORDER BY id ASC' );
	$my_query->execute( [ $rowType ] );
	$my_query = $my_query->fetchAll();

	if( $rowType === Term::TYPE_CAT ) {
		foreach( $my_query AS &$term ) {
			$term_count = $_db->quote( $term->id );
			$term_count = $_db->query( 'SELECT COUNT(*) FROM %s WHERE category=%s LIMiT 1', posts, $term_count );
			$term_count = (int) $term_count->fetchColumn();
			$term->count = $term_count;
			$term_count = $_db->prepare( 'UPDATE TermInfo SET count=? WHERE termId=?' );
			$term_count->execute( [ $term->count, $term->id ] );
		}	
	} elseif($rowType === Term::TYPE_TAG) {
		foreach( $my_query AS &$term ) {
			$term_count = $_db->quote( $term->id );
			$term_count = $_db->query( 'SELECT COUNT(*) FROM %s WHERE folderInfoId=(SELECT id FROM %s WHERE termId=%s LIMiT 1)', $_db->term_relationships, TermInfo, $term_count );
			$term_count = (int) $term_count->fetchColumn();
			$term->count = $term_count;
			$term_count = $_db->prepare( 'UPDATE TermInfo SET count=? WHERE termId=?' );
			$term_count->execute( [ $term->count, $term->id ] );
		}
	}

}
