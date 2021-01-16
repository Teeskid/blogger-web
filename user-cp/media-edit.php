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
require( ABSPATH . BASE_UTIL . '/HtmlUtil.php' );

$action = request( 'action', 'redirect', 'id' );
$action->redirect = $action->redirect ?? 'media.php';
switch( $action->action ) {
	case 'modify':
		$media = $db->prepare( 'SELECT * FROM Post WHERE id=? AND rowType=? LIMIT 1' );
		$media->execute( [ $action->id, 'media' ] );
		if( 0 === $media->rowCount() )
			redirect( BASEPATH . '/404.php' );
		$media = $db->fetchClass( $media, 'Post' );
		$_page = new Page( 'Edit Post', USERPATH . '/media-edit.php?action=modify&id=' . $media->id );
		break;
	case 'create':
		$media = new Media();
		$_page = new Page( 'Create Post', USERPATH . '/media-new.php' );
		break;
	default:
		die();
}
if( $media->id ) {
	$metaValue = $db->prepare( 'SELECT metaValue FROM PostMeta WHERE postId=? AND metaKey=? LIMIT 1' );
	$metaValue->execute( [ $media->id, 'media_metadata' ] );
	$metaValue = $metaValue->fetchColumn();
	$metaValue = json_decode($metaValue);
} else {
	$metaValue = (object) [ 'fileName' => '' ];
}
include( 'html-header.php' );
?>
<ol class="breadcrumb">
	<li><a href="index.php">Home</a></li>
	<li><a href="media.php">Media Library</a></li>
	<li class="active"><?=$action->action?></li>
</ol>
<div class="container-sm">
	<div class="card bg-light text-dark">
		<div class="card-header">Enter new details</div>
		<div class="card-body">
			<form id="mediaForm">
				<input type="hidden" name="id" value="<?=$media->id?>" />
				<input type="hidden" name="action" value="modify" />
				<div class="mb-3">
					<label for="title" class="form-label">Media Title</label>
					<input type="text" class="form-control" name="title" id="title" value="<?=$media->title ?>" minlength="10" pattern="[\w\d\s_]+" required />
					<div class="form-text">Titles must be only letters, numbers and spaces</div>
				</div>
				<div class="mb-3" style="margin-bottom:5px">
					<label for="permalink" class="form-label">Media Permalink</label>
					<input type="text" class="form-control" name="permalink" id="permalink" value="<?=$media->permalink?>" pattern="[A-Za-z0-9-]+" disabled />
					<div class="form-text">Small letters and hyphens only</div>
				</div>
				<div class="form-check" style="margin-top:5px">
					<label><input type="checkbox" id="autoPermalink" checked /> Auto (using the above title)</label>
				</div>
				<p class="right-align">
					<button type="submit" class="btn btn-primary" name="action" value="modify">Edit</button>
					<a class="btn btn-default" href="javascript:history.back(1)">Cancel</a>
				</p>
			</form>
		</div>
	</div>
</div>
<?php
$action->redirect = json_encode($action->redirect);
$_page->addPageMeta( Page::META_JS_FILE, USERPATH . '/js/async-form.js' );
$_page->addPageMeta( Page::META_JS_CODE, <<<EOS
$(document).ready(function() {
	var media = $("form#mediaForm");
	media.find("input#title").change(function(event){
		$.ajax({
			url: "../api/make-name.php",
			data: { text: this.value },
			success: function(response) {
				document.getElementById("permalink").value = (response.success ? response.text : "");
			}
		});
		
	});
	media.find("#autoPermalink").change(function(event){
		$("#permalink").attr("disabled", this.checked);
	});
	media.asyncForm({ url: "../api/media-edit.php", target: $action->redirect });
});
EOS
);
include( 'html-footer.php' );
