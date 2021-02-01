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
class Paging {
	public $numItems;
	public $perPage;
	public $pageLast;
	public $pageNow;
	function __construct( int $perPage, int $numItems, int $pageNow = -1 ) {
		$this->perPage = $perPage;
		$this->numItems = $numItems;
		$this->pageLast = (int) ceil( $numItems / $perPage );
		if( $this->pageLast < 1 )
			$this->pageLast = 1;
		if( $pageNow === -1 )
			$this->pageNow = request('page') ?? 1;
		else
			$this->pageNow = $pageNow;
		if( $this->pageNow > $this->pageLast )
			$this->pageNow = $this->pageLast;
		if( $this->pageNow < 1 )
			$this->pageNow = 1;
	}
	function getLimit() : string {
		$limit = sprintf( '%d, %d',  ($this->pageNow - 1) * $this->perPage, $this->perPage );
		return $limit;
	}
}
