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

$option = request( 'tab', 'subject', 'sort' );

$where = [ 'Post.subject=?' ];
switch( $option->tab ){
	case 'self':
		$where[] = 'Post.author=' . $db->quote($_login->userId);
		break;
	default:
		$option->tab = 'all';
}
switch( $option->subject ){
	case 'image':
		$where[] = sprintf( 'Post.mimeType IN (%s)', $db->quoteList(FORMAT_IMAGE) );
		break;
	case 'audio':
		$where[] = sprintf( 'Post.mimeType IN (%s)', $db->quoteList(FORMAT_AUDIO) );
		break;
	case 'video':
		$where[] = sprintf( 'Post.mimeType IN (%s)', $db->quoteList(FORMAT_VIDEO) );
		break;
	default:
		$option->subject = 'all';
		// $where[] = 'Post.mimeType != NULL';
}
switch( $option->sort ){
	case 'nameAsc':
		$sort = 'Post.title ASC';
		break;
	case 'nameDesc':
		$sort = 'Post.title DESC';
		break;
	case 'dateAsc':
		$sort = 'Post.posted ASC';
		break;
	default:
		$option->sort = 'dateDesc';
		$sort = 'Post.posted DESC';
}
$where = implode( ' AND ', $where );

$paging = $db->prepare( 'SELECT COUNT(*) FROM Post WHERE ' . $where );
$paging->execute( [ 'media' ] );
$paging = parseInt( $paging->fetchColumn() );
$paging = new Paging( 20, $paging );

$mediaList = $db->prepare(
	'SELECT Post.id, Post.title, Post.posted AS uploaded, PostMeta.metaValue AS metaValue, Person.userName AS uploader FROM Post LEFT JOIN PostMeta ON PostMeta.postId=Post.id AND PostMeta.metaKey=? ' .
	'LEFT JOIN Person ON Person.id=Post.author WHERE ' . $where . ' ORDER BY ' . $sort . ' LIMIT ' . $paging->getLimit()
);
$mediaList->execute( [ 'media_metadata', 'media' ] );
$mediaList = $mediaList->fetchAll( PDO::FETCH_CLASS, 'Media' );

$_page = sprintf( '/user-cp/media.php?tab=%s&type=%s&sort=%s', $option->tab, $option->subject, $option->sort );
$_page = new Page( 'Media Library', $_page );
$_page->setMetaItem( Page::META_CSS_CODE, <<<'EOS'
@media (max-width: 767px) {
	td h4 {
		font-weight:100;
		font-style:normal;
	}
	th:nth-child(4), td:nth-child(4), th:nth-child(5), td:nth-child(5) {
		display:none;
	}
}
@media (max-width: 991px) {
	th:nth-child(5), td:nth-child(5) {
		display:none;
	}
}
EOS
);
include( 'html-header.php' );
?>
<ul class="breadcrumb">
	<li><a href="index.php">Home</a></li>
	<li class="active">Media</li>
</ul>
<h2>Media Library <a href="media-new.php" role="button" class="badge">Upload</a></h2>
<div class="panel panel-primary">
	<div class="panel-heading">Screen Option</div>
	<div class="panel-body">
		<form role="form" class="form-inline" action="<?=$_SERVER['REQUEST_URI']?>" method="get">
			<input type="hidden" name="tab" value="<?=$option->tab?>" />
			<div class="form-group">
				<label for="select-format" class="control-label">Select Format</label>
				<select id="select-format" name="type" class="form-control">
<?php
foreach( [ 'nameAsc' => 'Name Ascending', 'nameDesc' => 'Name Descending', 'dateAsc' => 'Date Ascending', 'dateDesc' => 'Date Descending' ] as $index => $entry ) {
?>
					<option value="<?=$index?>"<?=($index==$option->subject?' selected':'')?>><?=$entry?></option>
<?php
}
?>
				</select>
			</div>
			<div class="form-group">
				<label for="select-sort" class="control-label">Sort By</label>
				<select id="select-sort" name="sort" class="form-control">
<?php
foreach( [ 'nameAsc' => 'Name Ascending', 'nameDesc' => 'Name Descending', 'dateAsc' => 'Date Ascending', 'dateDesc' => 'Date Descending' ] as $index => $entry ) {
?>
					<option value="<?=$index?>"<?=($index==$option->sort?' selected':'')?>><?=$entry?></option>
<?php
}
?>
				</select>
			</div>
			<div class="form-group">
				<button type="submit" class="btn btn-primary">Apply Filter</button>
			</div>
		</form>
	</div>
</div>
<div class="panel panel-info">
	<div class="panel-heading">Uploaded Files</div>
	<ul class="nav nav-tabs">
<?php
foreach( [ 'all' => 'All', 'self' => 'By You' ] as $index => $entry ) {
?>
		<li role="presentation"<?=($option->tab==$index?' class="active"':'')?>><a role="tab" target="_self" href="?tab=<?=$index?>"><?=$entry?></a></li>
<?php
}
?>
	</ul>
	<table id="medialist" class="table table-striped table-hover">
		<tr>
			<th class="text-center" width="20">#</th>
			<th>File Name</th>
			<th width="50"></th>
			<th>Uploaded By</th>
			<th>Date</th>
		</tr>
<?php
if( ! isset($mediaList[0]) ) {
?>
	<tr class="text-center info"><td colspan="5">No data available</td></tr>
<?php
}
foreach( $mediaList as $index => &$entry ) {
	$metaValue = json_decode( $entry->metaValue );
	$metaValue->fileSize = htmlspecialchars( Media::formatSize( $metaValue->fileSize ?? 0 ) );
	$entry->uploaded = htmlspecialchars($entry->uploaded);
	$entry->uploader = htmlspecialchars($entry->uploader);
	$entry->title = htmlspecialchars($entry->title);
	$entry->domId = 'Bt_' . $entry->id;
	$entry->id = htmlentities($entry->id);
?>
		<tr data-id="<?=$entry->id?>">
			<td class="text-center"><?=++$index?></td>
			<td><?=$entry->title?></td>
			<td class="text-center">
				<div class="dropdown">
					<button id="<?=$entry->domId?>" type="button" aria-haspopup="true" aria-expanded="false" data-toggle="dropdown" class="btn btn-primary btn-xs">ACTION <span class="caret"></span></button>
					<ul class="dropdown-menu" aria-labelledby="<?=$entry->domId?>">
						<li><a href="#" data-action="modify">Edit</a></li>
						<li><a href="#" data-action="unlink" class="text-danger">Delete</a></li>
					</ul>
				</div>
			</td>
			<td><?=$entry->uploader?></td>
			<td><?=$entry->uploaded?></td>
		</tr>
<?php
	$index = $entry = null;
}
?>
	</table>
	<div class="panel-footer">
		<a href="#" id="select-all">Select All - </a>
		<span> With Selected: </span>
		<button type="submit" name="action" value="unlink" class="btn-small">Delete</button>
<?php
doHtmlPaging( $paging, $_page->path )
?>
	</div>
</div>
<?php
$_page->setMetaItem( Page::META_JS_FILE, 'js/jquery.action-button.js' );
$_page->setMetaItem( Page::META_JS_CODE, <<<'EOS'
	$(document).ready(function(){
		$("table#medialist a[data-action]").actionBtn({
			unlink: "../api/media-edit.php",
			modify: function(id) {
				window.location = 'media-edit.php?action=modify&id=' + id;
			}
		});
	});
EOS
);
include( 'html-footer.php' );
