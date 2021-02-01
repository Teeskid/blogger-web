<?php
/**
 * Page Request Handler
 * @package Sevida
 * @subpackage Handlers
 */
if( ! defined('ABSPATH') )
	die();
require( ABSPATH . BASE_UTIL . '/HtmlUtil.php' );

$page  = $_db->prepare( 'SELECT * FROM Post WHERE id=:value OR permalink=:value LIMIT 1' );
$page->execute( $theValue );
$page = $_db->fetchClass( $page, 'Page' );
$page->permalink = Rewrite::pageUri( $page );

$theCrumb = [ [ BASEURI . '/', 'Home' ] ];
$theCrumb[] = $page->title;

// parseBBCode( $entry->content );

initHtmlPage( $page->title, $page->permalink );
$HTML->addPageMeta( Page::META_CSS_LOAD, 'post' );
include( __DIR__ . '/header.php' );
BreadCrumb( $theCrumb );
?>
<div class="post">
	<div class="post-head">
		<h2><?=escHtml($page->title)?></h2>
	</div>
	<div id="postBar" class="btn-group">
<?php
if( isLoggedIn() ) {
	$return = rawurlencode($HTML->path);
?>
		<a class="btn btn-sm" href="<?=USERURI?>/page-edit.php?id=<?=$page->id?>&action=modify&redirect=<?=$return?>" target="_blank"><?=icon('edit')?> Edit</a>
		<a class="btn btn-sm" href="<?=USERURI?>/page-edit.php?id=<?=$page->id?>&action=delete&redirect=<?=$return?>" target="_blank"><?=icon('trash')?> Delete</a>


		<a class="btn btn-sm" href="#" onclick="window.print()"><?=icon('print')?> Print</a>
	</div>
	<div class="post-content"><p><?=$page->content?></p></div>
</div>
<?php
include( __DIR__ . '/footer.php' );
