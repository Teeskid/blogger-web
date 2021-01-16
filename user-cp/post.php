<?php
/**
 * Posts Management Page
 * 
 * View all blog posts from this pages
 * 
 * Links to to other related management pages like tags and categories
 * @package Sevida
 * @subpackage Administration
 */
/** Include blog bootstrap file and related utilities */
require( dirname(__FILE__) . '/Load.php' );
require( ABSPATH . BASE_UTIL . '/HtmlUtil.php' );
/**
 * Sreen options
 * @var object
 */
$option = request( 'cat', 'tab', 'sot' );
/**
 * Instantiate the page object including the options
 */
$_page = sprintf( '/user-cp/post.php?tab=%s&sot=%s&cat=%s', $option->tab, $option->sot, $option->cat );
$_page = new Page( 'Posts', $_page );
/** Create a where clause base on $option->tab */
$where = [ 'Post.rowType=?' ];
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
/** Fallback for no category option */
if( $option->cat != 0 )
	$where[] = 'Post.category=' . $db->quote( $option->cat );
/** Create an order-by clause phrase */
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
/**
 * Pagination backends: We use the $where clause here too since we are fetching a tab
 * independently
 */
$paging = $db->prepare( 'SELECT COUNT(*) FROM Post WHERE ' . $where );
$paging->execute( [ 'post' ] );
$paging = parseInt( $paging->fetchColumn() );
$paging = new Paging( 20, $paging );
/** Fetch the posts from database applying all requested options */
$postList = $db->prepare( sprintf(
	'SELECT Post.id, Post.title, Post.permalink, Post.excerpt, PostMeta.metaValue as thumbnail, IF(Post.author IN (?, NULL), ?, Person.userName) as author, IFNULL(Term_A.title, ?) AS category, Post.posted, GROUP_CONCAT(?, Term_B.title) AS labels FROM Post LEFT JOIN Term Term_A ON Term_A.id=Post.category ' .
	'LEFT JOIN Person ON Person.id=Post.author LEFT JOIN Term Term_B ON EXISTS(SELECT * FROM TermLink WHERE TermLink.termId=Term_B.id AND TermLink.postId=Post.id) LEFT JOIN PostMeta ON PostMeta.postId=Post.thumbnail AND PostMeta.metaKey=? WHERE %s GROUP BY Post.id ORDER BY %s LIMIT %s', $where, $order, $paging->getLimit()
) );
$postList->execute( [ $_login->userId, 'You', 'Uncategorized', ' ', 'media_metadata', 'post' ] );
$postList = $postList->fetchAll( PDO::FETCH_CLASS, 'Post' );
$postList = array_values($postList);
// Categories for selection widget in screen option form
$termList = Term::getList( 'cat' );
/** All tabs and sort options available */
$tabsData = [ 'all' => 'All', 'public' => 'Public', 'draft' => 'Draft' ];
$sortData = [ 'nameAsc' => 'Name Ascending', 'nameDesc' => 'Name Descending', 'dateAsc' => 'Date Ascending', 'dateDesc' => 'Date Descending' ];
// Begin the page
include( 'html-header.php' );
?>
<nav aria-label="breadcrumb">
	<ol class="breadcrumb my-4">
		<li class="breadcrumb-item"><a href="index.php">Home</a></li>
		<li class="breadcrumb-item active" aria-current="page">Posts</li>
	</ol>
