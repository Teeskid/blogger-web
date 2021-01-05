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
function restore_categories(&$zip, &$queries, &$values)
{
	// @categories
	global $db;
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
	$insert = sprintf('REPLACE INTO %s (uri,title,state) VALUES %s', $db->terms, $insert);
	$insert = array_push($queries, $insert);
	$values = array_merge($values, $array);
	$insert = $array = null;
	if($arra2) {
		$inser2 = implode(',', $inser2);
		$inser2 = sprintf('REPLACE INTO %s (category,metaKey,val) VALUES %s', $db->TermMeta, $inser2);
		$inser2 = array_push($queries, $inser2);
		$values = array_merge($values, $arra2);
		$inser2 = $arra2 = null;
	}

}
function restore_posts(&$zip, &$queries, &$values)
{
	// @posts
	global $db;
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
	global $db;
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
	global $db;
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
		$inser2 = sprintf('REPLACE INTO %s (id,metaKey,val) VALUES %s', $db->pages_, $inser2);
		$inser2 = array_push($queries, $inser2);
		$values = array_merge($values, $arra2);
		$inser2 = $arra2 = null;
	}
}
function restore_media(&$zip, &$queries, &$values)
{
	global $error;
	// @media
	global $db;
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
	global $db, $_user;
	$entry = json_decode($zip->getFromName('manifest.json'));
	if($entry === null || count($entry) === 0) {
		return;
	}
	// @@ c.id -> c.inId
	$entry->categories_inId = $entry->categories_inId ?? [];
	foreach($entry->categories_inId as $x => $y) {
		$update = array_fill(0, count($y), '?');
		$update = implode(',', $update);
		$update = sprintf('UPDATE %s SET inId=(SELECT category FROM %s WHERE metaKey=? AND metaValue=?) WHERE uri IN (%s)', $db->terms, $db->TermMeta, $update);
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
		$update = sprintf('UPDATE %s SET category=(SELECT id FROM %s WHERE uri=?) WHERE uri IN (%s)', posts, $db->terms, $update);
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
		$insert = sprintf('REPLACE INTO %s (category,metaKey,val) VALUES %s', $db->TermMeta, $insert);
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
	global $db;
	try
	{
		$db->beginTransaction();
		$x = $db->prepare($x);
		$x->execute($y);
		$db->commit();
		return true;
	}
	catch(Exception $e)
	{
		$db->rollBack();
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
