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
class Rewrite {
	const PAGE_SYNTAX = '/%permalink%/';
	const TERM_SYNTAX = '/%rowType%/%permalink%/';
	const USER_SYNTAX = '/user/%permalink%/';
	const POST_SYNTAX = [
		'/%year%/%month%/%permalink%/',
		'/post/%permalink%/',
		'/%permalink%/'
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
		'%permalink%',
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
		$permalink = BASEPATH . SELF::PAGE_SYNTAX;
		$permalink = str_replace( '%permalink%', $page->permalink, $permalink );
		return $permalink;
	}
	public static function postUri( Post $post ) : string {
		global $cfg;
		$permalink = BASEPATH . SELF::POST_SYNTAX[$cfg->permalink];
		$permalink = str_replace( '%permalink%', $post->permalink, $permalink );
		$permalink = str_replace( '%year%', $post->posted->year, $permalink );
		$permalink = str_replace( '%month%', $post->posted->month, $permalink );
		$permalink = str_replace( '%day%', $post->posted->day, $permalink );
		$permalink = str_replace( '%id%', $post->id, $permalink );
		return $permalink;
	}
	public static function userUri( string $username ) : string {
		$permalink = BASEPATH . SELF::USER_SYNTAX;
		$permalink = str_replace( '%permalink%', $username, $permalink );
		return $permalink;
	}
	public static function termUri( Term $term ) : string {
		$permalink = BASEPATH . SELF::TERM_SYNTAX;
		$permalink = str_replace( '%permalink%', $term->permalink, $permalink );
		$permalink = str_replace( '%rowType%', $term->rowType == 'cat' ? 'category' : 'tag', $permalink );
		return $permalink;
	}
}