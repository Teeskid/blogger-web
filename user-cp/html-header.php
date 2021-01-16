<?php
/**
 * HTML Header File
 *  
 * @package Sevida
 * @subpackage Administration
 */
/** Incase the page object was not instatiated */
if( ! ( isset($_page) && is_object($_page) ) ) {
	ob_clean();
	objectNotFound();
	exit;
}
if( LOGGED_IN ) {
	$fields = User::getFields( 'email', 'userName' );
	$upIcon = 'home';
	if( strpos( $_page->path, 'index.php' ) !== false )
		$upLink = '#';
	else
		$upLink = USERPATH . '/index.php';
} else {
	$upIcon = 'times';
	$upLink = BASEPATH . '/index.php';
}
/**
 * Create the right header for the page. It can be converted into a source code by
 * just adding a single get parameter "src"
 */
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
	<title><?=escHtml($cfg->blogName.' » '.$_page->title)?></title>
	<link rel="canonical" href="<?=escHtml($_page->url)?>" />
	<link rel="icon" href="<?=USERPATH.'/favicon.png'?>" type="image/png" />
	<?php
	/** Include the local files in debig mode */
	if( SE_DEBUG ) {
	?>
	<link rel="stylesheet" href="<?=USERPATH?>/css/bootstrap.min.css" />
	<?php
	} else {
	?>
	<link rel="dns-prefetch" href="//fonts.googleapis.com" />
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
	<?php
	}
	?>
	<link rel="stylesheet" href="<?=BASEPATH?>/css/fa-all.min.css" />
	<link rel="stylesheet" href="<?=BASEPATH?>/css/fa-brands.min.css" />
	<link rel="stylesheet" href="<?=BASEPATH?>/css/fa-solid.min.css" />
	<link rel="stylesheet" href="<?=USERPATH?>/css/styles.css" />
	<?php
	// Include additional css files required by the page
	doHeadCssInc();
	echo PHP_EOL;
	// Output additional css stylesheet assigned by the page
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
	/** Output additional meta tags required by the page */
	doHeadMetaTag()
	?>
</head>
<body class="bg-light pb-0">
	<header>
		<nav class="navbar navbar-expand-sm navbar-dark bg-dark">
			<div class="container-fluid">
				<a class="navbar-brand" href="<?=$upLink?>"><?php echo icon( $upIcon . ' ms-1 me-1' ), ' ', escHtml($cfg->blogName)?></a>
				<button type="button" class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#mainMenu" aria-expanded="false">
					<span class="navbar-toggler-icon"></span>
				</button>
				<div class="collapse navbar-collapse" id="mainMenu">
					<ul class="navbar-nav me-auto mb-2 mb-lg-0">
						<?php
						/** For logged in users only */
						if( LOGGED_IN ) {
						?>
						<li class="nav-item">
							<a class="nav-link" href="<?=BASEPATH?>/" target="_blank"><?=icon('external-link-alt')?>  Visit Site</a>
						</li>
						<li class="nav-item dropdown">
							<a class="nav-link dropdown-toggle" id="menuLinks" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?=icon('th-large')?> Menu</a>
							<ul class="dropdown-menu" aria-labelledby="menuLinks">
								<li><a class="dropdown-item" href="post.php"><?=icon('edit')?> Posts</a></li>
								<li><a class="dropdown-item" href="term.php?rowType=tag"><?=icon('link')?> Post Tags</a></li>
								<li><a class="dropdown-item" href="term.php?rowType=cat"><?=icon('folder')?> Categories</a></li>
								<li><a class="dropdown-item" href="page.php"><?=icon('book')?> Pages</a></li>
								<li><a class="dropdown-item" href="user.php"><?=icon('user')?> Users</a></li>
								<li><a class="dropdown-item" href="media.php"><?=icon('file')?> Uploads</a></li>
								<li><a class="dropdown-item" href="backup.php"><?=icon('hdd')?> Backup</a></li>
								<li><a class="dropdown-item" href="config.php"><?=icon('cog')?> Settings</a></li>
							</ul>
						</li>
						<li class="nav-item dropdown">
							<a class="nav-link dropdown-toggle" id="createLinks" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?=icon('plus')?> New</a>
							<ul class="dropdown-menu" aria-labelledby="createLinks">
								<li><a class="dropdown-item" href="post-new.php"><?=icon('edit')?> Post</a></li>
								<li><a class="dropdown-item" href="term.php?rowType=cat#name"><?=icon('folder')?> Category</a></li>
								<li><a class="dropdown-item" href="media-new.php"><?=icon('file')?> Upload</a></li>
								<li><a class="dropdown-item" href="page-new.php"><?=icon('book')?> Page</a></li>
								<li><a class="dropdown-item" href="user-new.php"><?=icon('user')?> User</a></li>
							</ul>
						</li>
						<li class="nav-item dropdown">
							<a class="nav-link dropdown-toggle" id="userLinks" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?=$fields->userName?></a>
							<ul class="dropdown-menu" aria-labelledby="userLinks">
								<li><a class="dropdown-item" href="profile.php?id=<?=$_login->userId?>"><?=icon('user')?> My Profile</a></li>
								<li><a class="dropdown-item" href="logout.php"><?=icon('sign-out-alt')?> Logout</a></li>
							</ul>
						</li>
						<?php
						}
						?>
					</ul>
				</div>
			</div>
		</nav>
	</header>
	<main class="container">
