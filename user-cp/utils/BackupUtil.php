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
require ABSCPATH . _INC_ . 'media.php';
require ABSPATH . _INC_ . 'class-upLoad.php';

define('PATH_BACKUP', ABSPATH.'/storage/backup/');

function create_backup($o = '*', &$error = []) {
	global $_db;
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
		elseif($o === 'posts') {
			$callbacks[] = 'posts';
		}
		elseif($o === 'pages') {
			$callbacks[] = 'pages';
		}
		elseif($o === 'media') {
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
			$stmt = $_db->exec("DELETE FROM $_db->meta WHERE metaKey IN ('last_backup','last_backup_date')");
			$stmt = $_db->prepare( 'INSERT INTO $_db->meta (metaKey,val) VALUES (?,?)' );
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
	global $_db, $_user;
	$backup->manifest = $backup->manifest ?? [];
	$backup->manifest['categories_inId'] = $_db->query( 'SELECT b.uri AS x, a.uri AS y FROM $_db->terms a LEFT JOIN $_db->terms b ON a.inId=b.id WHERE a.inId > 0 AND b.id > 0' )->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
	$backup->manifest['categories_id'] = $_db->query( 'SELECT c.uri AS x, b.uri AS y FROM $_db->TermMeta a LEFT JOIN $_db->terms b ON a.category=b.id LEFT JOIN media c ON a.metaValue=c.id WHERE a.prop='_media_id' AND a.val > 0 AND b.id > 0 AND c.id > 0' )->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
	$backup->manifest['posts_inId'] = $_db->query( 'SELECT b.uri AS x, a.uri AS y FROM Post a LEFT JOIN posts b ON a.inId=b.id WHERE a.inId > 0 AND b.id > 0' )->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
	$backup->manifest['posts_author'] = $_db->query( 'SELECT b.userName AS x, a.uri AS y FROM Post a LEFT JOIN users b ON a.author=b.id WHERE a.author > 0 AND b.id > 0' )->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
	$backup->manifest['posts_category'] = $_db->query( 'SELECT b.uri AS x, a.uri AS y FROM Post a LEFT JOIN $_db->terms b ON a.category=b.id WHERE a.category > 0 AND b.id > 0' )->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
	$backup->manifest['posts_id'] = $_db->query( 'SELECT c.uri AS x, b.uri AS y FROM Post_ a LEFT JOIN posts b ON a.id=b.id LEFT JOIN media c ON a.metaValue=c.id WHERE a.prop='_media_id' AND a.val > 0 AND b.id > 0 AND c.id > 0' ->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
	$backup->manifest['media_uploader'] = $_db->query( 'SELECT b.userName AS x, a.uri AS y FROM media a LEFT JOIN users b ON a.uploader=b.id WHERE a.uploader > 0 AND b.id > 0' ->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
	$backup->manifest['media_postId'] = $_db->query( 'SELECT c.uri, b.uri FROM media_ a LEFT JOIN media b ON a.id=b.id LEFT JOIN posts c ON a.metaValue=c.id WHERE a.prop='_post_id' AND a.val > 0 AND b.id > 0 AND c.id > 0' ->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
	$backup->manifest['replies_inId'] = $_db->query( 'SELECT CONCAT_WS('|', b.author, b.date), CONCAT_WS('|', a.author, a.date) FROM replies a LEFT JOIN replies b ON a.inId=b.id WHERE a.inId > 0 AND b.id > 0' )->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
	$backup->manifest['replies_postId'] = $_db->query( 'SELECT b.uri, CONCAT_WS('|', a.author, a.date) FROM replies a LEFT JOIN posts b ON a.postId=b.id WHERE a.postId > 0 AND b.id > 0' )->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
	$backup->manifest = array_filter($backup->manifest, function($e){
		return (empty($e) === false);
	});
}
/* CATEGORIES */
function backup_categories(&$backup, &$zip) {
	global $_db, $_user;
	$meta = $_db->query( 'SELECT category,metaKey,val FROM $_db->TermMeta WHERE metaKey NOT IN ('_media_id')' )->fetchAll(PDO::FETCH_GROUP);
	$backup->categories = $_db->query( 'SELECT id,title,uri,state FROM $_db->terms' )->fetchAll(PDO::FETCH_ASSOC);
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
	global $_db, $_user;
	backup_categories($backup, $zip);
	$meta = $_db->query( 'SELECT postId,metaKey,val FROM Post_ WHERE metaKey NOT IN ('_media_id')' )->fetchAll(PDO::FETCH_GROUP);
	$backup->posts = $_db->query( 'SELECT id,uri,title,excerpt,content,labels,state FROM Post ORDER BY id ASC' )->fetchAll(PDO::FETCH_ASSOC);
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
	global $_db, $_user;
	$backup->replies = $_db->query( 'SELECT content,date,MD5(CONCAT(author,date)) AS pending_id,state FROM replies ORDER BY id,inId ASC' )->fetchAll(PDO::FETCH_ASSOC);
	$meta = $_db->query( 'SELECT replyId,metaKey,val FROM replies_' )->fetchAll(PDO::FETCH_GROUP);
	$backup->replies = array_map(function(&$entry) use($meta) {
		$entry['state'] = parseInt($entry['state']);
		return $entry;
	}, $backup->replies);
	unset($meta);
}

/* PAGES */
function backup_PAGEs(&$backup, &$zip) {
	global $_db, $_user;
	$meta = $_db->query( 'SELECT id,metaKey,val FROM $_db->pages_ WHERE metaKey NOT IN ('_media_id')' )->fetchAll(PDO::FETCH_GROUP);
	$backup->pages = $_db->query( 'SELECT id,uri,title,content FROM pages ORDER BY id ASC' ->fetchAll(PDO::FETCH_ASSOC);
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
	global $_db;
	$meta = $_db->query( 'SELECT id,metaKey,val FROM media_ WHERE metaKey NOT IN ('_post_id','drawables')' ->fetchAll(PDO::FETCH_GROUP);
	$backup->media = $_db->query( 'SELECT id,filename,uploadDate FROM media ORDER BY id ASC' ->fetchAll(PDO::FETCH_ASSOC);
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
function restore_categories(&$zip, &$queries, &$values)
{
	// @categories
	global $_db;
	$entrys = json_decode($zip->getFromName('categories.json'));
	if($entrys === null || count($entrys) === 0) {
		return;
	}
	$insert = [];
	$inser2 = [];
	$array = [];
	$arra2 = [];
	foreach($entrys AS &$entry) {
		array_push($array, $entry->uri, $entry->title, $entry->state);
		array_push($insert, '(?,?,?)');
		if(isset($entry->meta) && $entry->meta) {
			foreach($entry->meta AS &$meta) {
				array_push($arra2, $entry->uri, $meta->metaKey, $meta->val);
				array_push($inser2, '(?,?,?)');
				$meta = null;
			}
		}
		$entry = null;
	}
	$insert = implode(',', $insert);
	$insert = sprintf('REPLACE INTO %s (uri,title,state) VALUES %s', $_db->terms, $insert);
	$insert = array_push($queries, $insert);
	$values = array_merge($values, $array);
	$insert = $array = null;
	if($arra2) {
		$inser2 = implode(',', $inser2);
		$inser2 = sprintf('REPLACE INTO %s (category,metaKey,val) VALUES %s', $_db->TermMeta, $inser2);
		$inser2 = array_push($queries, $inser2);
		$values = array_merge($values, $arra2);
		$inser2 = $arra2 = null;
	}

}
function restore_posts(&$zip, &$queries, &$values)
{
	// @posts
	global $_db;
	$entrys = json_decode($zip->getFromName('posts.json'));
	if($entrys === null || count($entrys) === 0) {
		return;
	}
	$insert = [];
	$inser2 = [];
	$array = [];
	$arra2 = [];
	foreach($entrys AS &$entry) {
		array_push($array, $entry->uri, $entry->title, $entry->excerpt, $entry->content, $entry->labels, $entry->state);
		array_push($insert, '(?,?,?,?,?,?)');
		if(isset($entry->meta) && $entry->meta) {
			foreach($entry->meta AS &$meta) {
				array_push($arra2, $entry->uri, $meta->metaKey, $meta->val);
				array_push($inser2, '(?,?,?)');
				$meta = null;
			}
		}
		$entry = null;
	}
	$insert = implode(',', $insert);
	$insert = sprintf('REPLACE INTO %s (uri,title,excerpt,content,labels,state) VALUES %s', posts, $insert);
	$insert = array_push($queries, $insert);
	$values = array_merge($values, $array);
	$insert = $array = null;
	if($arra2) {
		$inser2 = implode(',', $inser2);
		$inser2 = sprintf('REPLACE INTO %s (postId,metaKey,val) VALUES %s', posts_, $inser2);
		$inser2 = array_push($queries, $inser2);
		$values = array_merge($values, $arra2);
		$inser2 = $arra2 = null;
	}
}
function restore_replies(&$zip, &$queries, &$values)
{
	// @replies
	global $_db;
	$entrys = json_decode($zip->getFromName('replies.json'));
	if($entrys === null || count($entrys) === 0) {
		return;
	}
	$insert = [];
	$inser2 = [];
	$array = [];
	$arra2 = [];
	foreach($entrys AS &$entry) {
		array_push($array, $entry->content, $entry->date, $entry->state);
		array_push($insert, '(?,?,?)');
		$entry->meta = $entry->meta ?? [];
		array_push($entry->meta, (object)['metaKey' => 'pending_id', 'val' => $entry->pending_id]);
		if(isset($entry->meta) && $entry->meta) {
			foreach($entry->meta AS &$meta) {
				array_push($arra2, $entry->pending_id, $meta->metaKey, $meta->val);
				array_push($inser2, sprintf('((SELECT id FROM %s WHERE MD5(author,date)=?),?,?)', replies));
				$meta = null;
			}
		}
		$entry = null;
	}
	$insert = implode(',', $insert);
	$insert = sprintf('REPLACE INTO %s (content,date,state) VALUES %s', replies, $insert);
	$insert = array_push($queries, $insert);
	$values = array_merge($values, $array);
	$insert = $array = null;
	if($arra2) {
		$inser2 = implode(',', $inser2);
		$inser2 = sprintf('REPLACE INTO %s (replyId,metaKey,val) VALUES %s', replies_, $inser2);
		$inser2 = array_push($queries, $inser2);
		$values = array_merge($values, $arra2);
		$inser2 = $arra2 = null;
	}
}
function restore_PAGEs(&$zip, &$queries, &$values)
{
	// @pages
	global $_db;
	$entrys = json_decode($zip->getFromName('pages.json'));
	if($entrys === null || count($entrys) === 0) {
		return;
	}
	$insert = [];
	$inser2 = [];
	$array = [];
	$arra2 = [];
	foreach($entrys AS &$entry) {
		array_push($array, $entry->uri, $entry->title, $entry->content);
		array_push($insert, '(?,?,?)');
		if(isset($entry->meta) && $entry->meta) {
			foreach($entry->meta AS &$meta) {
				array_push($arra2, $entry->uri, $meta->metaKey, $meta->val);
				array_push($inser2, sprintf('(SELECT id FROM %s WHERE uri=?),?,?)', pages));
				$meta = null;
			}
		}
		$entry = null;
	}
	$insert = implode(',', $insert);
	$insert = sprintf('REPLACE INTO %s (uri,title,content) VALUES %s', pages, $insert);
	$insert = array_push($queries, $insert);
	$values = array_merge($values, $array);
	$insert = $array = null;
	if($arra2) {
		$inser2 = implode(',', $inser2);
		$inser2 = sprintf('REPLACE INTO %s (id,metaKey,val) VALUES %s', $_db->pages_, $inser2);
		$inser2 = array_push($queries, $inser2);
		$values = array_merge($values, $arra2);
		$inser2 = $arra2 = null;
	}
}
function restore_media(&$zip, &$queries, &$values)
{
	global $error;
	// @media
	global $_db;
	$entrys = json_decode($zip->getFromName('jquery.gallery.json'));
	if($entrys === null || count($entrys) === 0) {
		return;
	}
	$insert = [];
	$inser2 = [];
	$array = [];
	$arra2 = [];
	foreach($entrys AS &$entry) {
		$media = MediaUpload::fromString($entry->filename, $zip->getFromName($entry->filename));
		upload_media($media, ALLOWED_MIMES, $error);
		array_push($array, $media->uri, $entry->filename, $media->mime, $media->size, $entry->uploadDate);
		array_push($insert, '(?,?,?,?,?)');
		if(isset($entry->meta) && $entry->meta) {
			foreach($entry->meta AS &$meta) {
				array_push($arra2, $entry->uri, $meta->metaKey, $meta->val);
				array_push($inser2, sprintf('(SELECT id FROM %s WHERE uri=?),?,?)', media));
				$meta = null;
			}
		}
		$entry = null;
	}
	$insert = implode(',', $insert);
	$insert = sprintf('REPLACE INTO %s (uri,filename,mime,size,uploadDate) VALUES %s', media, $insert);
	$insert = array_push($queries, $insert);
	$values = array_merge($values, $array);
	$insert = $array = null;
	if($arra2) {
		$inser2 = implode(',', $inser2);
		$inser2 = sprintf('REPLACE INTO %s (id,metaKey,val) VALUES %s', media_, $inser2);
		$inser2 = array_push($queries, $inser2);
		$values = array_merge($values, $arra2);
		$inser2 = $arra2 = null;
	}
}
function restore_manifest(&$zip, &$queries, &$values)
{
	global $_db, $_user;
	$entry = json_decode($zip->getFromName('manifest.json'));
	if($entry === null || count($entry) === 0) {
		return;
	}
	// @@ c.id -> c.inId
	$entry->categories_inId = $entry->categories_inId ?? [];
	foreach($entry->categories_inId as $x => $y) {
		$update = array_fill(0, count($y), '?');
		$update = implode(',', $update);
		$update = sprintf('UPDATE %s SET inId=(SELECT category FROM %s WHERE metaKey=? AND metaValue=?) WHERE uri IN (%s)', $_db->terms, $_db->TermMeta, $update);
		array_push($queries, $update);
		array_push($values, $x);
		foreach($y as $z) {
			array_push($values, 'pending_uri', $z);
		}
	}
	// @@ p.id -> p.inId
	$entry->posts_inId = $entry->posts_inId ?? [];
	foreach($entry->posts_inId as $x => $y) {
		$update = array_fill(0, count($y), '?');
		$update = implode(',', $update);
		$update = sprintf('UPDATE %s SET inId=(SELECT category FROM %s WHERE pending_uri=?) WHERE uri IN (%s)', posts, posts_, $update);
		array_push($queries, $update);
		array_push($values, $x);
		foreach($y as $z) {
			array_push($values, $z);
		}
	}
	// @@ u.id -> p.author
	$entry->posts_author = $entry->posts_author ?? [];
	foreach($entry->posts_author as $x => $y) {
		$update = array_fill(0, count($y), '?');
		$update = implode(',', $update);
		$update = sprintf('UPDATE %s SET author=(SELECT id FROM %s WHERE userName=?) WHERE uri IN (%s)', posts, users, $update);
		array_push($queries, $update);
		array_push($values, $x);
		foreach($y as $z) {
			array_push($values, $z);
		}
	}
	// @@ u.id -> m.uploader
	$entry->media_uploader = $entry->media_uploader ?? [];
	foreach($entry->media_uploader as $x => $y) {
		$update = array_fill(0, count($y), '?');
		$update = implode(',', $update);
		$update = sprintf('UPDATE %s SET uploader=(SELECT id FROM %s WHERE userName=?) WHERE uri IN (%s)', media, users, $update);
		array_push($queries, $update);
		array_push($values, $x);
		foreach($y as $z) {
			array_push($values, $z);
		}
	}
	// @@ p.id -> r.postId
	$entry->posts_inId = $entry->posts_inId ?? [];
	foreach($entry->posts_inId as $x => $y) {
		$update = array_fill(0, count($y), '?');
		$update = implode(',', $update);
		$update = sprintf('UPDATE %s SET postId=(SELECT id FROM %s WHERE uri=?) WHERE MD5(CONCAT(author,date)) IN (%s)', replies, posts, $update);
		array_push($queries, $update);
		array_push($values, $x);
		foreach($y as $z) {
			array_push($values, $z);
		}
	}
	// @@ c.id -> p.category
	$entry->posts_category = $entry->posts_category ?? [];
	foreach($entry->posts_category as $x => $y) {
		$update = array_fill(0, count($y), '?');
		$update = implode(',', $update);
		$update = sprintf('UPDATE %s SET category=(SELECT id FROM %s WHERE uri=?) WHERE uri IN (%s)', posts, $_db->terms, $update);
		array_push($queries, $update);
		array_push($values, $x);
		foreach($y as $z) {
			array_push($values, $z);
		}
	}
	// @@ r.id -> r.inId
	$entry->replies_inId = $entry->replies_inId ?? [];
	foreach($entry->replies_inId as $x => $y) {
		$update = array_fill(0, count($y), '?');
		$update = implode(',', $update);
		$update = sprintf('UPDATE %s SET inId=(SELECT replyId FROM %s WHERE metaKey=? AND metaValue=?) WHERE MD5(CONCAT(author,date)) IN (%s)', replies, replies_, $update);
		array_push($queries, $update);
		array_push($values, 'pending_id', $x);
		foreach($y as $z) {
			array_push($values, $z);
		}
	}
	// @@ m.id -> c.id @[new]
	$entry->categories_id = $entry->categories_id ?? [];
	foreach($entry->categories_id as $x => $y) {
		$insert = array_fill(sprintf('(SELECT id FROM %s WHERE uri=?),?,?)', media), 0, count($y));
		$insert = implode(',', $insert);
		$insert = sprintf('REPLACE INTO %s (category,metaKey,val) VALUES %s', $_db->TermMeta, $insert);
		array_push($queries, $insert);
		foreach($y as $z) {
			array_push($values, $x, '_media_id', $z);
		}
	}
	// @@ m.id -> p.id @[new]
	$entry->posts_id = $entry->posts_id ?? [];
	foreach($entry->posts_id as $x => $y) {
		$insert = array_fill(0, count($y), sprintf('(SELECT id FROM %s WHERE uri=?),?,?)', media));
		$insert = implode(',', $insert);
		$insert = sprintf('REPLACE INTO %s (postId,metaKey,val) VALUES %s', posts_, $insert);
		array_push($queries, $insert);
		foreach($y as $z) {
			array_push($values, $x, '_media_id', $z);
		}
	}
	// @@ p.id -> m_.postId @[new]
	$entry->media_postId = $entry->media_postId ?? [];
	foreach($entry->media_postId as $x => $y) {
		$insert = array_fill(sprintf('(SELECT id FROM %s WHERE uri=?),?,?)', posts), 0, count($y));
		$insert = implode(',', $insert);
		$insert = sprintf('REPLACE INTO %s (id,metaKey,val) VALUES %s', media_, $insert);
		array_push($queries, $insert);
		foreach($y as $z) {
			array_push($values, $x, '_post_id', $z);
		}
	}
	array_push($queries,
		sprintf('UPDATE %s SET author=? WHERE author IN (?,?)', posts),
		sprintf('UPDATE %s SET uploader=? WHERE uploader IN (?,?)', media)
	);
	array_push($values,
		$_user, '0', null,
		$_user, '0', null
	);
	$update = $insert = null;
}
function reset_vars(&$x = [], &$y = [])
{
	$x = [];
	$y = [];
}
function restore_bulk_query($x, $y)
{
	if(count($x) === 0) {
		return true;
	}
	$x = implode(';', $x);
	global $_db;
	try
	{
		$_db->beginTransaction();
		$x = $_db->prepare($x);
		$x->execute($y);
		$_db->commit();
		return true;
	}
	catch(Exception $e)
	{
		$_db->rollBack();
		return false;
	}
}
function restore_callback(&$zip)
{
	$r = ['count' => [], 'error' => []];

	global $queries, $values;

	reset_vars($queries, $values);
	restore_categories($zip, $queries, $values);
	restore_bulk_query($queries, $values);

	reset_vars($queries, $values);
	restore_posts($zip, $queries, $values);
	restore_bulk_query($queries, $values);

	reset_vars($queries, $values);
	restore_replies($zip, $queries, $values);
	restore_bulk_query($queries, $values);

	reset_vars($queries, $values);
	restore_PAGEs($zip, $queries, $values);
	restore_bulk_query($queries, $values);

	reset_vars($queries, $values);
	restore_media($zip, $queries, $values);
	restore_bulk_query($queries, $values);

	reset_vars($queries, $values);
	restore_manifest($zip, $queries, $values);
	restore_bulk_query($queries, $values);

	return $r;
}
