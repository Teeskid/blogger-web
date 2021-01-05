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
class RewriteRule {
	public $regex;
	public $vars;
	public $endPoint;
	function __construct( int $endPoint, string $regex, array $vars ) {
		$regex = preg_quote( BASEPATH, '#' ) . $regex;
		$regex = str_replace( '#', '\\#', $regex );
		$regex = '#^' . $regex . '$#';
		$this->endPoint = $endPoint;
		$this->varName = $vars;
		$this->pattern = $regex;
	}
}