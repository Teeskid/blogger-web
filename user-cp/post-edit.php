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

$action = $action ?? request( 'action', 'id', 'redirect' );
if( ! is_object($action) )
	exit;
$action->id = parseInt( $action->id );
$action->redirect = $action->redirect ?? 'post.php';
switch( $action->action ) {
	case 'modify':
		$post = $db->prepare( 'SELECT * FROM Post WHERE id=? AND subject=? LIMIT 1' );
		$post->execute( [ $action->id, 'post' ] );
		if( 0 === $post->rowCount() )
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
require( ABSPATH . BASE_UTIL . '/UIUtil.php' );

$postCategory = Term::getList( 'cat' );
if( $post->id ) {
	$postTags = $db->prepare( 'SELECT TermLink.termId as id, Term.title FROM TermLink LEFT JOIN Term ON Term.id=TermLink.termId WHERE TermLink.postId=?' );
	$postTags->execute( [ $post->id ] );
	$postTags = $postTags->fetchAll();
} else {
	$postTags = [];
}
if( $post->thumbnail ) {
	$postThumbnail = $db->prepare( 'SELECT a.title, a.mimeType, b.metaValue AS image FROM Post a LEFT JOIN PostMeta b ON b.postId=a.id AND b.metaKey=? WHERE a.id=? LIMIT 1' );
	$postThumbnail->execute( [ 'media_metadata', $post->thumbnail ] );
	$postThumbnail = $postThumbnail->fetch();
	$postThumbnail->image = json_decode($postThumbnail->image);
	$postThumbnail->image = Media::getImage( $postThumbnail->image, 'small' );
} else {
	$postThumbnail = (object) [ 'title' => 'Not Available', 'mimeType' => '', 'image' => Media::getAvatar( 'small' ) ];
}
$postThumbnail->title = htmlspecialchars($postThumbnail->title);
$postThumbnail->image = htmlentities($postThumbnail->image);

$post->title = htmlentities($post->title);
$post->excerpt = htmlspecialchars($post->excerpt);
$post->content = htmlspecialchars($post->content);
$post->password = htmlentities($post->password);
$postLocked = checked( ! empty($post->password) );
$postStatus = checked( $post->status=== 'draft' );

// $_page->setMetaItem( Page::META_CSS_FILE, 'css/post-edit.css' );
include( 'html-header.php' );
?>
<div id="mediaDialog" class="modal" tabindex="-1" role="dialog"></div>
<ol class="breadcrumb">
	<li><a href="index.php">Home</a></li>
	<li><a href="post.php">Post</a></li>
	<li class="active"><?=$action->action?></li>
</ol>
<form role="form" id="postForm" action="#">
	<input type="hidden" name="id" value="<?=$post->id?>" />
	<input type="hidden" name="jwt" value="<?=$_login->session?>" />
	<input type="hidden" name="action" value="<?=$action->action?>" />
	<div class="row">
		<div class="col-xs-12 col-sm-7">
			<div class="form-group">
				<label for="title" class="control-label">Post Title</label>
				<input type="text" class="form-control" name="title" id="title" value="<?=$post->title ?>" minlen="10" pattern="[\w\d\s]+" required />
				<p class="help-block">Titles must be only letters, numbers and spaces</p>
			</div>
			<div class="form-group">
				<label for="permalink" class="control-label">Post Permalink</label>
				<input type="text" class="form-control" name="permalink" id="permalink" value="<?=$post->permalink?>" pattern="[A-Za-z0-9-]+" required />
			</div>
			<div class="form-group">
				<label for="excerpt" class="control-label">Post Excerpt</label>
				<textarea class="form-control" name="excerpt" id="excerpt" rows="5"><?=$post->excerpt?></textarea>
			</div>
			<div class="checkbox">
				<label><input type="checkbox" id="autoExcerpt" /> Auto Generate</label>
			</div>
			<div class="form-group">
				<button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#mediaDialog" data-format="media" data-choice="mutiple" data-action="attach"><?=icon('video-camera')?> Add Media</button><br>
				<label for="content" class="control-label">Post Content</label>
				<textarea class="form-control" name="content" id="content" rows="20"><?=$post->content?></textarea>
			</div>
		</div>
		<div class="col-xs-12 col-sm-5">
			<div id="accordion" class="panel-group" role="tablist" aria-multiselectable="true">
				<div class="panel panel-info">
					<div id="headerA" class="panel-heading" role="tab">
						<h4 class="panel-title">
							<a href="#collapseA" role="button" data-toggle="collapse" data-parent="#accordion" aria-expanded="false" aria-controls="collapseA"><?=icon('folder-open')?> Category</a>
						</h4>
					</div>
					<div id="collapseA" class="collapse panel-collapse" role="tabpanel" aria-labelledby="headerA">
						<div class="panel-body">
							<div class="form-group">
								<label for="category" class="control-label">Select Category</label>
								<select class="form-control" id="category" name="category">
<?php
foreach( $postCategory as $entry ) {
	$entry->title = htmlspecialchars( strtoupper($entry->title) );
	$entry->checked = selected( $post->category === $entry->id );
?>
									<option value="<?=$entry->id?>"<?=$entry->checked?>><?=$entry->title?></option>
<?php
}
unset( $postCategory, $entry );
?>
								</select>
							</div>
							<div class="form-group">
								<label for="catText" class="control-label">Category Name</label>
								<input type="text" class="form-control" id="catText" />
							</div>
							<div class="form-group right-align">
								<a role="button" class="btn btn-primary" href="#catText" data-subject="cat">Quick Add</a>
							</div>
						</div>
					</div>
				</div>
				<div class="panel panel-default">
					<div id="headingB" class="panel-heading" role="tab">
						<h4 class="panel-title">
							<a href="#collapseB" role="button" data-toggle="collapse" data-parent="#accordion" aria-expanded="false" aria-controls="collapseB"><?=icon('image')?> Thumbnail</a>
						</h4>
					</div>
					<div id="collapseB" class="collapse panel-collapse" role="tabpanel" aria-labelledby="headingB">
						<div class="panel-body">
							<div class="media">
								<div class="media-left">
									<input id="thumbnail" type="hidden" name="thumbnail" value="<?=$post->thumbnail?>" />
									<img class="media-object" id="imageSrc" alt="..." src="<?=$postThumbnail->image?>" />
								</div>
								<div class="media-body">
									<h5 class="media-heading" id="imageName"><?=$postThumbnail->title?></h5>
									<p>
										<span><?=$postThumbnail->mimeType?></span><br>
										<button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#mediaDialog" data-format="images" data-choice="single" data-action="thumbnail"><?=icon('camera')?> Upload</button>
									</p>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="panel panel-info">
					<div id="headingC" class="panel-heading" role="tab">
						<h4 class="panel-title">
							<a href="#collapseC" role="button" data-toggle="collapse" data-parent="#accordion" aria-expanded="false" aria-controls="collapseC"><?=icon('tag')?> Tags</a>
						</h4>
					</div>
					<div id="collapseC" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingC">
						<div class="panel-body">
							<div id="postTags" class="postTags">
<?php
foreach( $postTags as $index => $entry ) {
	$entry->id = (int) $entry->id;
	$entry->title = htmlspecialchars( strtoupper($entry->title) );
?>
								<p class="checkbox"><label><input type="checkbox" name="labels[]" value="<?=$entry->id?>" checked /><span><?=$entry->title?></span></label></p>
<?php
}
unset( $index, $entry );
?>
							</div>
							<div class="form-group">
								<label for="tagText" class="control-label">Tag Text</label>
								<input id="tagText" type="text" class="form-control" />
							</div>
							<div class="form-group right-align">
								<a role="button" class="btn btn-primary" href="#tagText" data-subject="tag">Quick Add</a>
							</div>
							<p><a href="#" id="loadUsedTags">Load most used labels</a></p>
						</div>
					</div>
				</div>
			</div>
			<div class="panel panel-primary">
				<div class="panel-heading"><?=icon('check')?> Publish</div>
				<div class="panel-body">
					<div class="form-group">
						<p class="checkbox">
							<label>
								<input id="lockToggle" name="lockToggle" type="checkbox" <?=$postLocked?>/>
								<span>Lock Post</span>
							</label>
						</p>
					</div>
					<div class="form-group hide">
						<label for="password" class="control-label">Enter Password</label>
						<input type="text" class="form-control" id="password" name="password" value="<?=$post->password?>"<?=($postLocked?'':' disabled')?>/>
					</div>
					<p class="checkbox">
						<label>
							<input type="checkbox" id="status" name="status" value="draft"<?=$postStatus?>/>
							<span>Save in draft</span>
						</label>
					</p>
					<p class="right-align">
						<button type="submit" class="btn btn-primary" name="submit" value="true">Submit</button>
					</p>
				</div>
			</div>
		</div>
	</div>
</form>
<?php
$action->redirect = json_encode($action->redirect);
$_page->setMetaItem( Page::META_JS_FILE, 'js/jquery.async-form.js' );
$_page->setMetaItem( Page::META_JS_FILE, 'js/jquery.post-form.js' );
$_page->setMetaItem( Page::META_JS_CODE, <<<EOS
$(document).ready(function() {
	$("form#postForm").postForm()
		.asyncForm({ url: "../api/post-edit.php", target: $action->redirect });
		
});
EOS
);
include( 'html-footer.php' );
