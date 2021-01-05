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
class Config {
	var $blogName, $blogDesc, $blogDate, $permalink, $installed, $searchable, $about;
	function __construct( array $data = [] ) {
		foreach( $data as $index => $entry ) {
			$index = $entry->metaKey;
			$this->$index = $entry->metaValue;
		}
		$this->permalink = (int) $this->permalink;
	}
}