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

$action = request( 'action', 'id' );
switch( $action->action ) {
	case 'modify':
		$page = $_db->prepare( 'SELECT * FROM Post WHERE id=? AND rowType=? LIMIT 1' );
		$page->execute( [ $action->id, 'page' ] );
		if( 0 === $page->rowCount() )
			redirect( BASEURI . '/404.php' );
		$page = $_db->fetchClass( $page, 'Page' );
		break;
	case 'create':
		$page = new Page();
		break;
	default:
		ob_clean();
		die();
}
if( $page->id ) {
	$pageMeta = $_db->prepare( 'SELECT metaKey, metaValue FROM PostMeta WHERE postId=?' );
	$pageMeta->execute( [ $page->id ] );
	$pageMeta = $_db->fetchMeta( $pageMeta, true );
} else {
	$pageMeta = array_fill_keys( [ Page::META_HEAD_TAG, Page::META_CSS_CODE, Page::META_JS_CODE ], null );
}
$pageMeta = array_map( 'escHtml', $pageMeta );

$page->title = escHtml( $page->title );
$page->content = escHtml( $page->content );
$page->status = escHtml( $page->status );
$page->password = escHtml( $page->password );
$pageLocked = checked( ! empty($page->password) );
$pageStatus = checked( $page->status=== 'draft' );
switch( $action->action ) {
	case 'modify':
		initHtmlPage( 'Edit Page', 'page-edit.php?action=modify&id=' . $page->id );
		break;
	case 'create':
	default:
		initHtmlPage( 'Create Page', 'page-edit.php?action=create' );
		break;
}
include_once( __DIR__ . '/header.php' );
?>
<nav aria-label="breadcrumb">
	<ol class="breadcrumb my-3">
		<li class="breadcrumb-item"><a href="index.php">Home</a></li>
		<li class="breadcrumb-item"><a href="page.php">Page</a></li>
		<li class="breadcrumb-item active" aria-current="page"><?=$action->action?></li>
	</ol>
</nav>
<form id="pageForm">
	<input type="hidden" name="id" value="<?=$page->id?>" />
	<input type="hidden" name="action" value="<?=$action->action?>" />
	<div class="row">
		<div class="col-md-7">
			<div class="mb-3">
				<label for="title" class="form-label">Page Title</label>
				<input type="text" class="form-control" name="title" id="title" value="<?=$page->title ?>" />
			</div>
			<div class="mb-3">
				<label for="content" class="form-label">Page Content</label>
				<textarea class="form-control" name="content" id="content" rows="20"><?=$page->content?></textarea>
			</div>
		</div>
		<div class="col-md-5">
			<div id="accordion" class="accordion" role="tablist" aria-multiselectable="true">
				<div class="accordion-item">
					<div id="headerA" class="card-header" role="tab">
						<h4 class="card-title">
							<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-parent="#accordion" data-bs-target="#collapseA" aria-expanded="false" aria-controls="collapseA"><?=icon('code')?> Custom Head Tags</a>
						</h4>
					</div>
					<div id="collapseA" class="accordion-collapse collapse" role="tabpanel" aria-labelledby="headerA">
						<div class="card-body">
							<div class="mb-3">
								<label for="headTag">Enter Code Below</label>
								<textarea id="headTag" rows="5" class="form-control" name="meta[<?=Page::META_HEAD_TAG?>]"><?=$pageMeta[Page::META_HEAD_TAG]?></textarea>
							</div>
						</div>
					</div>
				</div>
				<div class="accordion-item">
					<div id="headingB" class="card-header" role="tab">
						<h4 class="card-title">
							<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-parent="#accordion" data-bs-target="#collapseB" aria-expanded="false" aria-controls="collapseB"><?=icon('adjust')?> Custom CSS Style</a>
						</h4>
					</div>
					<div id="collapseB" class="accordion-collapse collapse" role="tabpanel" aria-labelledby="headingB">
						<div class="card-body">
							<div class="mb-3">
								<label for="cssCode">Enter Code Below</label>
								<textarea id="cssCode" rows="5" class="form-control" name="meta[<?=Page::META_CSS_CODE?>]"><?=$pageMeta[Page::META_CSS_CODE]?></textarea>
							</div>
						</div>
					</div>
				</div>
				<div class="card panel-info">
					<div id="headingC" class="card-header" role="tab">
						<h4 class="card-title">
							<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-parent="#accordion" data-bs-target="#collapseC" aria-expanded="false" aria-controls="collapseC"><?=icon('code')?> Custom Javascript</a>
						</h4>
					</div>
					<div id="collapseC" class="card-collapse collapse" role="tabpanel" aria-labelledby="headingC">
						<div class="card-body">
							<div class="mb-3">
								<label for="jsCode">Enter Code Below</label>
								<textarea id="jsCode" rows="5" class="form-control" name="meta[<?=Page::META_JS_CODE?>]"><?=$pageMeta[Page::META_JS_CODE]?></textarea>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="card bg-light text-dark">
				<div class="card-header"><?=icon('check')?> Publish</div>
				<div class="card-body">
					<div class="mb-3">
						<div class="form-check">
							<label><input id="pageLock" type="checkbox"<?=$pageLocked?> /> Lock Page</span></label>
						</div>
					</div>
					<div id="password_div" class="mb-3 hide">
						<label for="password" class="form-label">Enter Password</label>
						<input type="text" class="form-control" id="password" name="password" value="<?=$page->password?>" <?=($pageLocked?'':'disabled ')?>/>
					</div>
					<p class="form-check">
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
addPageJsFile( 'js/async-form.js' );
function onPageJsCode() {
$(document).ready(function(){
	var asyncForm = AsyncForm(document.getElementById("pageForm"), {url: "../api/page-edit.php", target: "page.php?tab=all"});
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
include_once( __DIR__ . '/footer.php' );
