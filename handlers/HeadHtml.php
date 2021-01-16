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
if( ! ( isset($_page) && is_object($_page) ) )
	redirect( BASEPATH . '/404.php' );
if( LOGGED_IN ) {
	$fields = User::getFields( $_login->userId, 'userName' );
	$fields->permalink = Rewrite::userUri( $fields->userName );
}
$catsList = $db->prepare( 'SELECT IFNULL(master, 0) as header, master, id, title, permalink FROM Term WHERE rowType=? ORDER BY IF(id=1,555,id) DESC LIMIT 20' );
$catsList->execute( [ 'cat' ] );
$catsList = $catsList->fetchAll( PDO::FETCH_CLASS|PDO::FETCH_GROUP, 'Term' );

$_populars = $db->prepare( 'SELECT a.id, a.permalink, a.title, a.excerpt, a.posted, b.metaValue AS thumbnail FROM Post a LEFT JOIN PostMeta b ON b.postId=a.thumbnail AND b.metaKey=? WHERE a.rowType=? AND a.status=? ORDER BY a.viewCount DESC LIMIT 0,5' );
$_populars->execute( [ 'media_metadata', 'post', 'public' ] );
$_populars = $_populars->fetchAll( PDO::FETCH_CLASS, 'Post' );

$_archives = $db->prepare( 'SELECT DATE_FORMAT(posted, ?) AS archive FROM Post WHERE rowType=? GROUP BY archive' );
$_archives->execute( [ '%Y|%M|%m', 'post' ] );
$_archives = $_archives->fetchAll();

$_comments = $db->query( 'SELECT id, fullName, email, website, content, replied FROM Reply WHERE master=NULL ORDER BY replied DESC LIMIT 5' );
$_comments = $_comments->fetchAll(PDO::FETCH_GROUP);

$_postTags = $db->prepare( 'SELECT title, permalink FROM Term WHERE rowType=? ORDER BY childCount DESC LIMIT 20' );
$_postTags->execute( [ 'tag' ] );
$_postTags = $_postTags->fetchAll( PDO::FETCH_CLASS, 'Term' );

$liBuilder = function( Term &$entry ) use( $catsList ) : bool {
	$entry->id = (int) $entry->id;
	$entry->dom = 'ni_' . $entry->id;
	$entry->title = escHtml($entry->title);
	$entry->rowType = 'cat';
	$entry->permalink = Rewrite::termUri( $entry );
	if( $entry->id != 0 && isset($catsList[$entry->id]) ) {
		echo '<li class="popup"><a href="', $entry->permalink,'">', $entry->title, '</a>', icon('plus'), '<ul aria-labelledby="', $entry->dom, '">';
		array_walk( $catsList[$entry->id], $GLOBALS['liBuilder'] );
		echo '</ul></li>';
	} else {
		echo '<li><a href="', $entry->permalink, '">', $entry->title, '</a></li>';
	}
	$entry = null;
	return true;
};
$GLOBALS['liBuilder'] = $liBuilder;

$_blogName = escHtml($cfg->blogName);
$_pageName = escHtml($_page->title);

if( isset($_GET['src']) )
	header( 'Content-Type: text/plain;charset=utf-8', true );
else
	header( 'Content-Type: text/html;charset=utf-8', true );
?><!DOCTYPE html>
<html lang="en" dir="ltr" class="no-js">
<head>
	<meta charset="UTF-8" />
	<title><?=$_pageName?> - <?=$_blogName?></title>
	<link href="<?=$_page->url?>" rel="canonical" />
	<link href="<?=BASEPATH?>/favicon.png" rel="shortcut icon" type="image/png" />
	<meta content="width=device-width,user-scalable=yes,initial-scale=1.0" name="viewport">
	<meta content="no" name="msapplication-tap-highlight" />
	<link media="all" rel="stylesheet" href="<?=BASEPATH?>/css/ie-10-fix.css" />
	<link media="all" rel="stylesheet" href="<?=BASEPATH?>/css/fontawesome.min.css" />
	<link media="all" rel="stylesheet" href="<?=BASEPATH?>/css/fa-all.min.css" />
	<link media="all" rel="stylesheet" href="<?=BASEPATH?>/css/fa-brands.min.css" />
	<link media="all" rel="stylesheet" href="<?=BASEPATH?>/css/fa-solid.min.css" />
	<link media="all" rel="stylesheet" href="<?=BASEPATH?>/css/animate.css" />
	<link media="all" rel="stylesheet" href="<?=BASEPATH?>/css/owl.carousel.min.css" />
	<link media="all" rel="stylesheet" href="<?=BASEPATH?>/css/ripple.min.css" />
<?php
$style = $_page->getMetaItem( Page::META_CSS_LOAD );
array_push( $style, 'feeds', 'styles' );
$style = array_reverse($style);
$style = implode( ',', $style );
if( ! SE_DEBUG ) {
	$style = md5($style) . '.css';
?>
	<link rel="stylesheet" media="all" href="<?=BASEPATH?>/css/styles.min.css" />
	<link rel="dns-prefetch" href="//fonts.googleapis.com" />
	<link rel="preconnect" href="//fonts.gstatic.com" crossorigin />
	<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![}]-->
	<script>
	(function(html){
		html.className = html.className.replace(/\bno-js\b/,"js");
	})(document.documentElement);
	const BASE_URL = <?=@json_encode(BASE_URL)?>;
	const BASEPATH = <?=@json_encode(BASEPATH)?>;
	</script>
