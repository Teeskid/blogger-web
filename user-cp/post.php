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

$option = request( 'cat', 'tab', 'sot' );

$_page = sprintf( '/user-cp/post.php?tab=%s&sot=%s&cat=%s', $option->tab, $option->sot, $option->cat );
$_page = new Page( 'Posts', $_page );

$where = [ 'Post.subject=?' ];
switch( $option->tab ) {
	case 'public':
		$where[] = 'Post.status=' . $db->quote( 'public' );
		break;
	case 'draft':
		$where[] = 'Post.status=' . $db->quote( 'draft' );
		break;
	default:
		$option->tab = 'all';
}
if( $option->cat != 0 )
	$where[] = 'Post.category=' . $db->quote( $option->cat );
$order = [];
switch( $option->sot ) {
	case 'nameAsc':
		$order[] = 'Post.title ASC';
		break;
	case 'nameDesc':
		$order[] = 'Post.title DESC';
		break;
	case 'dateAsc':
		$order[] = 'Post.posted ASC';
		break;
	default:
		$option->sot = 'dateDesc';
		$order[] = 'Post.posted DESC';
}
$order = implode( ',', $order );
$where = implode( ' AND ', $where );

$paging = $db->prepare( 'SELECT COUNT(*) FROM Post WHERE ' . $where );
$paging->execute( [ 'post' ] );
$paging = parseInt( $paging->fetchColumn() );
$paging = new Paging( 20, $paging );

$postList = $db->prepare( sprintf(
	'SELECT Post.id, Post.title, Post.permalink, Post.excerpt, PostMeta.metaValue as thumbnail, IF(Post.author IN (?, NULL), ?, Person.userName) as author, IFNULL(Term_A.title, ?) AS category, Post.posted, GROUP_CONCAT(?, Term_B.title) AS labels FROM Post LEFT JOIN Term Term_A ON Term_A.id=Post.category ' .
	'LEFT JOIN Person ON Person.id=Post.author LEFT JOIN Term Term_B ON EXISTS(SELECT * FROM TermLink WHERE TermLink.termId=Term_B.id AND TermLink.postId=Post.id) LEFT JOIN PostMeta ON PostMeta.postId=Post.thumbnail AND PostMeta.metaKey=? WHERE %s GROUP BY Post.id ORDER BY %s LIMIT %s', $where, $order, $paging->getLimit()
) );
$postList->execute( [ $_login->userId, 'You', 'Uncategorized', ' ', 'media_metadata', 'post' ] );
$postList = $postList->fetchAll( PDO::FETCH_CLASS, 'Post' );
$postList = array_values($postList);

$termList = Term::getList( 'cat' );

$tabsData = [ 'all' => 'All', 'public' => 'Public', 'draft' => 'Draft' ];
$sortData = [ 'nameAsc' => 'Name Ascending', 'nameDesc' => 'Name Descending', 'dateAsc' => 'Date Ascending', 'dateDesc' => 'Date Descending' ];

include( 'html-header.php' );
?>
<ol class="breadcrumb">
	<li><a href="index.php">Home</a></li>
	<li class="active">Posts</li>
</ol>
<div class="page-header">
	<h2>Posts <small><a href="post-new.php" class="btn btn-primary btn-xs">Create</a></small></h2>
</div>
<div class="row">
	<div class="col-sm-4">
		<div class="panel panel-primary">
			<div class="panel-heading">Screen Option</div>
			<ul class="nav nav-tabs">
<?php
foreach( $tabsData as $index => $entry ) {
?>
				<li role="presentation"<?=($option->tab===$index?' class="active"':'')?>>
					<a role="tab" target="_self" href="?tab=<?=$index?>"><?=htmlspecialchars($entry)?></a>
				</li>
<?php
}
?>
			</ul>
			<div class="panel-body">
				<form role="form" action="<?=$_SERVER['REQUEST_URI']?>" method="get">
					<div class="form-group">
						<label for="select-category" class="control-label">Select Category</label>
						<select class="form-control" id="select-category" name="cat">
							<option value="0">All</option>
