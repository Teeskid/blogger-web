<?php
/**
 * Users Dashboard
 *
 * Index page for logged in users. Users
 * @package Sevida
 * @subpackage Administration
 */
/** Load boostrap file and utilities */
require( dirname(__FILE__) . '/Load.php' );
require( ABSPATH . BASE_UTIL . '/HtmlUtil.php' );
require( ABSPATH . USER_UTIL . '/PostUtil.php' );
loadPostConstants();

/** Some site statistics card */
$tableStats = [
	'post' => [ 'Posts', 'post.php' ],
	'page' => [ 'Pages', 'page.php' ],
	'media'=> [ 'Files', 'uploads.php' ]
];
/** Fetch the number of items in the tables */
$rowCounts = $db->prepare(
	'SELECT rowType, COUNT(*) AS rowCount FROM Post WHERE rowType=? UNION ' .
	'SELECT rowType, COUNT(*) AS rowCount FROM Post WHERE rowType=? UNION ' .
	'SELECT rowType, COUNT(*) AS rowCount FROM Post WHERE rowType=? '
);
$rowCounts->execute( [ 'post', 'page', 'media' ] );
$rowCounts = $rowCounts->fetchAll( PDO::FETCH_KEY_PAIR );
$rowCounts = array_map( 'notEmpty', $rowCounts );

$_page = new Page( 'Dashboard', USERPATH . '/index.php' );
include( 'html-header.php' );
?>
<h2 class="my-2">Dashboard</h2>
<div class="row mb-3 gy-3">
	<div class="col-sm-6">
		<div class="card">
			<h3 class="card-header">At A Glance</h3>
			<div class="card-body">
				<div class="row align-items-end text-center gx-1">
					<?php
					foreach( $tableStats AS $index => $entry ) {
						$entry[2] = $rowCounts[$index] ?? 0;
					?>
					<div class="col border">
						<span class="display-1 d-block mb-1"><?=$entry[2]?></span>
						<span class="display-5 d-block mb-1"><?=$entry[0]?></span>
						<a href="<?=$entry[1]?>" class="btn btn-primary btn-sm d-block mb-2">View</a>
					</div>
					<?php
					}
					unset( $tableStats, $rowCounts );
					?>
				</div>
			</div>
		</div>
	</div>
	<div class="col-sm-6">
		<div class="card">
			<h3 class="card-header">Quick Draft</h3>
			<div class="card-body">
				<form id="postForm">
					<input type="hidden" name="action" value="create" />
					<input type="hidden" name="status" value="draft" />
					<div class="mb-3">
						<label class="form-label" for="title">Post Title</label>
						<input class="form-control" type="text" id="title" name="title" minlength="10" maxlength="50" pattern="<?=substr(REGEX_POST_TITLE, 1, -1)?>" required />
						<div class="form-text">Post titles can only contain letters and numbers</div>
					</div>
					<div class="mb-3">
						<label class="form-label" for="content">What's on your mind ?</label>
						<textarea class="form-control" rows="5" id="content" name="content" required></textarea>
					</div>
					<button type="submit" class="btn btn-primary" name="submit">Save Draft</button>
				</form>
			</div>
		</div>
	</div>
</div>
<?php
$_page->addPageMeta( Page::META_JS_FILE, USERPATH . '/js/async-form.js' );
$_page->addPageMeta( Page::META_JS_CODE, <<<'EOS'
$(document).ready(function() {
	window.SeForm.call(document.getElementById("postForm"), {
			url: "../api/post-edit.php", target: "post.php?tab=draft"
	});
});
EOS
);
include( 'html-footer.php' );
