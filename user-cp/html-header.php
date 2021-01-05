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
	die();
if( LOGGED_IN )
	$fields = User::getFields( 'email', 'userName' );
if( strpos( $_page->path, 'index.php' ) !== false ) {
	$upIcon = icon('home');
	$upLink = '#';
} else {
	$upIcon = icon('arrow-left');
	$upLink = 'javascript:history.back(1)';
}
if( isset($_GET['src']) )
	header( 'Content-Type: text/plain;charset=utf-8', true );
else {
	@header( 'Content-Type: text/html;charset=utf-8', true );
	@header( 'X-Robots-Tag: noindex' );
}
?><!DOCTYPE html>
<html lang="en" class="no-js" dir="ltr">
<head>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="robots" content="noindex,nofollow" />
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title><?=htmlspecialchars($cfg->blogName.' » '.$_page->title)?></title>
	<link rel="canonical" href="<?=htmlentities($_page->url)?>" />
	<link rel="icon" href="<?=USERPATH.'/favicon.png'?>" type="image/png" />
<?php
if( ! SE_DEBUG ) {
?>
	<link rel="dns-prefetch" href="//fonts.googleapis.com" />
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<?php
}
?>
	<link rel="stylesheet" href="<?=BASEPATH?>/css/fa-all.min.css" />
	<link rel="stylesheet" href="<?=BASEPATH?>/css/fa-brands.min.css" />
	<link rel="stylesheet" href="<?=BASEPATH?>/css/fa-solid.min.css" />
	<link rel="stylesheet" href="<?=USERPATH?>/css/bootstrap.min.css" />
	<link rel="stylesheet" href="<?=USERPATH?>/css/bootstrap-theme.min.css" />
	<link rel="stylesheet" href="<?=USERPATH?>/css/styles.css" />
<?php
doHeadCssInc();
doHeadCssTag();
?>
	<script>
	(function(html){
		html.className = html.className.replace(/\bno-js\b/,"js");
	})(document.documentElement);
	const BASE_URL = <?=json_encode(BASE_URL)?>;
	const BASEPATH = <?=json_encode(BASEPATH)?>;
	</script>
	<!--[if lt IE 9]>
	<script src="../js/html5.js?ver=3.7.3" type="text/javascript"></script>
<?php
if( ! SE_DEBUG ) {
?>
	<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<?php
}
?>
	<![}]-->
<?php
doHeadMetaTag();
?>
</head>
<body>
	<header>
		<nav class="navbar navbar-default bg-primary">
			<div class="container-fluid">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#mainMenu" aria-expanded="false">
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a class="navbar-brand" href="<?=$upLink?>"><?php echo $upIcon, ' ', htmlspecialchars($cfg->blogName)?></a>
				</div>
				<div class="collapse navbar-collapse" id="mainMenu">
					<ul class="nav navbar-nav navbar-left">
						<li><a href="<?=BASEPATH?>/" target="_blank"><?=icon('external-link-alt')?>  Visit Site</a></li>
					</ul>
<?php
if( LOGGED_IN ) {
?>
					<ul class="nav navbar-nav navbar-right">
						<li class="dropdown">
							<a href="#" role="button" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?=icon('th-large')?> Menu</a>
							<ul class="dropdown-menu">
								<li><a href="post.php"><?=icon('edit')?> Posts</a></li>
								<li><a href="term.php?subject=tag"><?=icon('link')?> Post Tags</a></li>
								<li><a href="term.php?subject=cat"><?=icon('folder')?> Categories</a></li>
								<li><a href="page.php"><?=icon('book')?> Pages</a></li>
								<li><a href="user.php"><?=icon('user')?> Users</a></li>
								<li><a href="media.php"><?=icon('file')?> Uploads</a></li>
								<li><a href="backup.php"><?=icon('hdd')?> Backup</a></li>
								<li><a href="settings.php"><?=icon('cog')?> Settings</a></li>
							</ul>
						</li>
						<li class="dropdown">
							<a href="#" role="button" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?=icon('plus')?> New</a>
							<ul class="dropdown-menu">
								<li><a href="post-new.php"><?=icon('edit')?> Post</a></li>
								<li><a href="term.php?subject=cat#name"><?=icon('folder')?> Category</a></li>
								<li><a href="media-new.php"><?=icon('file')?> Upload</a></li>
								<li><a href="page-new.php"><?=icon('book')?> Page</a></li>
								<li><a href="user-new.php"><?=icon('user')?> User</a></li>
							</ul>
						</li>
						<li class="dropdown">
							<a href="#" role="button" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?=icon('caret-down')?> <?=$fields->userName?></a>
							<ul class="dropdown-menu">
								<li><a href="profile.php?id=<?=$_login->userId?>"><?=icon('user')?> My Profile</a></li>
								<li><a href="logout.php"><?=icon('sign-out-alt')?> Logout</a></li>
							</ul>
						</li>
					</ul>
<?php
}
?>
				</div>
			</div>
		</nav>
	</header>
	<div class="container">
		<main>
<?php
if( isset($_SESSION['__MESSAGE__']) && ! empty($_SESSION['__MESSAGE__']) ) {
	$MESSAGE = jsonUnserialize( $_SESSION['__MESSAGE__'] );
?>
			<p class="text-center alert alert-<?=$MESSAGE['context']?>"><?=$MESSAGE['message']?></p>
<?php
unset( $MESSAGE, $_SESSION['__MESSAGE__'] );
}