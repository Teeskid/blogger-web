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
class Post {
	public $id;
	public $thumbnail;
	public $category;
	public $author;
	public $title;
	public $permalink;
	public $content;
	public $excerpt;
	public $posted;
	public $modified;
	public $status;
	public $password;
	public $viewCount;

	public static function findId( string $value ) : int {
		global $db;
		$postId = $db->prepare( 'SELECT id FROM Post WHERE permalink=? LIMIT 1' );
		$postId->execute( [ $value ] );
		$postId = parseInt( $postId->fetchColumn() );
		return $postId;
	}
}