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
define( 'REQUIRE_LOGIN', true );
require_once( __DIR__ . '/Load.php' );

if( isPostRequest() ) {
	// Errors
	$error = [];
	// collect form data
	$comment_usmode = trim($_POST['mode']);
	if( $comment_usmode === 'anonymous' ) {
		$comment_name = 'Anonymous';
		$comment_email = '';
		$comment_website = '';
	} else {
		$comment_name = trim($_POST['name']);
		$comment_email = trim($_POST['email']);
		$comment_website = trim($_POST['website']);
	}
	$comment_author = (int) trim($_POST['author'] ?? 0);
	$comment_postid = (int) trim($_POST['postid']);
	$comment_parent = (int) trim($_POST['term']);
	$comment_redirect = trim($_POST['redirect']);
	$comment_content = trim($_POST['content']);
	$comment_ipaddr = trim($_SERVER['REMOTE_ADDR']);
	$comment_status = trim('public');
	$comment_date = formatDate( time() );
	if( empty($comment_name) || preg_match( '#[<>\(\)\"]#', $comment_name) )
		$error[] = 'Name cannot contain html tag, you can use BBCODEs instead';
	if( empty($comment_content) || $comment_content === 'Your Comment...' )
		$error[] = 'Invalid comment content';
	if( false && preg_match( '#[<">]#', $comment_content) )
		$error[] = 'Comment cannot contain html labels. Use BBCODEs instead';
	try {
		if( count($error) !== 0)
			throw new Exception( implode( '<br>', $error ) );
		$my_query = $_db->prepare_x( 'INSERT INTO %s (term,id,name,email,website,ipaddress,author,content,date,status) VALUES (?,?,?,?,?,?,?,?,?,?)', replies );
		$my_query->execute( [
			$comment_parent,
			$comment_postid,
			$comment_name,
			$comment_email,
			$comment_website,
			$comment_ipaddr,
			$comment_author,
			$comment_content,
			$comment_date,
			$comment_status
		] );
		$comment_id = $_db->lastInsertId();
		$comment_redirect .= '#comment-'.$comment_id;
	}
	catch( Exception $e )
	{
		sendError(
			sprintf(
				'Sorry, but we cold not submit your comment due to an error: %s.<br><a href="javascript:history.back(1)">Go back</a>',
				$e->getMessage()
			)
		);
	}
	redirect( $comment_redirect );
}
