<?php
/**
 * Post Editing Page
 * 
 * Used to create or edit a post
 * 
 * @package Sevida
 * @subpackage Administration
 */
/** Load the blog bootstrap file */
require( dirname(__FILE__) . '/Load.php' );
/** 
 * We check an validate an existing $action since page-new.php may have defined it already
 */
if( isset($action) && ! is_object($action) )
	exit;
$action = $action ?? request( 'action', 'id', 'redirect' );
$action->id = parseInt( $action->id );
$action->redirect = $action->redirect ?? 'post.php';
/** Find where we are goint to */
switch( $action->action ) {
	case 'modify':
		$post = $db->prepare( 'SELECT * FROM Post WHERE id=? AND rowType=? LIMIT 1' );
		$post->execute( [ $action->id, 'post' ] );
		if( $post->rowCount() === 0 )
			redirect( BASEPATH . '/404.php' );
		$post = $db->fetchClass( $post, 'Post' );
		$_page = new Page( 'Edit Post', USERPATH . '/post-edit.php?action=modify&id=' . $post->id );
		break;
	case 'create':
		$post = new Post();
		$_page = new Page( 'Create Post', USERPATH . '/post-new.php' );
		break;
	default:
		die();
}
require( ABSPATH . BASE_UTIL . '/HtmlUtil.php' );
// Get the categories for select widget
$postCategory = Term::getList( 'cat' );
/** Find the attached tags */
if( $post->id ) {
	$postLabels = $db->prepare( 'SELECT TermLink.termId as id, Term.title FROM TermLink LEFT JOIN Term ON Term.id=TermLink.termId WHERE TermLink.postId=?' );
	$postLabels->execute( [ $post->id ] );
	$postLabels = $postLabels->fetchAll();
} else {
	$postLabels = [];
}
/** Find the thumbnail */
if( $post->thumbnail ) {
	$postThumbnail = $db->prepare( 'SELECT a.title, a.mimeType, b.metaValue AS image FROM Post a LEFT JOIN PostMeta b ON b.postId=a.id AND b.metaKey=? WHERE a.id=? LIMIT 1' );
	$postThumbnail->execute( [ 'media_metadata', $post->thumbnail ] );
	$postThumbnail = $postThumbnail->fetch();
	$postThumbnail->image = json_decode($postThumbnail->image);
	$postThumbnail->image = Media::getImage( $postThumbnail->image, 'small' );
} else {
	$postThumbnail = (object) [ 'title' => 'Not Available', 'mimeType' => '', 'image' => Media::getAvatar( 'small' ) ];
}
$postThumbnail->title = escHtml($postThumbnail->title);
$postThumbnail->image = escHtml($postThumbnail->image);
/** Escape html */
$post->title = escHtml($post->title);
$post->excerpt = escHtml($post->excerpt);
$post->content = escHtml($post->content);
$post->password = escHtml($post->password);
$postLocked = checked( ! empty($post->password) );
$postStatus = checked( $post->status=== 'draft' );

include( 'html-header.php' );
?>
<div id="mediaDialog" class="modal" tabindex="-1" role="dialog"></div>
<nav aria-labelled="breadcrumb">
	<ol class="breadcrumb my-4">
		<li class="breadcrumb-item"><a href="index.php">Home</a></li>
		<li class="breadcrumb-item"><a href="post.php">Post</a></li>
		<li class="breadcrumb-item active" aria-current="page"><?=$action->action?></li>
	</ol>
