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

$_page = new Page( 'Settings', '/user-cp/settings.php' );
$_page->setMetaItem( Page::META_CSS_FILE, 'css/compact.css' );

$config = $db->prepare( 'SELECT metaKey, metaValue FROM Config WHERE metaKey IN (?,?,?,?,?,?)' );
$config->execute( [ 'blogName', 'blogDesc', 'blogDate', 'permalink', 'blogEmail', 'searchable' ] );
$config = $config->fetchAll();
$config = new Config( $config );
$config->blogName = htmlentities($config->blogName);
$config->blogDesc = htmlspecialchars($config->blogDesc);
$config->blogEmail = htmlentities($config->blogEmail);
$config->permalink = parseInt( $config->permalink );
$config->searchable = checked( $config->searchable === 'true' );

$dateSample = [ 'F j, Y', 'Y-m-d', 'm/d/Y', 'd/m/Y' ];

include( 'html-header.php' );
?>
<ul class="breadcrumb">
	<li><a href="index.php">Home</a></li>
	<li class="active">Settings</li>
</ul>
<div class="container">
	<div class="container-sm">
		<div class="panel panel-primary">
			<div class="panel-heading">Global Settings</div>
			<ul role="tablist" class="nav nav-tabs nav-justified">
				<li role="presentation" class="active"><a href="#tools" role="tab" data-toggle="tab" aria-controls="tools">Home</a></li>
				<li role="presentation"><a href="#global" role="tab" data-toggle="tab" aria-controls="global">Global</a></li>
				<li role="presentation"><a href="#permalink" role="tab" data-toggle="tab" aria-controls="permalink">Permalink</a></li>
				<li role="presentation"><a href="#sitedate" role="tab" data-toggle="tab" aria-controls="sitedate">Date</a></li>
				<li role="presentation"><a href="#banners" role="tab" data-toggle="tab" aria-controls="banner">Banners</a></li>
				<li role="presentation"><a href="#reset" role="tab" data-toggle="tab" aria-controls="reset" class="text-danger">Reset</a></li>
			</ul>
			<div class="panel-body">
				<form role="form" id="config" name="config" action="#">
					<input type="hidden" id="action" name="action" value="tools" />
					<div class="tab-content">
						<div role="tabpanel" id="tools" class="tab-pane active fade in">
							<div class="panel-body">
								<div class="checkbox"><label><input type="checkbox" name="minify" /> Minify JS/CSS</label></div>
								<div class="checkbox"><label><input type="checkbox" name="sitemap" /> Resubmit Sitemap</label></div>
							</div>
						</div>
						<div role="tabpanel" id="global" class="tab-pane">
							<div class="form-group">
								<label for="blogName" class="control-label">Blog Title</label>
								<input type="text" class="form-control" name="blogName" id="blogName" value="<?=$config->blogName?>" />
							</div>
							<div class="form-group">
								<label for="blogEmail" class="control-label">Support Email</label>
								<input type="text" class="form-control" name="blogEmail" id="blogEmail" value="<?=$config->blogEmail?>" />
							</div>
							<div class="form-group">
								<label for="blogDesc" class="control-label">Blog Description</label>
								<textarea class="form-control" name="blogDesc" id="blogDesc" rows="4"><?=$config->blogDesc?></textarea>
							</div>
							<div class="checkbox"><label for="searchable"><input type="checkbox" id="searchable" name="searchable"<?=$config->searchable?>/> Search Engine Visibility</label></div>
						</div>
						<div role="tabpanel" id="permalink" class="tab-pane fade">
							<ul class="list-group">
<?php
foreach( Rewrite::POST_SYNTAX as $index => $entry ) {
	$entry = str_replace( '%id%', '123', $entry );
	$entry = str_replace( '%year%', '2019', $entry );
	$entry = str_replace( '%month%', '12', $entry );
	$entry = str_replace( '%day%', '01', $entry );
	$entry = str_replace( '%permalink%', 'sample-post', $entry );
	$entry = BASE_URL . $entry;
	$check = checked( $index === $cfg->permalink );
?>
								<li class="list-group-item">
									<div class="radio">
										<label>
											<input type="radio" name="permalink" value="<?=$index?>"<?=$check?> />
											<span class="text-primary"><?=$entry?></span>
										</label>
									</div>
								</li>
<?php
}
?>
							</ul>
						</div>
						<div role="tabpanel" class="tab-pane fade" id="sitedate">
							<ul class="list-group">
<?php
foreach( $dateSample as $index => $entry ) {
	$mShow = date($entry);
	$xheck = false;
	if( $config->blogDate != $entry ) {
		$check = '';
		$xheck = true;
	} else {
		$check = checked(true);
		$xheck = false;
	}
?>
								<li class="list-group-item radio">
									<label><input type="radio" name="blogDate" value="<?=$entry?>"<?=$check?> />  <?=$mShow?></label>
								</li>
<?php
}
?>
							</ul>
						</div>
						<div role="tabpanel" class="tab-pane fade" id="banners">
						</div>
						<div role="tabpanel" class="tab-pane fade" id="reset">
							<p class="alert alert-danger">Resetting your site deletes everything from this site. Data can not be recovered unless backuped, so it's recommended you run a <a href="backup.php" class="alert-link">backup</a> before reset.</p>
							<p class="checkbox"><label for="noFiles"><input type="checkbox" id="noFiles" name="noFiles" value="true" /> Leave Uploaded Files</label></p>
							<div class="form-group form-group-lg has-feedback">
								<label class="control-label" for="password">Password</label>
								<div class="input-group">
									<?=icon('lock','','input-group-addon')?>
									<input class="form-control" type="password" id="password" name="password" />
									<?=icon('tick form-control-feedback sr-only')?>
								</div>
							</div>
						</div>
					</div>
					<p><button id="submit" type="submit" class="btn btn-primary">Execute</button></p>
				</form>
			</div>
		</div>
	</div>
</div>
<?php
$_page->setMetaItem( Page::META_JS_FILE, 'js/jquery.async-form.js' );
$_page->setMetaItem( Page::META_JS_CODE, <<<'EOS'
	var elemAction, elemSubmit;
	$(document).ready(function(){
		$("a[data-toggle='tab']").on("show.bs.tab", function(e){
			var tabId = $(e.target).attr("aria-controls"), action, btnText;
			switch(tabId){
				case "reset":
				case "tools":
					action = tabId;
					btnText = "Execute";
					break;
				default:
					action = "modify";
					btnText = "Save Changes"
			}
			elemAction.val(action);
			elemSubmit.text(btnText);
		});
		elemAction = $("input#action");
		elemSubmit = $("button#submit");
		$("form#config").asyncForm({url: "../api/config.php", target: "config.php"});
	});
EOS
);
include( 'html-footer.php' );