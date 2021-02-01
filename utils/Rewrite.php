<?php
/**
 * URL Rewrite Controler
 * @package Sevida
 * @subpackage Utilities
 */
class Rewrite {
	const PAGE_SYNTAX = '/%name%/';
	const TERM_SYNTAX = '/%type%/%name%/';
	const USER_SYNTAX = '/user/%name%/';
	const POST_SYNTAX = [
		'/%year%/%month%/%name%/',
		'/post/%name%/',
		'/%name%/'
	];

	const P_YYMM = 0;
	const P_POST = 1;
	const P_ROOT = 2;

	public $rewriteCode = [
		'%year%',
		'%month%',
		'%day%',
		'%hour%',
		'%minute%',
		'%second%',
		'%name%',
		'%id%'
	];
	public $patternString = [
		'([0-9]{4})',
		'([0-9]{1,2})',
		'([0-9]{1,2})',
		'([0-9]{1,2})',
		'([0-9]{1,2})',
		'([0-9]{1,2})',
		'([a-zA-Z0-9-]+)',
		'([0-9]+)'
	];
	public $replaceString = [
		'year=',
		'month=',
		'day=',
		'hour=',
		'minute=',
		'second=',
		'name=',
		'page=',
	];
	public $rules;

	function __construct() {
		$this->rules = [];
	}
	public function addToRules( Rule $rule ) {
		array_push( $this->rules, $rule );
	}
	public static function pageUri( Page $page ) : string {
		$permalink = BASEURI . SELF::PAGE_SYNTAX;
		$permalink = str_replace( '%name%', $page->permalink, $permalink );
		return $permalink;
	}
	public static function postUri( Post $post ) : string {
		global $_cfg;
		$permalink = BASEURI . SELF::POST_SYNTAX[$_cfg->permalink];
		$permalink = str_replace( '%name%', $post->permalink, $permalink );
		$permalink = str_replace( '%year%', $post->datePosted->year, $permalink );
		$permalink = str_replace( '%month%', $post->datePosted->month, $permalink );
		$permalink = str_replace( '%day%', $post->datePosted->day, $permalink );
		$permalink = str_replace( '%id%', $post->id, $permalink );
		return $permalink;
	}
	public static function userUri( string $username ) : string {
		$permalink = BASEURI . SELF::USER_SYNTAX;
		$permalink = str_replace( '%name%', $username, $permalink );
		return $permalink;
	}
	public static function termUri( Term $term ) : string {
		$permalink = BASEURI . SELF::TERM_SYNTAX;
		$permalink = str_replace( '%name%', $term->permalink, $permalink );
		$permalink = str_replace( '%type%', $term->rowType == Term::TYPE_CAT ? 'category' : Term::TYPE_TAG, $permalink );
		return $permalink;
	}
}