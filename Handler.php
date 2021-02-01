<?php
/**
 * Blog Requests Handler
 * @package Sevida
 */
/**
 * Directory to index files
 * @var string
 */
define( 'INDICES',  '/indices' );

require( ABSPATH . BASE_UTIL . '/RewriteUtil.php' );

global $rewrite;
$rewrite = new Rewrite();
$GLOBALS['rewrite'] = $rewrite;

rewriteConstants();
loadRewriteRules();
rewriteTheRequest();

if( isset($_GET['misc']) ) {
	$index = EP_MISC;
} elseif( isset($_GET['post']) ) {
	$index = EP_POST;
} elseif( isset($_GET['blog']) ) {
	$index = EP_BLOG;
} elseif( isset($_GET['user']) ) {
	$index = EP_USER;
} else {
	if( isset($_GET['name']) ) {
		/**
		 * Resolve name conflict on root requests
		 */
		$index = $_db->prepare(
			'SELECT :EP_BLOG AS header FROM Term WHERE permalink=:permalink UNION ' .
			'SELECT IF(rowType=:PT_POST, :EP_POST, IF(rowType=:PT_PAGE, :EP_PAGE, NULL)) ' .
				'AS header FROM Post WHERE permalink=:permalink UNION ' .
			'SELECT :EP_USER AS header FROM Uzer WHERE userName=:permalink '
		);
		$index->execute([
			'PT_POST' => 'post',
			'PT_PAGE' => 'page',
			'EP_POST' => EP_POST,
			'EP_PAGE' => EP_PAGE,
			'EP_BLOG' => EP_BLOG,
			'EP_USER' => EP_USER,
			'permalink' => $_GET['name']
		]);
		$index = $index->fetchColumn();
		$index = (int) $index;
	} else {
		$index = 0;
	}
}
switch( $index ) {
	case EP_BLOG :
		include( ABSPATH . INDICES . '/blog.php' );
		break;
	case EP_POST :
		include( ABSPATH . INDICES . '/post.php' );
		break;
	case EP_USER :
		include( ABSPATH . INDICES . '/user.php' );
		break;
	case EP_MISC :
		include( ABSPATH . INDICES . '/misc.php' );
		break;
	default:
		if( preg_match( '#\.(map|css|jpg|png|js|json|mp3)$#i', $_SERVER['REQUEST_URI'] ) ) {
			objectNotFound();
			die();
		}
		redirect( BASEURI . '/404.php' );
}