<?php
foreach( $termList as $index => $entry ) {
	$entry->id = parseInt( $entry->id );
	$entry->title = htmlentities($entry->title);
?>
							<option value="<?=$entry->id?>"<?=($option->cat===$entry->id?' selected':'')?>><?=$entry->title?></option>
<?php
}
?>
						</select>
					</div>
					<div class="form-group">
						<label for="select-sot" class="control-label">Sort By</label>
						<select class="form-control" id="select-sot" name="sot">
<?php
foreach( $sortData as $index => $entry ) {
?>
							<option value="<?=$index?>"<?=($option->sot===$index?' selected':'')?>><?=$entry?></option>
<?php
}
?>
						</select>
					</div>
					<input type="hidden" name="tab" value="<?=$option->tab?>" />
					<p class="form-group">
						<button type="submit" class="btn btn-primary">Apply Filter</button>
					</p>
				</form>
			</div>
		</div>
		<div class="panel panel-primary">
			<div class="panel-heading">Other Links</div>
			<div class="list-group" role="group">
				<a class="list-group-item" href="term.php?subject=tag">Post Tags</a>
				<a class="list-group-item" href="term.php?subject=cat">Post Categories</a>
				<a class="list-group-item" href="#">Post Comments</a>
			</div>
		</div>
	</div>
	<div class="col-sm-8">
<?php
if( isset($postList[0]) ) {
	echo '<ul id="posts" class="media-list">';
	$icons = [ 'posted' => 'calendar', 'author' => 'user', 'category' => 'folder', 'labels' => 'link' ];
	$icons = array_map( 'icon', $icons );
	$icons = (object) $icons;
	foreach( $postList as $index => $entry ) {
		if( ! $entry->excerpt )
			$entry->excerpt = '---';
		if( ! $entry->labels )
			$entry->labels = '---';
		$entry->title = htmlspecialchars($entry->title);
		$entry->excerpt = htmlspecialchars($entry->excerpt);
		$entry->category = htmlspecialchars($entry->category);
		$entry->author = htmlspecialchars($entry->author);
		$entry->posted = parseDate( $entry->posted );
		$entry->permalink = htmlentities( Rewrite::postUri( $entry ) );
		$entry->posted = $entry->posted->month . ', ' . $entry->posted->year;
		$entry->posted = htmlspecialchars($entry->posted);
		$entry->thumbnail = json_decode($entry->thumbnail);
		$entry->thumbnail = Media::getImage( $entry->thumbnail, 'small' );
		$entry->thumbnail = htmlentities($entry->thumbnail);
?>
		<li data-id="<?=$entry->id?>" class="media">
			<div class="media-left">
				<img class="media-object" alt="..." src="<?=$entry->thumbnail?>" />
			</div>
			<div class="media-body">
				<h4 class="media-heading"><?=++$index?>. <a href="<?=$entry->permalink?>" target="_blank"><?=$entry->title?></a></h4>
				<p>
					<a href="#" data-action="modify" class="btn btn-xs btn-primary">Edit</a>
					<a href="#" data-action="unlink" class="btn btn-xs btn-danger">Delete</a><br>
					<span class="text-info"><?=$entry->excerpt?></span><br>
					<span class="label label-primary"><?=$icons->posted.' '.$entry->posted?></span>
					<span class="label label-primary"><?=$icons->author.' '.$entry->author?></span>
					<span class="label label-primary"><?=$icons->category.' '.$entry->category?></span>
					<span class="label label-primary"><?=$icons->labels.' '.$entry->labels?></span>
				</p>
			</div>
		</li>
<?php
	}
	unset($icons);
	echo '</ul>';
} else {
	echo '<div class="alert alert-info text-center">No data available</div>';
}
unset( $postList, $entry );
?>
	</div>
</div>
<?php
doHtmlPaging( $paging, $_page->path );
$redirect = json_encode( urlencode($_page->path) );
$_page->setMetaItem( Page::META_JS_FILE, 'js/jquery.action-button.js' );
$_page->setMetaItem( Page::META_JS_CODE, <<<'EOS'
$(document).ready(function(){
	$("ul#posts a[data-action]").actionBtn({
		unlink: "../api/post-edit.php",
		modify: "post-edit.php?action=modify&id=[id]&redirect=" + encodeURIComponent(document.URL)
	});
});
EOS
);
include( 'html-footer.php' );
