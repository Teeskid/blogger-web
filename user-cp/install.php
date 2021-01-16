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
require( dirname(__DIR__) . '/Load.php' );
require( ABSPATH . USER_UTIL . '/InstallUtil.php' );

if( get_class($cfg) === 'Config' )
	redirect( USERPATH . '/login.php' );

noCacheHeaders();

/**
 * @var \Config
 */
$cfg = new Config( [
	'blogName' => 'Sevida',
	'installed' => '2020'
] );
/**
 * @var int
 */
$step = (int) request('step');

$errors = [];
$causes = [];
if( isPostRequest() ) {
	/**
	 * @var object
	 */
	$install = request( 'userName', 'password', 'blogName', 'blogEmail', 'blogDesc', 'searchable' );
	/**
	 * Validate the submitted data
	 */
	if( preg_match( REGEX_VALID_NAME, $install->userName ) ) {
		$install->email = null;
	} elseif ( preg_match( REGEX_VALID_EMAIL, $install->userName ) ) {
		$install->email = $install->userName;
		$install->userName = null;
	} else {
		$causes[] = 'userName';
		$errors[] = 'username must be 5 characters and more, no special characters';
	}
	if( 5 > strlen($install->blogName) ) {
		$causes[] = 'blogName';
		$errors[] = 'blog name too short';
	} elseif( ! preg_match( REGEX_VALID_NAME, $install->blogName ) ) {
		$causes[] = 'blogName';
		$errors[] = 'invalid blog name';
	}
	$install->searchable = json_encode( $install->searchable === 'true' );
	if( empty($errors) ) {
		try {
			// populate database tables
			createTables();
			/** commit data to database */
			$install->role = 'owner';
			$install->password = password_hash( $install->password, PASSWORD_BCRYPT );
			$install = get_object_vars($install);
			$inserts = $db->prepare(
				'REPLACE INTO person SET userName=:userName,email=:email,password=:password,role=:role,id=0; ' .
				"REPLACE INTO config (metaKey, metaValue) VALUES ('blogName',:blogName),('blogEmail',:blogEmail),('blogDesc',:blogDesc),('searchable',:searchable)"
			);
			$inserts->execute( $install );
			// all is well, head to installation
			redirect( 'install.php' );
		} catch ( Exception $e ) {
			dropAllTables();
			$errors[] = $e->getMessage();
		}
	}
	$install['password'] = '';
	if( empty($install['userName']) )
		$install['userName'] = $install['email'];
} else {
	$install = [
		'userName' => 'admin',
		'password' => 'admin',
		'blogName' => 'MyBlog',
		'blogEmail' => '',
		'blogDesc' => 'Nice entertainment blog',
		'searchable' => 'true',
	];
}
require( ABSPATH . BASE_UTIL . '/HtmlUtil.php' );

$install = (object) array_map( 'escHtml', $install );
$install->searchable = checked( $install->searchable === 'true' );

$errors = implode( '<br>', $errors );

$_page = new Page( 'Installation', USERPATH . '/install.php?step=' . $step );
$_page->addPageMeta( Page::META_CSS_FILE, 'css/compact.css' );
include( 'html-header.php' );
?>
<div class="col-sm-6 offset-sm-3 col-xl-4 offset-xl-4 mb-3">
	<div id="logo"><a href="http://techify.ng/">Sevida</a></div>
	<div class="card bg-light text-dark">
		<?php
		switch( $step ) {
			case 2:
		?>
		<h3 class="card-header">Success</h3>
		
		<?php
				break;
			default:
		?>
		<h3 class="card-header">Installation</h3>
		<div class="card-body">
			<form id="config" method="post" action="#" class="needs-validation" novalidate>
				<input type="hidden" name="step" value="1" />
				<?php
				if( ! empty($errors) )
					echo '<div class="alert alert-danger text-center">', $errors, '</div>';
				unset($errors);
				?>
				<h4>Global Admin</h4>
				<div class="mb-3">
					<label class="form-label" for="userName">Username or Email</label>
					<div class="input-group input-group-lg">
						<div class="input-group-text"><?=icon('user-secret')?></div>
						<input class="form-control<?=( in_array( 'userName', $causes ) ? ' is-invalid' : '' )?>" type="text" id="userName" name="userName" value="<?=$install->userName?>" required minlength="5" maxlength="15" />
					</div>
				</div>
				<div class="mb-3">
					<label class="form-label" for="password">Password</label>
					<div class="input-group input-group-lg">
						<div class="input-group-text"><?=icon('lock')?></div>
						<input class="form-control<?=( in_array( 'password', $causes ) ? ' is-invalid' : '' )?>" type="password" id="password" name="password" value="<?=$install->password?>" required minlength="8" maxlength="15" />
					</div>
				</div>
				<h4>Blog Information</h4>
				<div class="mb-3">
					<label class="form-label" for="blogName">Blog Name</label>
					<div class="input-group input-group-lg">
						<div class="input-group-text"><?=icon('pen')?></div>
						<input class="form-control<?=( in_array( 'blogName', $causes ) ? ' is-invalid' : '' )?>" type="text" id="blogName" name="blogName" value="<?=$install->blogName?>" required minlength="5" maxlength="15" />
					</div>
				</div>
				<div class="mb-3">
					<label class="form-label" for="blogEmail">Blog Contact Email</label>
					<div class="input-group input-group-lg">
						<div class="input-group-text"><?=icon('at')?></div>
						<input class="form-control<?=( in_array( 'blogEmail', $causes ) ? ' is-invalid' : '' )?>" type="email" id="blogEmail" name="blogEmail" value="<?=$install->blogEmail?>" required minlength="5" maxlength="25" />
					</div>
				</div>
				<div class="mb-3">
					<label class="form-label" for="blogDesc">Blog Description</label>
					<textarea class="form-control<?=( in_array( 'blogDesc', $causes ) ? ' is-invalid' : '' )?>" id="blogDesc" name="blogDesc" rows="3" maxlength="120"><?=$install->blogDesc?></textarea>
					<div class="form-text">Describe your blog in 120 charachters</div>
				</div>
				<div class="mb-3 form-check">
					<input id="searchable" class="form-check-input" type="checkbox" name="searchable" value="true"<?=$install->searchable?> />
					<label for="searchable" class="form-check-label">Allow search engines to crawl this site</label>
				</div>
				<button name="submit" type="submit" class="btn btn-primary float-end">Submit</button>
			</form>
		</div>
		<?php
			break;
		}
		?>
	</div>
</div>
<?php
include( 'html-footer.php' );
