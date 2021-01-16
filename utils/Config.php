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

	final public function __construct( array $data = [] ) {
		foreach( $data as $index => $entry )
			$this->$index = $entry;
		$this->permalink = (int) $this->permalink;
		$this->searchable = $this->searchable === 'true';
	}
}
