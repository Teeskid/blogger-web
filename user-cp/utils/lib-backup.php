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
require ABSCPATH._INC_.'media.php';
require ABSPATH._INC_.'class-upLoad.php';

define('PATH_BACKUP', ABSPATH.'/storage/backup/');

function create_backup($o = '*', &$error = []) {
	global $db;
	$zip = new_zip_archive(true);
	if($zip === false) {
		$error[] = sprintf('Failed to create zip file: %s', $zip->error);
		return false;
	}
	else {
		$callbacks = ['manifest'];
		if($o === '*') {
			$callbacks = array_merge($callbacks, ['posts','pages','media']);
		}
		else if($o === 'posts') {
			$callbacks[] = 'posts';
		}
		else if($o === 'pages') {
			$callbacks[] = 'pages';
		}
		else if($o === 'media') {
			$callbacks[] = 'media';
		}
		else
		{
			return false;
		}
		$zip->addFromString('index.json', json_encode($callbacks));
		$backup = new Class(){};
		foreach($callbacks as $call) {
			$call = sprintf('backup_%s', $call);
			$call($backup, $zip);
		}
		foreach($backup as $k => &$v){
			$k = sprintf('%s.json', $k);
			$v = array_filter($v, function($e){
				return (empty($e) === false);
			});
			$v = json_encode($v);
			$zip->addFromString($k, $v);
			$v = null;
		}
		$delete = glob(PATH_BACKUP.'*.BIN');
		if($zip->close() === FALSE) {
			$error[] = 'failed creating zip';
			return false;
		}
		else {
			$delete = array_map('unlink', $delete);
			$stmt = $db->exec("DELETE FROM $db->meta WHERE metaKey IN ('last_backup','last_backup_date')");
			$stmt = $db->prepare( 'INSERT INTO $db->meta (metaKey,val) VALUES (?,?)' );
			$stmt->execute(['last_backup', json_encode(['filename' => $zip->name, 'date' => formatDate(time())])]);
			return $zip->name;
		}
	}
}
function new_zip_archive() {
	// instantiate zip file
	$zip_name = sprintf('BACKUP_%s.BIN', date('Y_m_d_H_i_s'));
	$zip = new ZipArchive();
	if( $zip->open(PATH_BACKUP.$zip_name, ZipArchive::CREATE) === true ) {
		$zip->name = $zip_name;
		return $zip;
	}
	else {
		$zip->close();
		return false;
	}
}
/* MANIFEST */
function backup_manifest(&$backup, &$zip) {
	global $db, $_user;
	$backup->manifest = $backup->manifest ?? [];
	$backup->manifest['categories_inId'] = $db->query( 'SELECT b.uri AS x, a.uri AS y FROM $db->terms a LEFT JOIN $db->terms b ON a.inId=b.id WHERE a.inId > 0 AND b.id > 0' )->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
	$backup->manifest['categories_id'] = $db->query( 'SELECT c.uri AS x, b.uri AS y FROM $db->TermMeta a LEFT JOIN $db->terms b ON a.category=b.id LEFT JOIN media c ON a.metaValue=c.id WHERE a.prop='_media_id' AND a.val > 0 AND b.id > 0 AND c.id > 0' )->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
	$backup->manifest['posts_inId'] = $db->query( 'SELECT b.uri AS x, a.uri AS y FROM Post a LEFT JOIN posts b ON a.inId=b.id WHERE a.inId > 0 AND b.id > 0' )->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
	$backup->manifest['posts_author'] = $db->query( 'SELECT b.userName AS x, a.uri AS y FROM Post a LEFT JOIN users b ON a.author=b.id WHERE a.author > 0 AND b.id > 0' )->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
	$backup->manifest['posts_category'] = $db->query( 'SELECT b.uri AS x, a.uri AS y FROM Post a LEFT JOIN $db->terms b ON a.category=b.id WHERE a.category > 0 AND b.id > 0' )->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
	$backup->manifest['posts_id'] = $db->query( 'SELECT c.uri AS x, b.uri AS y FROM Post_ a LEFT JOIN posts b ON a.id=b.id LEFT JOIN media c ON a.metaValue=c.id WHERE a.prop='_media_id' AND a.val > 0 AND b.id > 0 AND c.id > 0' ->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
	$backup->manifest['media_uploader'] = $db->query( 'SELECT b.userName AS x, a.uri AS y FROM media a LEFT JOIN users b ON a.uploader=b.id WHERE a.uploader > 0 AND b.id > 0' ->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
	$backup->manifest['media_postId'] = $db->query( 'SELECT c.uri, b.uri FROM media_ a LEFT JOIN media b ON a.id=b.id LEFT JOIN posts c ON a.metaValue=c.id WHERE a.prop='_post_id' AND a.val > 0 AND b.id > 0 AND c.id > 0' ->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
	$backup->manifest['replies_inId'] = $db->query( 'SELECT CONCAT_WS('|', b.author, b.date), CONCAT_WS('|', a.author, a.date) FROM replies a LEFT JOIN replies b ON a.inId=b.id WHERE a.inId > 0 AND b.id > 0' )->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
	$backup->manifest['replies_postId'] = $db->query( 'SELECT b.uri, CONCAT_WS('|', a.author, a.date) FROM replies a LEFT JOIN posts b ON a.postId=b.id WHERE a.postId > 0 AND b.id > 0' )->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
	$backup->manifest = array_filter($backup->manifest, function($e){
		return (empty($e) === false);
	});
}
/* CATEGORIES */
function backup_categories(&$backup, &$zip) {
	global $db, $_user;
	$meta = $db->query( 'SELECT category,metaKey,val FROM $db->TermMeta WHERE metaKey NOT IN ('_media_id')' )->fetchAll(PDO::FETCH_GROUP);
	$backup->categories = $db->query( 'SELECT id,title,uri,state FROM $db->terms' )->fetchAll(PDO::FETCH_ASSOC);
	$backup->categories = array_map(function($entry) use($meta) {
		if(isset($meta[$entry['id']]))
			$entry['meta'] = $meta[$entry['id']];
		$entry['state'] = parseInt($entry['state']);
		unset($meta[$entry['id']], $entry['id']);
		return $entry;
	}, $backup->categories);
	unset($meta);
}
/* POSTS */
function backup_posts(&$backup, &$zip) {
	global $db, $_user;
	backup_categories($backup, $zip);
	$meta = $db->query( 'SELECT postId,metaKey,val FROM Post_ WHERE metaKey NOT IN ('_media_id')' )->fetchAll(PDO::FETCH_GROUP);
	$backup->posts = $db->query( 'SELECT id,uri,title,excerpt,content,labels,state FROM Post ORDER BY id ASC' )->fetchAll(PDO::FETCH_ASSOC);
	$backup->posts = array_map(function(&$entry) use($meta) {
		if(isset($meta[$entry['id']]))
			$entry['meta'] = $meta[$entry['id']];
		$entry['state'] = parseInt($entry['state']);
		unset($meta[$entry['id']], $entry['id']);
		return $entry;
	}, $backup->posts);
	unset($meta);
	backup_replies($backup, $zip);
}
/* REPLIES */
function backup_replies(&$backup, &$zip) {
	global $db, $_user;
	$backup->replies = $db->query( 'SELECT content,date,MD5(CONCAT(author,date)) AS pending_id,state FROM replies ORDER BY id,inId ASC' )->fetchAll(PDO::FETCH_ASSOC);
	$meta = $db->query( 'SELECT replyId,metaKey,val FROM replies_' )->fetchAll(PDO::FETCH_GROUP);
	$backup->replies = array_map(function(&$entry) use($meta) {
		$entry['state'] = parseInt($entry['state']);
		return $entry;
	}, $backup->replies);
	unset($meta);
}

/* PAGES */
function backup_PAGEs(&$backup, &$zip) {
	global $db, $_user;
	$meta = $db->query( 'SELECT id,metaKey,val FROM $db->pages_ WHERE metaKey NOT IN ('_media_id')' )->fetchAll(PDO::FETCH_GROUP);
	$backup->pages = $db->query( 'SELECT id,uri,title,content FROM pages ORDER BY id ASC' ->fetchAll(PDO::FETCH_ASSOC);
	$backup->pages = array_map(function(&$entry) use($meta) {
		if(isset($meta[$entry['id']]))
			$entry['meta'] = $meta[$entry['id']];
		unset($meta[$entry['id']], $entry['id']);
		return $entry;
	}, $backup->pages);
	unset($meta);
}
/* MEDIA */
function backup_media(&$backup, &$zip) {
	global $db;
	$meta = $db->query( 'SELECT id,metaKey,val FROM media_ WHERE metaKey NOT IN ('_post_id','drawables')' ->fetchAll(PDO::FETCH_GROUP);
	$backup->media = $db->query( 'SELECT id,filename,uploadDate FROM media ORDER BY id ASC' ->fetchAll(PDO::FETCH_ASSOC);
	$backup->media = array_map(function(&$entry) use($meta, $zip) {
		if(isset($meta[$entry['id']]))
			$entry['meta'] = $meta[$entry['id']];
		$f = PATH_UPLOAD.$entry['filename'];
		if(file_exists($f)) {
			$l = filesize($f);
			// add files less than 5kb
			if($l <= (1024 * 1024 * 5)) {
				$c = file_get_contents($f);
				$zip->addFromString($entry['filename'], $c);
				unset($c, $f, $l);
			}
		}
		unset($meta[$entry['id']], $entry['id']);
		return $entry;
	}, $backup->media);
	unset($meta);
}
