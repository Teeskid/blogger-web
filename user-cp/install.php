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
define( 'SE_INSTALL', true );

require( dirname(__DIR__) . '/Load.php' );
require( ABSPATH . BASE_UTIL . '/UIUtil.php' );

if( isset($cfg) && is_object($cfg) )
	redirect( 'login.php' );

noCacheHeaders();

$cfg = new Config();
$cfg->blogName = 'Sevida';
$cfg->installed = '2020';

$step = request('step') ?? 1;

$errors = [];

if( isPostRequest() ) {
	$install = request( 'blogName', 'email', 'userName', 'password' );

	if( strlen( $install->blogName ) < 3 )
		$errors[] = 'blogName too short';
	if( strlen($install->blogName) > 15 )
		$errors[] = 'blogName too long';
	if( preg_match('/[^\w\_\-\s]/', $install->blogName) )
		$errors[] = 'invalid website blogName';
	if( strlen($install->email) < 8 || ! testEmail($install->email) )
		$errors[] = 'invalid admin email';
	if( strlen($install->userName) < 5 || preg_match('/[^\w\@\.\_\s]/i', $install->userName) )
		$errors[] = 'invalid admin userName';
	if( strlen($install->password) < 5 )
		$errors[] = 'invalid admin password';
	if( strlen($install->password) > 15 )
		$errors[] = 'admin password too long';

	if( isset($errors[0]) ) {
		$errors = implode( '<br>', $errors );
		showError( 'Form Error', $errors );
	}

	require( ABSPATH . USER_UTIL . '/LoginUtil.php' );
	require( ABSPATH . USER_UTIL . '/InstallUtil.php' );

	$install->userName = makePermalink( $install->userName );
	$install->password = encryptPassword( $install->password );
	$install->email = strtolower( $install->email );
	$install->searchable = json_encode( ! isset($install->searchable) );

	try {
		dropTables();
		createTables();

		$insert = $db->prepare( 'INSERT INTO Config (metaKey, metaValue) VALUES (?,?),(?,?),(?,?),(?,?),(?,?),(?,?)' );
		$insert = $insert->execute( [
			'blogName', $install->blogName,
			'blogEmail', $install->email,
			'blogDate', 'Y m d H:i:s A',
			'permalink', 0,
			'installed', time(),
			'searchable', $install->searchable
		] );
		$insert = $db->prepare( 'INSERT INTO Term (id,title,permalink,subject) VALUES (1,?,?,?)' );
		$insert = $insert->execute( [ 'Uncategorized', 'uncategorized', 'cat' ] );

		$insert = $db->prepare( 'INSERT INTO Person (id,userName,email,password,role) VALUES (1,?,?,?,?)' );
		$insert = $insert->execute( [ $install->userName, $install->email, $install->password, 'owner' ] );

		$step = 2;
	} catch( Exception $e ) {
		dropTables();
		$errors[] = $e->getMessage();
	}
	if( count($errors) != 0 ) {
		$errors = implode( '<br>', $errors );
		showError( $errors );
	}
	$install->password = '';

} else {
	$install = array_fill_keys( [ 'blogName', 'userName', 'password', 'email' ], null );	
	$install = (object) array_map( 'htmlentities', $install );
}
$_page = new Page( 'Installation', '/user-cp/install.php?step=' . $step );
$_page->setMetaItem( Page::META_CSS_FILE, 'css/compact.css' );
include( 'html-header.php' );
?>
<div class="container">
	<div id="logo"><a href="http://techify.ng/">Sevida</a></div>
	<div class="container-sm">
<?php
switch( $step ) {
	case 1:
?>
		<div class="panel panel-primary">
			<div class="panel-heading"><?=icon('cog')?> Information needed</div>
			<div class="panel-body">
				<form role="form" class="form" method="post" action="#">
					<input type="hidden" name="action" value="3" />
<?php
	if( isset($errors[0]) ) {
			$errors = implode( '<br>', $errors );
			echo '<div class="alert alert-danger text-center">', $errors, '</div>';
		} else {
			echo '<p class="alert alert-info">Please provide the following information. Don&#8217;t worry, you can always change these settings later.</p>';
		}
		unset( $errors );
?>
					<div class="form-group form-group-lg">
						<label class="control-label" for="blogName">Site Title</label>
						<div class="input-group">
							<?=icon('globe','','input-group-addon')?>
							<input class="form-control" type="text" id="blogName" name="blogName" minlength="5" maxlength="15" value="<?=$install->blogName?>" required />
						</div>
					</div>
					<div class="form-group form-group-lg">
						<label class="control-label" for="userName">Username</label>
						<div class="input-group">
							<?=icon('user','','input-group-addon')?>
							<input class="form-control" type="text" id="userName" minlength="5" maxlength="15" name="userName" value="<?=$install->userName?>" required />
						</div>
						<p class="help-block">Usernames can have only alphanumeric characters, spaces, underscores, hyphens, periods, and the @ symbol.</p>
					</div>
					<div class="form-group form-group-lg">
						<label class="control-label" for="email">Email</label>
						<div class="input-group">
							<?=icon('at','','input-group-addon')?>
							<input class="form-control" type="email" id="email" minlength="10" maxlength="30" name="email" value="<?=$install->email?>" required />
						</div>
						<p class="help-block">Double-check your email address before continuing.</p>
					</div>
					<div class="form-group form-group-lg">
						<label class="control-label" for="password">Password</label>
						<div class="input-group">
							<?=icon('lock','','input-group-addon')?>
							<input class="form-control" type="password" id="password" minlength="8" maxlength="20" name="password" value="<?=$install->password?>" required />
						</div>
						<p class="help-block">You will need this password to log&nbsp;in. Please store it in a secure location.</p>
					</div>
					<div class="checkbox">
						<label for="searchable"><input type="checkbox" id="searchable" name="searchable" /> Allow Search Engines to crawl my site</label>
						<p class="help-block">It is up to search engines to honor this request.</p>
					</div>
					<input type="hidden" name="language" value="en_US" />
					<button type="submit" name="submit" id="submit" class="btn btn-primary">Go Install</button>
				</form>
			</div>
		</div>
<?php
	break;
	case 2:
?>
		<div class="panel panel-success">
			<div class="panel-heading">Installation Success</div>
			<div class="panel-body">
				<div class="form-group form-group-lg">
					<label class="control-label">Username</label>
					<p class="form-control-static"><?=$install->userName?></p>
				</div><div class="form-group form-group-lg">
					<label>Password</label>
					<p class="form-control-static">Your choosen password.</p>
				</div>
				<p><a href="login.php" class="btn btn-success">Log In</a></p>
			</div>
		</div>
<?php
	break;
}
?>
	</div>
</div>
<?php
include( 'html-footer.php' );
