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

$action = request( 'action', 'id' );
switch( $action->action ) {
	case 'modify':
		$page = $db->prepare( 'SELECT * FROM Post WHERE id=? AND subject=? LIMIT 1' );
		$page->execute( [ $action->id, 'page' ] );
		if( 0 === $page->rowCount() )
			redirect( BASEPATH . '/404.php' );
		$page = $db->fetchClass( $page, 'Page' );
		break;
	case 'create':
		$page = new Page();
		break;
	default:
		ob_clean();
		die();
}
if( $page->id ) {
	$pageMeta = $db->prepare( 'SELECT metaKey, metaValue FROM PostMeta WHERE postId=?' );
	$pageMeta->execute( [ $page->id ] );
	$pageMeta = $db->fetchMeta( $pageMeta, true );
} else {
	$pageMeta = array_fill_keys( [ Page::META_HEAD_TAG, Page::META_CSS_CODE, Page::META_JS_CODE ], null );
}
$pageMeta = array_map( 'htmlspecialchars', $pageMeta );

$page->title = htmlentities( $page->title );
$page->content = htmlspecialchars( $page->content );
$page->status = htmlentities( $page->status );
$page->password = htmlentities( $page->password );
$pageLocked = checked( ! empty($page->password) );
$pageStatus = checked( $page->status=== 'draft' );
switch( $action->action ) {
	case 'modify':
		$_page = new Page( 'Edit Page', '/user-cp/page-edit.php?action=modify&id=' . $page->id );
		break;
	case 'create':
	default:
		$_page = new Page( 'Create Page', '/user-cp/page-edit.php?action=create' );
		break;
}
include( 'html-header.php' );
?>
<ol class="breadcrumb">
	<li><a href="index.php">Home</a></li>
	<li><a href="page.php">Page</a></li>
	<li class="active"><?=$action->action?></li>
</ol>
<form role="form" id="pageForm" action="#">
	<input type="hidden" name="id" value="<?=$page->id?>" />
	<input type="hidden" name="action" value="<?=$action->action?>" />
	<div class="row">
		<div class="col-xs-12 col-sm-7">
			<div class="form-group">
				<label for="title" class="control-label">Page Title</label>
				<input type="text" class="form-control" name="title" id="title" value="<?=$page->title ?>" />
			</div>
			<div class="form-group">
				<label for="content" class="control-label">Page Content</label>
				<textarea class="form-control" name="content" id="content" rows="20"><?=$page->content?></textarea>
			</div>
		</div>
		<div class="col-xs-12 col-sm-5">
			<div id="accordion" class="panel-group" role="tablist" aria-multiselectable="true">
				<div class="panel panel-default">
					<div id="headerA" class="panel-heading" role="tab">
						<h4 class="panel-title">
							<a href="#collapseA" role="button" data-toggle="collapse" data-parent="#accordion" aria-expanded="false" aria-controls="collapseA"><?=icon('code')?> Custom Head Tags</a>
						</h4>
					</div>
					<div id="collapseA" class="collapse panel-collapse" role="tabpanel" aria-labelledby="headerA">
						<div class="panel-body">
							<div class="form-group">
								<label for="headTag">Enter Code Below</label>
								<textarea id="headTag" rows="5" class="form-control" name="meta[<?=Page::META_HEAD_TAG?>]"><?=$pageMeta[Page::META_HEAD_TAG]?></textarea>
							</div>
						</div>
					</div>
				</div>
				<div class="panel panel-default">
					<div id="headingB" class="panel-heading" role="tab">
						<h4 class="panel-title">
							<a href="#collapseB" role="button" data-toggle="collapse" data-parent="#accordion" aria-expanded="false" aria-controls="collapseB"><?=icon('adjust')?> Custom CSS Style</a>
						</h4>
					</div>
					<div id="collapseB" class="collapse panel-collapse" role="tabpanel" aria-labelledby="headingB">
						<div class="panel-body">
							<div class="form-group">
								<label for="cssCode">Enter Code Below</label>
								<textarea id="cssCode" rows="5" class="form-control" name="meta[<?=Page::META_CSS_CODE?>]"><?=$pageMeta[Page::META_CSS_CODE]?></textarea>
							</div>
						</div>
					</div>
				</div>
				<div class="panel panel-info">
					<div id="headingC" class="panel-heading" role="tab">
						<h4 class="panel-title">
							<a href="#collapseC" role="button" data-toggle="collapse" data-parent="#accordion" aria-expanded="false" aria-controls="collapseC"><?=icon('code')?> Custom Javascript</a>
						</h4>
					</div>
					<div id="collapseC" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingC">
						<div class="panel-body">
							<div class="form-group">
								<label for="jsCode">Enter Code Below</label>
								<textarea id="jsCode" rows="5" class="form-control" name="meta[<?=Page::META_JS_CODE?>]"><?=$pageMeta[Page::META_JS_CODE]?></textarea>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="panel panel-primary">
				<div class="panel-heading"><?=icon('check')?> Publish</div>
				<div class="panel-body">
					<div class="form-group">
						<div class="checkbox">
							<label><input id="pageLock" type="checkbox"<?=$pageLocked?> /> Lock Page</span></label>
						</div>
					</div>
					<div id="password_div" class="form-group hide">
						<label for="password" class="control-label">Enter Password</label>
						<input type="text" class="form-control" id="password" name="password" value="<?=$page->password?>" <?=($pageLocked?'':'disabled ')?>/>
					</div>
					<p class="checkbox">
						<label>
							<input type="checkbox" id="status" name="status" value="draft" <?=$pageStatus?>/>
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
$_page->setMetaItem( Page::META_JS_FILE, 'js/jquery.async-form.js' );
$_page->setMetaItem( Page::META_JS_CODE, <<<'EOS'
$(document).ready(function(){
	$("form#pageForm").asyncForm({url: "../api/page-edit.php", target: "page.php?tab=all"});
	$("input#pageLock").on("change", function(){
		var isLocked = this.checked,
			passWrap = $("#password_div");
			password = passWrap.find("#password"),
		password.attr("disabled", !isLocked);
		passWrap.toggleClass("hide", !isLocked);
		password.val(isLocked ? password.value : "");
	}).trigger("change");
});
EOS
);
include( 'html-footer.php' );
