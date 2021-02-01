<?php
/**
 * Blog Installation Page
 * 
 * @package Sevida
 * @subpackage Administration
 */
 /**
  * @var bool
  */
define( 'SE_HTML', true );
/**
 * We are installing
 * @var bool
 */
define( 'SE_INSTALL', true );

/** Load the blog bootstrap file and utilities */
require( __DIR__ . '/Load.php' );
require( ABSPATH . USER_UTIL . '/LoginUtil.php' );
require( ABSPATH . USER_UTIL . '/InstallUtil.php' );

if( isset($_cfg) && is_object($_cfg) )
	redirect( 'login.php' );

noCacheHeaders();

// Fallback blog configuration
$_cfg = new Config();
$_cfg->blogName = 'Sevida';
$_cfg->installed = '2020';

$step = (int) request('step');

$errors = [];
$causes = [];
if( isPostRequest() ) {
	$install = request( 'email', 'password', 'blogName', 'blogEmail', 'blogDesc', 'searchable' );
	/** * Validate the submitted data */
	if ( ! preg_match( User::REGEX_EMAIL, $install['email'] ) ) {
		$errors[] = 'invalid admin email address';
		$causes[] = 'email';
	}
	if( 5 > strlen($install['blogName']) ) {
		$errors[] = 'blog name too short';
		$causes[] = 'blogName';
	} elseif( ! preg_match( '#[\w\d\s\_]+#i', $install['blogName'] ) ) {
		$errors[] = 'invalid blog name';
		$causes[] = 'blogName';
	}
	if( 5 > strlen($install['password']) ) {
		$errors[] = 'password must be at least 5 characters long';
		$causes[] = 'password';
	}
	if( empty($errors) ) {
		$install['searchable'] = json_encode( $install['searchable'] === 'true' );
		$install['password'] = password_hash( $install['password'], PASSWORD_BCRYPT );
		try {
			// populate database tables
			dropAllTables();
			createDbTables();
			/** Commit configuration to databse */
			$inserts = $_db->prepare( 'INSERT INTO Config (metaKey, metaValue) VALUES (?, ?), (?, ?), (?, ?), (?, ?), (?, ?)' );
			$inserts->execute( [
				'blogName', $install['blogName'],
				'blogEmail', $install['blogEmail'],
				'blogDesc', $install['blogDesc'],
				'searchable', $install['searchable'],
				'installed', time()
			] );
			/** Add a global admin */
			$inserts = $_db->prepare( 'INSERT INTO Uzer (id,email,password,role) VALUES (?, ?, ?, ?)' );
			$inserts->execute( [ 1, $install['email'], $install['password'], User::ROLE_OWNER ] );
			/** Default category */
			$inserts = $_db->prepare( 'INSERT INTO Term (id,title,permalink,rowType,about) VALUES (?, ?, ?, ?, ?)' );
			$inserts->execute( [ 1, 'Uncategorized', 'uncategorized', Term::TYPE_CAT, 'This is a default category' ] );
			/** Prettey fine and ready to go */
			redirect( 'install.php' );
		} catch ( Exception $e ) {
			dropAllTables();
			$errors[] = $e->getMessage();
		}
	}
	$install['password'] = '';
} else {
	$install = [
		'email' => '',
		'password' => '',
		'blogName' => '',
		'blogEmail' => '',
		'blogDesc' => 'Nice entertainment blog',
		'searchable' => true,
	];
}
require( ABSPATH . BASE_UTIL . '/HtmlUtil.php' );

$install = array_map( 'escHtml', $install );
$install['searchable'] = checked( $install['searchable'] );

$errors = implode( '<br>', $errors );

initHtmlPage( 'Installation', 'install.php?step=' . $step );
addPageCssFile( 'css/compact.css' );
include_once( __DIR__ . '/header.php' );
?>
<div class="col-sm-6 offset-sm-3 col-xl-4 offset-xl-4 mb-3">
	<div id="logo"><a href="http://techify.ng/">Sevida</a></div>
	<div class="card bg-light text-dark">
		<h2 class="card-header">Installation</h2>
		<div class="card-body">
			<form id="config" method="post" action="" novalidate>
				<input type="hidden" name="step" value="1" />
				<?php
				if( ! empty($errors) )
					echo '<div class="alert alert-danger text-center">', $errors, '</div>';
				unset($errors);
				?>
				<h4>Global Admin</h4>
				<div class="mb-3">
					<label class="form-label" for="email">Admin Email</label>
					<div class="input-group input-group-lg">
						<div class="input-group-text"><?=icon('user-secret')?></div>
						<input class="form-control<?=( in_array( 'email', $causes ) ? ' is-invalid' : '' )?>" type="email" id="email" name="email" value="<?=$install['email']?>" required minlength="5" maxlength="30" />
					</div>
				</div>
				<div class="mb-3">
					<label class="form-label" for="password">Admin Password</label>
					<div class="input-group input-group-lg">
						<div class="input-group-text"><?=icon('lock')?></div>
						<input class="form-control<?=( in_array( 'password', $causes ) ? ' is-invalid' : '' )?>" type="password" id="password" name="password" value="<?=$install['password']?>" required minlength="5" maxlength="15" />
					</div>
				</div>
				<h4>Blog Information</h4>
				<div class="mb-3">
					<label class="form-label" for="blogName">Blog Name</label>
					<div class="input-group input-group-lg">
						<div class="input-group-text"><?=icon('pen')?></div>
						<input class="form-control<?=( in_array( 'blogName', $causes ) ? ' is-invalid' : '' )?>" type="text" id="blogName" name="blogName" value="<?=$install['blogName']?>" required minlength="5" maxlength="20" />
					</div>
				</div>
				<div class="mb-3">
					<label class="form-label" for="blogEmail">Blog Contact Email</label>
					<div class="input-group input-group-lg">
						<div class="input-group-text"><?=icon('at')?></div>
						<input class="form-control<?=( in_array( 'blogEmail', $causes ) ? ' is-invalid' : '' )?>" type="email" id="blogEmail" name="blogEmail" value="<?=$install['blogEmail']?>" required minlength="5" maxlength="25" />
					</div>
				</div>
				<div class="mb-3">
					<label class="form-label" for="blogDesc">Blog Description</label>
					<textarea class="form-control<?=( in_array( 'blogDesc', $causes ) ? ' is-invalid' : '' )?>" id="blogDesc" name="blogDesc" rows="3" maxlength="120"><?=$install['blogDesc']?></textarea>
					<div class="form-text">Describe your blog in 120 charachters</div>
				</div>
				<div class="mb-3 form-check">
					<input id="searchable" class="form-check-input" type="checkbox" name="searchable" value="true"<?=$install['searchable']?> />
					<label for="searchable" class="form-check-label">Allow search engines to crawl this site</label>
				</div>
				<button name="submit" type="submit" class="btn btn-primary float-end">Submit</button>
			</form>
		</div>
	</div>
</div>
<?php
include_once( __DIR__ . '/footer.php' );
