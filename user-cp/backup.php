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
require( ABSPATH . USER_UTIL . '/media.php' );
require( ABSPATH . BASE_UTIL . '/UIUtil.php' );

$error = [];

if( isset($_POST['submit']) )
{
	require( ABSPATH . USER_UTIL . '/lib-backup.php' );
	$opt = getPostData();
	$backup = create_backup($opt->backup, $error);
	if( isset($opt->async) ) {
		die(json_encode(['msg' => 'Success !']));
	}
	if($backup) {
		$backup = urlencode($backup);
		$backup = sprintf('backup-downLoad.php?filename=%s', $backup);
		header(sprintf('location:%s', $backup));
		exit;
	}
}
else if( isset($_POST['restore']) ) {
	require( ABSPATH . USER_UTIL . '/lib-restore.php' );
	$o = getPostData();
	$zip = new ZipArchive();
	$file = $_FILES['file']['tmp_name'];
	if(empty($file) === true) {
		$error[] = 'please select a backup file (bin format)';
	}
	else if($zip->open($file) === false || $zip->numFiles === 0) {
		$error[] = 'backup is corrupted';
	}
	else {
		$response = restore_callback($zip);
		$zip->close();
		message('success', sprintf('%s files imported successfully !', json_encode($response)));
		header('location:backup.php#restore');
		exit;
	}
}
else {

}
$error = implode('<br/>', $error);
$_page = new Page( 'Backup', '/user-cp/backup.php' );
include( 'html-header.php' );
?>
<nav>
	<div class="nav-wrapper breadcrumb-wrapper">
		<div class="col s12">
			<a href="index.php" class="breadcrumb">Home</a>
			<a href="#" class="breadcrumb active">Backup</a>
		</div>
	</div>
</nav>
<div class="card">
	<ul class="tabs card-tabs" role="group" aria-label="...">
		<li class="tab" role="presentation"><a href="#restore">IMPORT</a></li>
		<li class="tab" role="presentation"><a href="#backup">EXPORT</a></li>
	</ul>
	<div class="card-content" id="restore">
		<form class="form" role="form" action="<?=$_SERVER['REQUEST_URI']?>" method="post" enctype="multipart/form-data">
<?php
eAlert( $error, 'error' )
?>
			<div class="input-field file-field">
				<div class="btn">
					<span>Browse</span>
					<input type="file" name="file" id="file" />
				</div>
				<div class="file-path-wrapper">
					<input class="file-path" type="text" placeholder="Upload Backup File" />
				</div>
			</div>
			<div class="input-field center">
				<button type="submit" class="btn" name="restore"><?=icon('upload')?> Restore</button>
				<a href="index.php" class="btn-flat" role="button">Cancel</a>
			</div>
		</form>
	</div>
	<div class="card-content" id="backup" style="display:none">
<?php
if($lastBackup = _v('last_backup')) {
	$lastBackup = json_decode($lastBackup);
?>
<div class="card-panel">
	LAST BACKUP ON: <?=$lastBackup->date?> 
	<a href="../storage/backup/<?=rawurlencode($lastBackup->filename)?>" class="btn-small">Download</a>
</div>
<?php
}
?>
		<form class="form" role="form" action="<?=$_SERVER['REQUEST_URI']?>" method="post">
			<div class="">
				<p>Choose what to export</p>
				<p><label><input type="radio" class="filled-in" name="backup" value="*" checked /><span>All content</span></label></p>
				<p><label><input type="radio" class="filled-in" name="backup" value="posts" /><span>Posts</span></label></p>
				<p><label><input type="radio" class="filled-in" name="backup" value="pages" /><span>Pages</span></label></p>
				<p><label><input type="radio" class="filled-in" name="backup" value="media" /><span>Media Files</span></label></p>
			</div>
			<div class="input-field center">
				<button type="submit" class="btn" name="submit" value="backup"><?=icon('download')?> Generate</button>
				<a href="index.php" class="btn-flat" role="button">Cancel</a>
			</div>
		</form>
	</div>
</div>
<?php
$_page->setMetaItem( Page::META_JS_CODE, <<<'EOS'
M.Tabs.init(document.querySelector(".tabs"));
document.getElementById("select-all").onchange = function(e) {
	var se = e.target.closest("input");
	document.querySelectorAll("input[type=radio]:not(#select-all)").forEach(function(elem){
		elem.checked = se.checked;
	});
};
EOS
);
include( 'html-footer.php' );
