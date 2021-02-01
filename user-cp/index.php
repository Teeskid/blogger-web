<?php
/**
 * Users Dashboard
 *
 * Index page for logged in users. Users
 * @package Sevida
 * @subpackage Administration
 */
/** Load boostrap file and utilities */
require( __DIR__ . '/Load.php' );
require( ABSPATH . BASE_UTIL . '/HtmlUtil.php' );
require( ABSPATH . USER_UTIL . '/PostUtil.php' );

$_usr->userName = User::getFields( $_usr->id, 'userName' );
loadPostConstants();

/** Some site statistics card */
$tableStats = [
	'post' => [ 'Posts', 'post.php' ],
	'page' => [ 'Pages', 'page.php' ],
	'media'=> [ 'Files', 'uploads.php' ]
];
/** Fetch the number of items in the tables */
$rowCounts = $_db->prepare(
	'SELECT rowType, COUNT(*) AS rowCount FROM Post WHERE rowType=? UNION ' .
	'SELECT rowType, COUNT(*) AS rowCount FROM Post WHERE rowType=? UNION ' .
	'SELECT rowType, COUNT(*) AS rowCount FROM Post WHERE rowType=? '
);
$rowCounts->execute( [ 'post', 'page', 'media' ] );
$rowCounts = $rowCounts->fetchAll( PDO::FETCH_KEY_PAIR );
$rowCounts = array_map( 'notEmpty', $rowCounts );

initHtmlPage( 'Dashboard', 'index.php' );
include_once( __DIR__ . '/header.php' );
?>
<h2 class="my-4">Dashboard</h2>
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
	<?php
	if( $_usr->userName === null ) {
	?>
	<div class="col-sm-6">
		<div class="card">
			<h3 class="card-header">Create A Username</h3>
			<div class="card-body">
				<form id="uzerForm" method="post" action="#" class="needs-validation" novalidate>
					<input type="hidden" name="action" value="setname" />
					<p class="alert alert-info">To be able to use direct profile links, you must a create a username. Please try it now.</p>
					<div class="mb-3">
						<label class="form-label" for="userName">Desired Username</label>
						<input class="form-control" type="text" id="userName" name="userName" minlength="5" maxlength="20" value="admin" required />
						<div class="form-text">Only letters, numbers and underscores are allowed in usernames</div>
					</div>
					<button id="submit" type="submit" class="btn btn-primary" name="submit"><?=icon('check me-1')?> Submit</button>
				</form>
			</div>
		</div>
	</div>
	<?php
	}
	?>
	<div class="col-sm-6">
		<div class="card">
			<h3 class="card-header">Quick Draft</h3>
			<div class="card-body">
				<form id="postForm" method="post" action="#" class="needs-validation" novalidate>
					<input type="hidden" name="action" value="create" />
					<input type="hidden" name="status" value="draft" />
					<div class="mb-3">
						<label class="form-label" for="title">Post Title</label>
						<input class="form-control" type="text" id="title" name="title" minlength="10" maxlength="60" pattern="<?=substr(REGEX_POST_TITLE, 1, -1)?>" required />
						<div class="form-text">Post titles can only contain letters and numbers</div>
					</div>
					<div class="mb-3">
						<label class="form-label" for="content">What's on your mind ?</label>
						<textarea class="form-control" rows="5" id="content" name="content" required></textarea>
					</div>
					<button type="submit" class="btn btn-primary" id="submit" name="submit"><?=icon('check me-1')?> Save Draft</button>
				</form>
			</div>
		</div>
	</div>
</div>
<?php
addPageJsFile( 'js/async-form.js' );
/**
 * Add custom javascript
 */
function onPageJsCode() {
	global $_usr;
?>
document.addEventListener( "DOMContentLoaded", function() {
	<?php
	if( $_usr->userName === null ) {
	?>
	var usrForm = AsyncForm(document.getElementById("uzerForm"), {
		url: "../api/user-edit.php", success: function(r) {
			usrForm.formElem.closest(".col-sm-6").remove();
			delete usrForm;
		}
	});
	<?php
	}
	?>
	var asyncForm = AsyncForm(document.getElementById("postForm"), {
		url: "../api/post-edit.php", success: function(response){
			// window.location = "post.php?tab=draft"
		}
	});
});
<?php
}
include_once( __DIR__ . '/footer.php' );
