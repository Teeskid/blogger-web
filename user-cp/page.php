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

$option = request( 'tab', 'sot' );
$where = [ 'a.rowType=\'page\'' ];
switch( $option->tab ) {
	case 'public':
		$where[] = 'a.status=\'public\'';
		break;
	case 'draft':
		$where[] = 'a.status=\'draft\'';
		break;
	default:
		$option->tab = 'all';
}
$order = [];
switch( $option->sot ) {
	case 'nameAsc':
		$order[] = 'a.title ASC';
		break;
	case 'nameDesc':
		$order[] = 'a.title DESC';
		break;
	case 'dateAsc':
		$order[] = 'a.datePosted ASC';
		break;
	default:
		$option->sot = 'dateDesc';
		$order[] = 'a.datePosted DESC';
}
$order = implode( ',', $order );
$where = implode( ' AND ', $where );

$paging = $_db->query( 'SELECT COUNT(*) FROM Post a WHERE ' . $where );
$paging = parseInt( $paging->fetchColumn() );
$paging = new Paging( 20, $paging );

$pageList = $_db->prepare( sprintf(
	'SELECT a.id, a.title, a.permalink, a.excerpt, IF(a.author=? OR a.author=NULL, ?, c.userName) as author, IFNULL(b.title, ?) AS category, a.datePosted, GROUP_CONCAT(?, d.title) AS labels FROM Post a LEFT JOIN Term b ON b.id=a.category ' .
	'LEFT JOIN Uzer c ON c.id=a.author LEFT JOIN Term d ON EXISTS(SELECT * FROM TermLink e WHERE e.termId=d.id AND e.postId=a.id) WHERE %s GROUP BY a.id ORDER BY %s LIMIT %s', $where, $order, $paging->getLimit()
) );
$pageList->execute( [ $_usr->id, 'You', 'Uncategorized', ' ' ] );
$pageList = $pageList->fetchAll( PDO::FETCH_CLASS, 'Page' );
$pageList = array_values($pageList);

$tabsData = [ 'all' => 'All', 'public' => 'Public', 'draft' => 'Draft' ];
$sortData = [ 'nameAsc' => 'Name Ascending', 'nameDesc' => 'Name Descending', 'dateAsc' => 'Date Ascending', 'dateDesc' => 'Date Descending' ];

$HTML = sprintf( '/user-cp/page.php?tab=%s&sot=%s', $option->tab, $option->sot );
initHtmlPage( 'Pages', $HTML );
include_once( __DIR__ . '/header.php' );
?>
<div class="page-header">
	<h2>Pages <small><a href="page-edit.php?action=create" class="label label-primary">Create</a></small></h2>
</div>
<nav aria-label="breadcrumb">
	<ol class="breadcrumb my-3">
		<li class="breadcrumb-item"><a href="index.php">Home</a></li>
		<li class="breadcrumb-item active" aria-current="page">Pages</li>
	</ol>
</nav>
<div class="row">
	<div class="col-md-5 col-lg-4">
		<div class="card bg-light text-dark">
			<div class="card-header">Screen Option</div>
			<ul class="nav nav-tabs">
<?php
foreach( $tabsData as $index => $entry ) {
?>
				<li role="presentation"<?=($option->tab===$index?' class="active"':'')?>>
					<a role="tab" target="_self" href="?tab=<?=$index?>"><?=escHtml($entry)?></a>
				</li>
<?php
}
?>
			</ul>
			<div class="card-body">
				<form action="<?=$_SERVER['REQUEST_URI']?>" method="get">
					<div class="mb-3">
						<label for="select-sot" class="form-label">Sort By</label>
						<select class="form-select" id="select-sot" name="sot">
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
					<p class="mb-3">
						<button type="submit" class="btn btn-primary">Apply Filter</button>
					</p>
				</form>
			</div>
		</div>
	</div>
	<div class="col-md-7 col-lg-8">
<?php
if( ! empty($pageList) ) {
?>
		<table id="pages" class="table table-striped table-responsive">
			<tr>
				<th width="20">#</th>
				<th>Title</th>
				<th width="80"></th>
				<th>By</th>
				<th>Hits</th>
				<th width="100">Date</th>
			</tr>
<?php
	foreach( $pageList as $index => &$entry ) {
		$entry->id = (int) $entry->id;
		$entry->domId = 'pp_' . $entry->id;
		$entry->title = escHtml($entry->title);
		$entry->excerpt = escHtml($entry->excerpt);
		$entry->category = escHtml($entry->category);
		$entry->permalink = escHtml(Rewrite::pageUri( $entry ));
		$entry->author = escHtml($entry->author);
		$entry->datePosted = date_format( date_create($entry->datePosted), $_cfg->blogDate );
		$entry->datePosted = escHtml($entry->datePosted);
?>
			<tr data-id="<?=$entry->id?>">
				<td><?=++$index?></td>
				<td><h4><a href="<?=$entry->permalink?>" target="_blank"><?=$entry->title?></a></h4>
				</td>
				<td class="text-center">
					<div class="dropdown">
						<button id="<?=$entry->domId?>" type="button" aria-haspopup="true" aria-expanded="false" data-bs-toggle="dropdown" class="btn btn-primary btn-xs">ACTION <span class="caret"></span></button>
						<ul class="dropdown-menu" aria-labelledby="<?=$entry->domId?>">
							<li><a href="#" data-action="modify">Edit</a></li>
							<li><a href="#" data-action="unlink" class="text-danger">Delete</a></li>
						</ul>
					</div>
				</td>
				<td><?=$entry->author?></td>
				<td>2120</td>
				<td><?=$entry->datePosted?></td>
			</div>
<?php
$entry = null;
}
?>
		</table>
<?php
} else {
?>
		<div class="alert alert-info text-center">No data available</div>
<?php
}
doHtmlPaging( $paging, $HTML->path )
?>
	</div>
</div>
<?php
unset( $pageList, $entry );
addPageJsFile( 'js/jquery.action-button.js' );
function onPageJsCode() {
$(document).on("ready", "table#pages", function(){
	$("table#pages a[data-action]").actionBtn({
		unlink: "../api/page-edit.php",
		modify: "page-edit.php?action=modify&id={id}"
	});
});
EOS
);
include_once( __DIR__ . '/footer.php' );
