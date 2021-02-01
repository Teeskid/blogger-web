<?php
/**
 * Blog Configuration Model
 *
 * @package Sevida
 * @subpackage Utilities
 */
class Config {
	/**
	 * @var string
	 */
	const REGEX_USERNAME = '#[^<>]{5,}#';
	/**
	 * @var string
	 */
	public $blogName;
	/**
	 * @var string
	 */
	public $blogEmail;
	/**
	 * @var string
	 */
	public $blogDesc;
	/**
	 * @var string
	 */
	public $blogDate;
	/**
	 * @var string
	 */
	public $permalink;
	/**
	 * @var string
	 */
	public $installed;
	/**
	 * @var bool
	 */
	public $searchable;
	/**
	 * @var string
	 */
	public $about;

	/**
	 * Config Constructor
	 * @param array $data [optional]
	 */
	final public function __construct( array $data = [] ) {
		if( ! empty($data) ) {
			foreach( $data as $index => $entry )
				$this->$index = $entry;
			$this->permalink = (int) $this->permalink;
			$this->searchable = $this->searchable === 'true';
		}
	}
	/**
	 * Loads blog configuration fields
	 * @param array $fields
	 */
	public function loadFields( string ...$fields ) {
		global $_db;
		$fields = $_db->quoteList( $fields );
		try {
			$config = $_db->prepare( "SELECT metaKey, metaValue FROM Config WHERE metaKey IN ($fields)" );
			$config->execute();
			if( 0 === $config->rowCount() )
				throw new Exception();
			$config = $config->fetchAll( PDO::FETCH_KEY_PAIR );
			$this->__construct($config);
		} catch( Exception $e ) {
		}
	}
}
