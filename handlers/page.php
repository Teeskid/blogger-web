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
require( ABSPATH . BASE_UTIL . '/UIUtil.php' );

$page  = $db->prepare( 'SELECT * FROM Post WHERE id=:value OR permalink=:value LIMIT 1' );
$page->execute( $theValue );
$page = $db->fetchClass( $page, 'Page' );
$page->permalink = Rewrite::pageUri( $page );

$theCrumb = [ [ BASEPATH . '/', 'Home' ] ];
$theCrumb[] = $page->title;

// parseBBCode( $entry->content );

$_page = new Page( $page->title, $page->permalink );
$_page->setMetaItem( Page::META_CSS_LOAD, 'post' );
include( ABSPATH . BASE_UTIL . '/HeadHtml.php' );
htmBreadCrumb( $theCrumb );
?>
<div class="post">
	<div class="post-head">
		<h2><?=htmlspecialchars($page->title)?></h2>
	</div>
	<div id="postBar" class="btn-group">
<?php
if( isLoggedIn() ) {
	$return = rawurlencode($_page->path);
?>
		<a class="btn btn-sm" href="<?=USERPATH?>/page-edit.php?id=<?=$page->id?>&action=modify&redirect=<?=$return?>" target="_blank"><?=icon('edit')?> Edit</a>
		<a class="btn btn-sm" href="<?=USERPATH?>/page-edit.php?id=<?=$page->id?>&action=delete&redirect=<?=$return?>" target="_blank"><?=icon('trash')?> Delete</a>


		<a class="btn btn-sm" href="#" onclick="window.print()"><?=icon('print')?> Print</a>
	</div>
	<div class="post-content"><p><?=$page->content?></p></div>
</div>
<?php
include( ABSPATH . BASE_UTIL . '/TailHtml.php' );