<?php
} else {
	$style = urlencode($style);
	echo '<link media="all" rel="stylesheet" href="', BASEPATH, '/css/index.php?load=', $style, '" />';
}
doHeadCssInc();
doHeadCssTag();
doHeadMetaTag();
?>
</head>
<body>
<div id="container">
<header id="header">
	<nav>
<?php
if( LOGGED_IN ) {
?>
		<div class="navbar navbar-sm cp">
			<ul class="nav nav-left">
				<li><a href="<?=USERPATH?>" target="_blank"><?=icon('dashboard')?> Dashboard</a></li>
				<li class="popup">
					<a id="userBar" href="#" role="button" aria-haspopup="true" aria-expanded="false">New</a>
					<ul aria-labelledby="userBar">
						<li><a href="<?=USERPATH?>/post-new.php">Post</a></li>
						<li><a href="<?=USERPATH?>/media-upLoad.php">Media</a></li>
						<li><a href="<?=USERPATH?>/page-new.php">Page</a></li>
						<li><a href="<?=USERPATH?>/user-create.php">User</a></li>
					</ul>
				</li>
			</ul>
			<ul class="nav nav-right">
				<li class="popup">
					<a id="userPro" href="#" role="button" aria-haspopup="true" aria-expanded="false"><?=$fields->userName?></a>
					<ul aria-labelledby="userPro">
						<li><a href="<?=$fields->permalink?>"><?=icon('user')?>  My Profile</a></li>
						<li><a href="<?=USERPATH?>/logout.php?redirect=<?=rawurlencode($_page->path)?>"><?=icon('sign-out')?> Logout</a></li>
					</ul>
				</li>
			</ul>
		</div>
<?php
}
?> 
		<div class="navbar navbar-sm">
			<a id="topNavBtn" role="button" href="#" class="nav-btn toggle" data-target="topNav" aria-haspopup="true" aria-expanded="false"><?=icon('bars')?></a>
			<span class="nav-txt"><?=date('M d, Y h:i A')?></span>
			<ul class="nav nav-left nav-popup" id="topNav">
				<li><a href="<?=BASEPATH?>/about-us/">About Us</a></li>
				<li><a href="<?=BASEPATH?>/contact-us/">Contact Us</a></li>
				<li><a href="<?=BASEPATH?>/privacy-policy/">Privacy Policy</a></li>
<?php
if( ! LOGGED_IN )
	echo '<li><a href="', USERPATH, '/login.php" rel="noindex,nofollow" target="_blank">Sign In</a></li>';
?>
			</ul>
			<ul class="nav nav-right">
				<li class="iconic"><a href="https://fb.me/amaedyteeskid"><?=icon('facebook','b')?></a></li>
				<li class="iconic"><a href="https://twitter.com/amaedyteeskid"><?=icon('twitter','b')?></a></li>
				<li class="iconic"><a href="https://github.com/amaedyteeskid"><?=icon('github','b')?></a></li>
				<li class="iconic"><a href="wtai://+2348145737179"><?=icon('whatsapp','b')?></a></li>
			</ul>
		</div>
		<div class="brand">
			<h1><a href="<?=BASEPATH?>/"><img alt="<?=$_blogName?> Logo" src="<?=BASEPATH?>/images/logo.png" /></a></h1>
		</div>
		<div class="navbar navbar-lg" id="nav-bar">
			<a id="mainNavBtn" role="button" href="#" class="nav-btn toggle" data-target="mainNav" aria-haspopup="true" aria-expanded="false"><?=icon('bars')?></a>
			<a id="homeButton" href="<?=BASEPATH?>/" class="nav-btn"><?=icon('home')?></a>
			<ul class="nav nav-left nav-popup" id="mainNav" tabindex="0" aria-labelledby="mainNavBtn">
<?php
if( isset($catsList[0]) )
	array_walk( $catsList[0], $liBuilder );
else
	echo '<li>No Items</li>';
unset( $catsList, $liBuilder );
?>
				<li id="moreLi" class="popup" style="display:none">
					<a id="mn_m" href="#" aria-haspopup="true" aria-expanded="false">More</a>
					<?=icon('plus')?>
					<ul id="moreUl" aria-labelledby="mn_m"></ul>
				</li>
			</ul>
			<form id="searchForm" class="nav nav-right nav-form">
				<input type="search" name="s" value="Search Here" />
				<button type="submit" name="submit" value="true"><?=icon('search')?></button>
			</form>
		</div>
	</nav>
</header>
<main id="main">
	<div class="row">
		<div class="col-main">
			<div class="advert advert-top">
<?php
if( LOGGED_IN || SE_DEBUG ) echo '---'; else {
?>
					<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
					<!-- TOP AD -->
					<ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-6077742528829558" data-ad-slot="9981233430" data-ad-format="auto"></ins>
					<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
<?php
}
?>
				</div>
