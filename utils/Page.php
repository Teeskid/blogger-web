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
class Page {
	public $id;
	public $title;
	public $permalink;
	public $content;
	public $status;
	public $password;
	public $posted;

	public $url;
	public $path;

	public $meta = [];

	const META_JS_CODE = 'js_labels';
	const META_JS_FILE = 'js_files';
	const META_CSS_CODE = 'css_labels';
	const META_CSS_FILE = 'css_files';
	const META_CSS_LOAD = 'css_load';
	const META_HEAD_TAG = 'head_labels';

	function __construct( string $title = null, string $path = null ) {
		if( ! empty($title) )
			$this->setTitle( $title );
		if( ! empty($path) )
			$this->setPath( $path );
	}
	function setTitle( string $title ) {
		$this->title = $title;
	}
	function setUrl( string $path ) {
		$this->url = BASE_URL . $path;
	}
	function setPath( string $path ) {
		$this->path = BASEPATH . $path;
		$this->setUrl( $this->path );
	}
	function addPageMeta( string $key, string ...$content ) {
		$meta = $this->meta[$key] ?? [];
		$meta = array_merge( $meta, $content );
		$this->meta[$key] = $meta;
	}
	function getMetaItem( string $key ) {
		$meta = $this->meta[$key] ?? [];
		return $meta;
	}
	public static function findId( string $value ) : int {
		global $db;
		$id = $db->prepare( 'SELECT id FROM Post WHERE rowType=? permalink=? LIMIT 1' );
		$id->execute( [ 'page', $value ] );
		$id = parseInt( $id->fetchColumn() );
		return $id;
	}}
