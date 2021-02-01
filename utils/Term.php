<?php
/**
 * Term Model
 * @package Sevida
 * @subpackage Utilities
 */
class Term {
	const TYPE_CAT = 'cat';
	const TYPE_TAG = 'tag';
	public $term;
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
		global $_db;
		$mQuery = $_db->prepare( 'SELECT id, term, title FROM Term WHERE rowType=? AND id != ? ORDER BY IF(id=1, 100, title)' );
		$mQuery->execute( [ $rowType, $notId ] );
		$mQuery = $mQuery->fetchAll();
		return $mQuery;
	}
	public static function findId( string $permalink ) : int {
		global $_db;
		$termId = $_db->prepare( 'SELECT id FROM Term WHERE permalink=? LIMIT 1' );
		$termId->execute( [ $permalink ] );
		$termId = parseInt( $termId->fetchColumn() );
		return $termId;
	}

}