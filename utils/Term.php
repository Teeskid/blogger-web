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
class Term {
	public $id;
	public $master;
	public $title;
	public $permalink;
	public $rowType;
	public $childCount;

	function __construct( string $title = null, string $permalink = null ) {
		if( $title )
			$this->title = $title;
		if( $permalink )
			$this->permalink = $permalink;
	}
	public static function getList( string $rowType, $notId = false ) : array {
		global $db;
		$mQuery = $db->prepare( 'SELECT id, master, title FROM Term WHERE rowType=? AND id != ? ORDER BY IF(id=1, 100, title)' );
		$mQuery->execute( [ $rowType, $notId ] );
		$mQuery = $mQuery->fetchAll();
		return $mQuery;
	}
	public static function findId( string $permalink ) : int {
		global $db;
		$termId = $db->prepare( 'SELECT id FROM Term WHERE permalink=? LIMIT 1' );
		$termId->execute( [ $permalink ] );
		$termId = parseInt( $termId->fetchColumn() );
		return $termId;
	}

}