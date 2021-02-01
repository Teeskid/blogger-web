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
require( __DIR__ . '/Load.php' );
require( ABSPATH . BASE_UTIL . '/HtmlUtil.php' );

$post = (int) trim($_REQUEST['id']);
try
{
	$postId = $_db->quote($post);
	$post = $_db->query( 'SELECT * FROM Post WHERE id=$postId' )->fetch();
	if(!$post) throw (new Exception('Post not found !'));
	$filename = prepare_makePermalink($post->title);
	header("Content-Disposition:attachment;filename=$filename.posts");
	print json_encode($post);
	$post = null;
	exit;
}
catch(Exception $e)
{
	die($e->getMessage());
}