</nav>
<div class="row mb-3">
	<div class="col-sm">
		<div class="page-header">
			<h2>Posts</h2>
		</div>
	</div>
	<div class="col-sm-auto mb-3 mb-md-0 text-center">
		<div role="group" class="btn-group">
			<a href="post-new.php" class="btn btn-primary btn-sm">Create</a>
			<a href="post-new.php" class="btn btn-info btn-sm">Import</a>
			<a href="post-new.php" class="btn btn-success btn-sm">Export</a>
		</div>
	</div>
	<div class="w-100"></div>
	<div class="col-sm-5 col-md-4 mb-md-3">
		<div class="card mb-3">
			<div class="card-header">Screen Options</div>
			<div class="card-header">
				<ul class="nav nav-tabs card-header-tabs">
					<?php
					/** The tab widget children */
					foreach( $tabsData as $index => $entry ) {
						$entry = escHtml($entry);
					?>
					<li role="presentation" class="nav-item">
						<a role="tab" class="nav-link<?=( $option->tab === $index ? ' active" aria-current="true' : '' )?>" target="_self" href="?tab=<?=$index?>"><?=$entry?></a>
					</li>
					<?php
					}
					?>
				</ul>
			</div>
			<div class="card-body">
				<form action="<?=escHtml($_SERVER['PHP_SELF'])?>" method="get">
					<input type="hidden" name="tab" value="<?=$option->tab?>" />
					<div class="mb-3">
						<label for="select-category" class="form-label">Select Category</label>
						<select class="form-select" id="select-category" name="cat">
							<option value="0">All</option>
							<?php
							/** Category selection widget */
							foreach( $termList as $index => $entry ) {
								$entry->id = parseInt( $entry->id );
								$entry->title = escHtml($entry->title);
							?>
							<option value="<?=$entry->id?>"<?=($option->cat===$entry->id?' selected':'')?>><?=$entry->title?></option>
							<?php
							}
							?>
						</select>
					</div>
					<div class="mb-3">
						<label for="select-sot" class="form-label">Sort By</label>
						<select class="form-select" id="select-sot" name="sot">
							<?php
							/** Sorting customization : It defaults to dateDesc */
							foreach( $sortData as $index => $entry ) {
							?>
							<option value="<?=$index?>"<?=($option->sot===$index?' selected':'')?>><?=$entry?></option>
							<?php
							}
							?>
						</select>
					</div>
					<button type="submit" class="btn btn-primarymb-3">Apply Filter</button>
				</form>
			</div>
		</div>
		<div class="card mb-3 text-dark">
			<div class="card-header">Other Links</div>
			<div class="list-group" role="group">
				<a class="list-group-item" href="term.php?rowType=tag">Post Tags</a>
				<a class="list-group-item" href="term.php?rowType=cat">Post Categories</a>
				<a class="list-group-item" href="#">Post Comments</a>
			</div>
		</div>
	</div>
	<div class="col-sm-7 col-md-8">
		<?php
		if( isset($postList[0]) ) {
			echo '<div id="posts">';
			$icons = [ 'posted' => 'calendar', 'author' => 'user', 'category' => 'folder', 'labels' => 'link' ];
			$icons = array_map( 'icon', $icons );
			$icons = (object) $icons;
			foreach( $postList as $index => $entry ) {
				$entry->title = escHtml($entry->title);
				if( ! $entry->excerpt )
				$entry->excerpt = '---';
				$entry->excerpt = escHtml($entry->excerpt);
				$entry->category = escHtml($entry->category);
				$entry->author = escHtml($entry->author);
				$entry->posted = parseDate( $entry->posted );
				$entry->permalink = escHtml( Rewrite::postUri( $entry ) );
				$entry->posted = $entry->posted->month . ', ' . $entry->posted->year;
				$entry->posted = escHtml($entry->posted);
				$entry->thumbnail = json_decode($entry->thumbnail);
				$entry->thumbnail = Media::getImage( $entry->thumbnail, 'small' );
				$entry->thumbnail = escHtml($entry->thumbnail);
				if( ! $entry->labels )
					$entry->labels = '---';
				$entry->labels = escHtml($entry->labels);
		?>
		<div data-id="<?=$entry->id?>" class="card mb-3 g-0">
			<div class="row">
				<div class="col-auto text-end">
					<img class="m-2 img-thumbnail" src="<?=$entry->thumbnail?>" alt="..." />
				</div>
				<div class="col ps-0">
					<div class="card-body pt-2 ps-0">
						<h4 class="card-title"><?=++$index . '. ' . $entry->title?></h4>
						<p class="card-text"><?=$entry->excerpt?></p>
						<p class="card-text">
							<span class="label label-primary"><?=$icons->posted . ' ' . $entry->posted?></span>
							<span class="label label-primary"><?=$icons->author . ' ' . $entry->author?></span>
							<span class="label label-primary"><?=$icons->category . ' ' . $entry->category?></span>
							<span class="label label-primary"><?=$icons->labels . ' '. $entry->labels?></span>
						</p>
						<a href="<?=$entry->permalink?>" class="card-link text-success">View</a>
						<a href="#" data-action="modify" class="card-link">Edit</a>
						<a href="#" data-action="unlink" class="card-link text-danger">Delete</a>
					</div>
				</div>
			</div>
		</div>
		<?php
			}
			unset($icons);
			echo '</div>';
		} else {
			echo '<div class="alert alert-info text-center">No data available</div>';
		}
		unset( $postList, $entry );
		?>
	</div>
</div>
<?php
doHtmlPaging( $paging, $_page->path );
// return to this page after doind whatever
$redirect = urlencode($_page->path);
$_page->addPageMeta( Page::META_JS_FILE, 'js/jquery.action-button.js' );
$_page->addPageMeta( Page::META_JS_CODE, <<<EOS
$(document).ready(function() {
	$("#posts a[data-action]").actionBtn({
		unlink: "../api/post-edit.php",
		modify: "post-edit.php?action=modify&id=[id]&redirect=$redirect"
	});
});
EOS
);
include( 'html-footer.php' );
