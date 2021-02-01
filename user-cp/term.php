<?php
/**
 * Terms Management Page
 * 
 * Tools for managing terms : categories and tags, add delete and edit
 * also lists all terms available in database
 * 
 * @package Sevida
 * @subpackage Administration 
 */
/** Load blog boostrap files */
require( __DIR__ . '/Load.php' );
require( ABSPATH . BASE_UTIL . '/HtmlUtil.php' );

/** Collect our screen options */
$option = request( 'rowType', 'sort' );
switch( $option->rowType ) {
	case Term::TYPE_CAT:
		$termName = 'Category';
		$termIcon = 'folder-open';
		$catsList = Term::getList( Term::TYPE_CAT );
		break;
	case Term::TYPE_TAG:
		$termName = 'Tags';
		$termIcon = 'labels';
		$catsList = [];
		break;
	default:
		redirect( BASEURI . '/404.php' );
}
initHtmlPage( $termName, 'term.php?rowType=' . $option->rowType );

$paging = $_db->prepare( 'SELECT COUNT(*) FROM Term WHERE rowType=?' );
$paging->execute( [ $option->rowType ] );
$paging = parseInt( $paging->fetchColumn() );
$paging = new Paging( 10, $paging );

$termList = $_db->prepare(
	'SELECT a.id, IFNULL(b.title, ?) AS parentName, a.title, a.permalink, a.rowType, a.about, a.childCount FROM Term a ' .
	'LEFT JOIN Term b ON b.id=a.term WHERE a.rowType=? ORDER BY IF(a.id=1, 555, IFNULL(a.term, a.id)) DESC, b.title ASC LIMIT ' . $paging->getLimit()
);
$termList->execute( [ 'None', $option->rowType ] );
$termList = $termList->fetchAll( PDO::FETCH_CLASS, 'Term' );
$termList = array_values($termList);

include_once( __DIR__ . '/header.php' );
?>
<nav aria-label="breadcrumb">
	<ol class="breadcrumb my-3">
		<li class="breadcrumb-item"><a href="index.php">Home</a></li>
		<li class="breadcrumb-item active" aria-current="page"><?=$termName?></li>
	</ol>
</nav>
<div class="page-header">
	<h2><?=$termName?> <small><a href="#term" class="btn btn-primary btn-xs">Create</a></small></h2>
</div>
<div class="row">
	<div class="col-md-5 col-lg-4">
		<form id="term">
			<div class="card bg-light text-dark">
				<div class="card-header">Create New</div>
				<div class="card-body">
					<input type="hidden" id="action" name="action" value="create" />
					<input type="hidden" id="id" name="id" value="" />
					<input type="hidden" id="rowType" name="rowType" value="<?=escHtml($option->rowType)?>" />
					<div class="mb-3">
						<label for="title" class="form-label">Name</label>
						<input id="title" class="form-control" type="text" name="title" />
						<span class="help-block">The name is how it appears on your site.</span>
					</div>
<?php
if( $option->rowType === Term::TYPE_CAT ) {
?>
					<div class="mb-3">
						<label for="term" class="form-label">Parent Category</label>
						<select class="form-select" id="term" name="term">
							<option value="" selected>Standalone</option>
<?php
	foreach( $catsList as $entry ) {
		$entry->id = (int) $entry->id;
		if( $entry->id === 1 || $entry->term )
			continue;
		$entry->title = escHtml($entry->title);
		$entry->id = escHtml($entry->id);
?>
							<option value="<?=$entry->id?>"><?=$entry->title?></option>
<?php
	}
	unset($catsList, $entry);
?>
						</select>
						<span class="help-block">Hierachical parent.</span>
					</div>
					<div class="mb-3">
						<label for="about" class="form-label">Description</label>
						<textarea class="form-control" id="about" name="about" class="materialize-textarea"></textarea>
						<span class="help-block">The about is not prominent by default; however</span>
					</div>
<?php
} else {
?>
					<input type="hidden" id="term" name="term" value="" />
					<input type="hidden" id="about" name="about" value="" />
<?php
}
?>
					<div class="mb-3">
						<button id="submit" type="submit" name="submit" class="btn btn-primary">Create</button>
						<button id="cancel" type="reset" class="btn btn-default hide">Cancel</button>
					</div>
				</div>
			</div>
		</form>
	</div>
	<div class="col-md-7 col-lg-8">
<?php
if( isset($termList[0]) ) {
?>
		<table id="terms" class="table table-striped table-hover">
			<tr>
				<th width="20">#</th>
				<th>Name</th>
				<th class="text-center" width="70"></th>
<?php
	if( $option->rowType === Term::TYPE_CAT )
		echo '<th>Parent</th>';
?>
				<th>Description</th>
				<th class="text-center" width="30">Used</th>
			</tr>
<?php
	foreach( $termList as $index => $entry ) {
		$entry->permalink = Rewrite::termUri( $entry );
		$entry->permalink = escHtml($entry->permalink);
		$entry->parentName = escHtml($entry->parentName);
		$entry->title = escHtml($entry->title);
		$entry->about = escHtml($entry->about);
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
						<button id="<?=$entry->domId?>" type="button" data-bs-toggle="dropdown" class="btn btn-primary btn-xs" aria-haspopup="true" aria-expanded="false">MENU <span class="caret"></span></button>
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
		if( $option->rowType === Term::TYPE_CAT )
			echo '<td>', $entry->parentName, '</td>';
?>
				<td><?=$entry->about?></td>
				<td class="text-center"><?=$entry->childCount?></td>
			</tr>
<?php
	}
	echo '</table>';
} else {
	echo '<div class="alert alert-info text-center">No data available</div>';
}
doHtmlPaging( $paging, $HTML->path )
?>
	</div>
</div>
<?php
addPageJsFile( 'js/async-form.js' );
addPageJsFile( 'js/jquery.term-form.js' );
addPageJsFile( 'js/jquery.action-button.js' );
function onPageJsCode() {
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
include_once( __DIR__ . '/footer.php' );
