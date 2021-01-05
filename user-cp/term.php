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

$option = request( 'subject', 'sort' );
switch( $option->subject ) {
	case 'cat':
		$termName = 'Category';
		$termIcon = 'folder-open';
		$catsList = Term::getList( 'cat' );
		break;
	case 'tag':
		$termName = 'Tags';
		$termIcon = 'labels';
		$catsList = [];
		break;
	default:
		redirect( BASEPATH . '/404.php' );
}
$_page = new Page( $termName, '/user-cp/term.php?subject=' . $option->subject );

$paging = $db->prepare( 'SELECT COUNT(*) FROM Term WHERE subject=?' );
$paging->execute( [ $option->subject ] );
$paging = parseInt( $paging->fetchColumn() );
$paging = new Paging( 10, $paging );

$termList = $db->prepare(
	'SELECT a.id, IFNULL(b.title, ?) AS parentName, a.title, a.permalink, a.subject, a.about, a.objects FROM Term a ' .
	'LEFT JOIN Term b ON b.id=a.master WHERE a.subject=? ORDER BY IF(a.id=1, 555, IFNULL(a.master, a.id)) DESC, b.title ASC LIMIT ' . $paging->getLimit()
);
$termList->execute( [ 'None', $option->subject ] );
$termList = $termList->fetchAll( PDO::FETCH_CLASS, 'Term' );
$termList = array_values($termList);

include( 'html-header.php' );
?>
<ol class="breadcrumb">
	<li><a href="index.php">Home</a></li>
	<li class="active"><?=$termName?></li>
</ol>
<div class="page-header">
	<h2><?=$termName?> <small><a href="#term" class="btn btn-primary btn-xs">Create</a></small></h2>
</div>
<div class="row">
	<div class="col-xs-12 col-sm-5 col-md-4">
		<form role="form" id="term" action="#">
			<div class="panel panel-primary">
				<div class="panel-heading">Create New</div>
				<div class="panel-body">
					<input type="hidden" id="action" name="action" value="create" />
					<input type="hidden" id="id" name="id" value="" />
					<input type="hidden" id="subject" name="subject" value="<?=htmlentities($option->subject)?>" />
					<div class="form-group">
						<label for="title" class="control-label">Name</label>
						<input id="title" class="form-control" type="text" name="title" />
						<span class="help-block">The name is how it appears on your site.</span>
					</div>
<?php
if( $option->subject === 'cat' ) {
?>
					<div class="form-group">
						<label for="master" class="control-label">Parent Category</label>
						<select class="form-control" id="master" name="master">
							<option value="" selected>Standalone</option>
<?php
	foreach( $catsList as $entry ) {
		$entry->id = (int) $entry->id;
		if( $entry->id === 1 || $entry->master )
			continue;
		$entry->title = htmlentities($entry->title);
		$entry->id = htmlentities($entry->id);
?>
							<option value="<?=$entry->id?>"><?=$entry->title?></option>
<?php
	}
	unset($catsList, $entry);
?>
						</select>
						<span class="help-block">Hierachical parent.</span>
					</div>
					<div class="form-group">
						<label for="about" class="control-label">Description</label>
						<textarea class="form-control" id="about" name="about" class="materialize-textarea"></textarea>
						<span class="help-block">The about is not prominent by default; however</span>
					</div>
<?php
} else {
?>
					<input type="hidden" id="master" name="master" value="" />
					<input type="hidden" id="about" name="about" value="" />
<?php
}
?>
					<div class="form-group">
						<button id="submit" type="submit" name="submit" class="btn btn-primary">Create</button>
						<button id="cancel" type="reset" class="btn btn-default hide">Cancel</button>
					</div>
				</div>
			</div>
		</form>
	</div>
	<div class="col-xs-12 col-sm-7 col-md-8">
<?php
if( isset($termList[0]) ) {
?>
		<table id="terms" class="table table-striped table-hover">
			<tr>
				<th width="20">#</th>
				<th>Name</th>
				<th class="text-center" width="70"></th>
<?php
	if( $option->subject === 'cat' )
		echo '<th>Parent</th>';
?>
				<th>Description</th>
				<th class="text-center" width="30">Used</th>
			</tr>
<?php
	foreach( $termList as $index => $entry ) {
		$entry->permalink = Rewrite::termUri( $entry );
		$entry->permalink = htmlentities($entry->permalink);
		$entry->parentName = htmlspecialchars($entry->parentName);
		$entry->title = htmlspecialchars($entry->title);
		$entry->about = htmlspecialchars($entry->about);
		$entry->domId = 'tr_' . $index;
?>
			<tr data-id="<?=$entry->id?>">
				<td><?=++$index?></td>
				<td><a href="<?=$entry->permalink?>"><h4><?=$entry->title?></h4></a></td>
				<td class="text-center">
<?php
	if( $entry->id !== 1 ) {
?>
					<div class="dropdown">
						<button id="<?=$entry->domId?>" type="button" data-toggle="dropdown" class="btn btn-primary btn-xs" aria-haspopup="true" aria-expanded="false">MENU <span class="caret"></span></button>
						<ul class="dropdown-menu" aria-labelledby="<?=$entry->domId?>">
							<li><a href="#" data-action="modify">Edit</a></li>
							<li><a href="#" data-action="unlink" class="text-danger">Delete</a></li>
						</ul>
					</div>
<?php
		} else {
			echo '<button type="button" class="btn btn-primary btn-xs disabled" disabled>MENU <span class="caret"></span></button>';
		}
?>
				</td>
<?php
		if( $option->subject === 'cat' )
			echo '<td>', $entry->parentName, '</td>';
?>
				<td><?=$entry->about?></td>
				<td class="text-center"><?=$entry->objects?></td>
			</tr>
<?php
	}
	echo '</table>';
} else {
	echo '<div class="alert alert-info text-center">No data available</div>';
}
doHtmlPaging( $paging, $_page->path )
?>
	</div>
</div>
<?php
$_page->setMetaItem( Page::META_JS_FILE, 'js/jquery.async-form.js' );
$_page->setMetaItem( Page::META_JS_FILE, 'js/jquery.term-form.js' );
$_page->setMetaItem( Page::META_JS_FILE, 'js/jquery.action-button.js' );
$_page->setMetaItem( Page::META_JS_CODE, <<<'EOS'
var termForm;
$(document).ready(function(){
	termForm = $("form#term").termEdit();
	$("table#terms a[data-action]").actionBtn({
		modify: function(id){
			termForm.trigger("async.loaded", id);
		},
		unlink: "../api/term-edit.php"
	});
});
EOS
);
include( 'html-footer.php' );
