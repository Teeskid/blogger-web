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
require( dirname(__FILE__) . '/Load.php' );
require( ABSPATH . BASE_UTIL . '/UIUtil.php' );

$post = (int) trim($_REQUEST['id']);
try
{
	$postId = $db->quote($post);
	$post = $db->query( 'SELECT * FROM Post WHERE id=$postId' )->fetch();
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