</nav>
<form id="postForm">
	<input type="hidden" name="id" value="<?=$post->id?>" />
	<input type="hidden" name="jwt" value="<?=$_login->session?>" />
	<input type="hidden" name="action" value="<?=$action->action?>" />
	<div class="row">
		<div class="col-xs-12 col-sm-7">
			<div class="mb-3">
				<label for="title" class="form-label">Post Title</label>
				<input type="text" class="form-control" name="title" id="title" value="<?=$post->title ?>" minlength="10" pattern="[\w\d\s]+" required />
				<div class="form-text">Titles must be only letters, numbers and spaces</div>
			</div>
			<div class="mb-3">
				<label for="permalink" class="form-label">Post Permalink</label>
				<input type="text" class="form-control" name="permalink" id="permalink" value="<?=$post->permalink?>" pattern="[A-Za-z0-9-]+" required />
			</div>
			<div class="mb-1">
				<label for="excerpt" class="form-label">Post Excerpt</label>
				<textarea class="form-control" name="excerpt" id="excerpt" rows="5"><?=$post->excerpt?></textarea>
			</div>
			<div class="form-check">
				<input class="form-check-input" type="checkbox" id="autoExcerpt" />
				<label class="form-check-label" for="autoExcerpt">Auto Generate</label>
			</div>
			<div class="mb-3">
				<label for="content" class="form-label mb-0">Post Content</label><br>
				<button type="button" class="btn btn-primary btn-sm mb-1" data-bs-toggle="modal" data-target="#mediaDialog" data-format="media" data-choice="mutiple" data-action="attach"><?=icon('video-camera')?> Add Media</button>
				<textarea class="form-control" name="content" id="content" rows="20"><?=$post->content?></textarea>
			</div>
		</div>
		<div class="col-xs-12 col-sm-5">
			<div id="accordion" class="accordion mb-3" role="tablist" aria-multiselectable="true">
				<div class="accordion-item">
					<h4 id="headerA" class="accordion-header" role="tab">
						<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-parent="#accordion" data-bs-target="#collapseA" aria-expanded="false" aria-controls="collapseA">Post Category</button>
					</h4>
					<div id="collapseA" class="accordion-collapse collapse" role="tabpanel" aria-labelledby="headerA">
						<div class="accordion-body">
							<div class="input-group mb-3">
								<label for="category" class="input-group-text">Select</label>
								<select class="form-select" id="category" name="category">
									<?php
									foreach( $postCategory as $entry ) {
										$entry->title = escHtml( $entry->title );
										$entry->checked = selected( $post->category === $entry->id );
									?>
									<option value="<?=$entry->id?>"<?=$entry->checked?>><?=$entry->title?></option>
									<?php
									}
									unset( $postCategory, $entry );
									?>
								</select>
								<button type="button" class="btn btn-primary" aria-label="Refresh List"><?=icon('sync-alt')?></button>
							</div>
							<div class="input-group mb-3">
								<label for="catText" class="input-group-text" id="catNameLabel">Create</label>
								<input type="text" class="form-control" id="catText" placeholder="Category Title" />
								<button id="addCatBtn" type="button" class="btn btn-primary" data-target="catText" data-rowtype="cat" aria-label="Quick Add"><?=icon('plus')?></button>
							</div>
						</div>
					</div>
				</div>
				<div class="accordion-item">
					<h4 id="headingB" class="accordion-header" role="tab">
						<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-parent="#accordion" data-bs-target="#collapseB" aria-expanded="false" aria-controls="collapseB">Post Thumbnail</button>
					</h4>
					<div id="collapseB" class="accordion-collapse collapse" role="tabpanel" aria-labelledby="headingB">
						<div class="accordion-body">
							<div class="row">
								<div class="col-auto">
									<input id="thumbnail" type="hidden" name="thumbnail" value="<?=$post->thumbnail?>" />
									<img class="img-thumbnail" id="imageSrc" alt="..." src="<?=$postThumbnail->image?>" />
								</div>
								<div class="col">
									<span id="imageName" class="d-block"><?=$postThumbnail->title?></span>
									<span id="imageMime" class="d-block"><?=$postThumbnail->mimeType?></span>
									<button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-target="#mediaDialog" data-format="images" data-choice="single" data-action="thumbnail"><?=icon('camera')?> Upload</button>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="accordion-item">
					<h4 id="headingC" class="accordion-header" role="tab">
						<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-parent="#accordion" data-bs-target="#collapseC" aria-expanded="false" aria-controls="collapseC">Post Tags</button>
					</h4>
					<div id="collapseC" class="accordion-collapse collapse" role="tabpanel" aria-labelledby="headingC">
						<div class="accordion-body">
							<div id="postLabels">
								<?php
								foreach( $postLabels as $index => $entry ) {
									$entry->id = (int) $entry->id;
									$entry->id = escHtml( $entry->id );
									$entry->dom = 'pt_' . $entry->id;
									$entry->title = escHtml( $entry->title );
								?>
								<div class="form-check">
									<input class="form-check-input" id="<?=$entry->dom?>" type="checkbox" name="labels[]" value="<?=$entry->id?>" checked />
									<label class="form-check-label" for="<?=$entry->dom?>" for=""><?=$entry->title?></label>
								</div>
								<?php
								}
								unset( $index, $entry );
								?>
							</div>
							<div class="input-group mt-2 mb-2">
								<label for="labelText" class="visually-hidden" id="tagNameLabel">Tag Title</label>
								<input type="text" class="form-control" id="labelText" placeholder="Tag Title" aria-labelledby="tagNameLabel" autocomplete="off" />
								<button id="addLabel" type="button" class="btn btn-primary" data-target="labelText" data-rowtype="tag">Quick Add</button>
							</div>
							<a href="#" id="loadTags">Load most used labels</a>
						</div>
					</div>
				</div>
				<div class="accordion-item">
					<h4 id="headingD" class="accordion-header" role="tab">
						<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-parent="#accordion" data-bs-target="#collapseD" aria-expanded="true" aria-controls="collapseD">Publish Post</button>
					</h4>
					<div id="collapseD" class="accordion-collapse collapse show" role="tabpanel" aria-labelledby="headingD">
						<div class="accordion-body">
							<div class="form-check mb-1">
								<input class="form-check-input" type="checkbox" id="postLock" <?=$postLocked?>/>
								<label class="form-check-label" for="postLock">Lock Post</label>
							</div>
							<div class="mb-3<?=($postLocked?'':' d-none')?>">
								<label for="password" class="form-label">Enter Password</label>
								<input type="text" class="form-control" id="password" name="password" value="<?=$post->password?>"<?=($postLocked?'':' disabled')?> />
							</div>
							<div class="form-check">
								<input class="form-check-input" type="checkbox" id="status" name="status" value="draft"<?=$postStatus?> />
								<label class="form-check-label" for="status">Save in draft</label>
							</div>
							<button type="submit" class="btn btn-primary float-end" name="submit" value="true">Submit</button>
							<div class="clearfix"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>
<?php
$action->redirect = json_encode($action->redirect);
$_page->addPageMeta( Page::META_JS_FILE, USERPATH . '/js/async-form.js' );
$_page->addPageMeta( Page::META_JS_FILE, 'js/post-form.js' );
$_page->addPageMeta( Page::META_JS_CODE, <<<EOS
$(document).ready(function() {
	$("form#postForm").postForm()
		.asyncForm({ url: "../api/post-edit.php", target: $action->redirect });
		
});
EOS
);
include( 'html-footer.php' );
