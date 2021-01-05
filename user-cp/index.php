<?php
/**
 * 
 * Developed By: Ahmad Tukur Jikamshi
 *
 * @facebook: amaedyteeskid
 * @twitter: amaedyteeskid
 * @instagram: amaedyteeskid
 * @whatsapp: +2348145737179
 */
require( dirname(__FILE__) . '/Load.php' );
require( ABSPATH . BASE_UTIL . '/UIUtil.php' );

$stats = $db->prepare(
	'SELECT ? AS title, ? AS pageTo, ? AS icon, COUNT(*) AS quantity FROM Post WHERE subject=? UNION ' .
	'SELECT ? AS title, ? AS pageTo, ? AS icon, COUNT(*) AS quantity FROM Post WHERE subject=? UNION ' .
	'SELECT ? AS title, ? AS pageTo, ? AS icon, COUNT(*) AS quantity FROM Post WHERE subject=?'
);
$stats->execute( [
	'Posts', 'post.php', 'books', 'post',
	'Pages', 'page.php', 'books', 'page',
	'Uploads', 'media.php', 'books', 'media'
] );
$stats = $stats->fetchAll();

$_page = new Page( 'Dashboard', USERPATH . '/index.php' );
include( 'html-header.php' );
?>
<h2>Dashboard</h2>
<div class="row">
	<div class="col-xs-12 col-sm-6">
		<div class="panel panel-info">
			<div class="panel-heading">At A Glance</div>
			<table class="table table-striped">
				<tr><th class="text-center" width="20">#</th><th>Items</th><th>Link</th></tr>
<?php
foreach( $stats AS $index => $entry ) {
?>
				<tr>
					<td><?=$entry->quantity?></td>
					<td><?=(icon($entry->icon).' '.$entry->title)?></td>
					<td><a href="<?=$entry->pageTo?>" class="btn btn-primary btn-xs">View <?=icon('caret-right')?></a></td>
				</tr>
<?php
}
?>
			</table>
			<div class="panel-body">
				<div role="group" class="list-group" aria-label="At A Glance">
				</div>
			</div>
		</div>
	</div>
	<div class="col-xs-12 col-sm-6">
		<div class="panel panel-primary">
			<div class="panel-heading">Quick Draft</div>
			<div class="panel-body">
				<form role="form" id="draft" action="#">
					<input type="hidden" name="action" value="create" />
					<input type="hidden" name="status" value="draft" />
					<div class="form-group">
						<label class="control-label" for="title">Title</label>
						<input class="form-control" type="text" id="title" name="title" minlen="10" pattern="[\w\d\s\?_-#]" required />
					</div>
					<div class="form-group">
						<label class="control-label" for="content">What's on your mind ?</label>
						<textarea class="form-control" rows="5" id="content" name="content" required></textarea>
					</div>
					<div class="form-group right-align">
						<button type="submit" class="btn btn-primary" name="submit">Save Draft</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<?php
$_page->setMetaItem( Page::META_JS_FILE, 'js/jquery.async-form.js' );
$_page->setMetaItem( Page::META_JS_CODE, <<<'EOS'
$(document).ready(function(){
	$("form#draft").asyncForm({url: "../api/post-edit.php", target: "post.php?tab=draft"});
});
EOS
);
include( 'html-footer.php' );
