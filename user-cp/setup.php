<?php
/**
 * Setup Configuration Page
 * 
 * Handles generation of the following files
 * 
 * - Manifest.php file which contains the database setup parameters, unique authentication keys and
 * 	 the base blog url and path
 * - .htacess for apache url rewriting
 * - .lighttpd for lighttpd rewrite rules, if using it as the server
 * - robots.txt for blocking search engines to index what we do not want it to
 * 
 * Note: There is no input for the page url in the form, it is assumed the current
 * url your are visiting this page with. Whether http or (s) matters.
 * 
 * @package Sevida
 * @subpackage Administration
 */
 /**
  * @var bool
  */
define( 'SE_HTML', true );
/**
 * @var bool
 */
define( 'SE_NO_DB', true );
/**
 * Tells that we are browsing the configuration, lest we walk in circle
 * @var bool
 */
define( 'SE_SETUP', true );

/** Load the blog bootstrap file and utilities */
require( dirname(__DIR__) . '/Load.php' );
require( ABSPATH . USER_UTIL . '/SetupUtil.php' );

// A setup already exists, try installing
if ( SE_KONFIG )
	redirect( 'install.php' );

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
	$config = request( 'dbHost', 'dbUser', 'password', 'dbName', 'charset' );
	/**
	 * Validate the submitted data
	 */
	if( 3 > strlen($config->dbHost) ) {
		$causes[] = 'dbHost';
		$errors[] = 'invalid database host';
	}
	if( ! preg_match( REGEX_VALID_NAME, $config->dbUser ) ) {
		$causes[] = 'dbUser';
		$errors[] = 'invalid database user';
	}
	if( ! preg_match( REGEX_VALID_NAME, $config->dbName ) ) {
		$causes[] = 'dbName';
		$errors[] = 'Invalid database name';
	}
	if( empty($errors) ) {
		try {
			/** Fork a trial connection */
			$db = new Database( $config->dbHost, $config->dbName, $config->charset, $config->dbUser, (string) $config->password );
			if( ! $db->dbConnect() ) {
				$causes[] = 'dbHost';
				$causes[] = 'dbUser';
				$causes[] = 'dbName';
				$causes[] = 'password';
				throw new Exception( 'Server not running or details detailes provided invalid' );
			}
			// Lets generate robots.txt file in the root directory of our blog
			generateRobotsFile();

			// Generate .htaccess file in the root
			generateHtaccessFile();

			// Generate .lighttpd file
			generateLighttpdFile();

			// Generate Manifest.php file
			generateManifestFile( $config );
			
			// all is well, head to installation
			redirect( 'install.php' );
		} catch ( Exception $e ) {
			$errors[] = $e->getMessage();
			unlinkUserFiles();
		}
	}
	$config = (array) $config;
} else {
	// Lets fallback the form data
	$config = [
		'dbHost' => 'localhost',
		'dbUser' => 'root',
		'dbName' => 'sevida',
		'password' => ''
	];
}
$config = (object) array_map( 'escHtml', $config );
$errors = implode( '<br>', $errors );

$_page = new Page( 'Setup Configuration', USERPATH . '/setup.php?step=' . $step );
$_page->addPageMeta( Page::META_CSS_FILE, 'css/compact.css' );
require( ABSPATH . BASE_UTIL . '/HtmlUtil.php' );
include( 'html-header.php' );
?>
<div class="col-sm-6 offset-sm-3 col-xl-4 offset-xl-4 mb-3">
	<div id="logo"><a href="http://techify.ng/">Sevida</a></div>
	<div class="card bg-light text-dark">
		<?php
		switch( $step ) {
			/**
			 * The Configuration Form
			 */
			case 2:
		?>
		<h3 class="card-header">Setup Configuration</h3>
		<div class="card-body">
			<form id="config" method="post" action="#" class="needs-validation" novalidate>
				<input type="hidden" name="step" value="2" />
				<input type="hidden" name="language" value="en_US" />
				<input type="hidden" name="charset" value="utf8mb4" />
				<?php
				/**
				 * Print out form errors
				 */
				if( ! empty($errors) )
					echo '<div class="alert alert-danger text-center">', $errors, '</div>';
				unset($errors);
				?>
				<div class="alert alert-info">Below you should enter your database connection details. If you&#8217;re not sure about these, contact your host.</div>
				<div class="mb-3">
					<label class="form-label" for="dbHost">Database Host Name</label>
					<div class="input-group input-group-lg">
						<div class="input-group-text"><?=icon('server')?></div>
						<input class="form-control<?=( in_array( 'dbHost', $causes ) ? ' is-invalid' : '' )?>" type="text" id="dbHost" name="dbHost" value="<?=$config->dbHost?>" required minlength="3" maxlength="20" />
					</div>
				</div>
				<div class="mb-3">
					<label class="form-label" for="dbName">Database Name</label>
					<div class="input-group input-group-lg">
						<div class="input-group-text"><?=icon('database')?></div>
						<input class="form-control<?=( in_array( 'dbName', $causes ) ? ' is-invalid' : '' )?>" type="text" id="dbName" name="dbName" value="<?=$config->dbName?>" required minlength="3" maxlength="20" />
					</div>
				</div>
				<div class="mb-3">
					<label class="form-label" for="dbUser">Username</label>
					<div class="input-group input-group-lg">
						<div class="input-group-text"><?=icon('user-secret')?></div>
						<input class="form-control<?=( in_array( 'dbUser', $causes ) ? ' is-invalid' : '' )?>" type="text" id="dbUser" name="dbUser" value="<?=$config->dbUser?>" required minlength="3" maxlength="20" />
					</div>
				</div>
				<div class="mb-3">
					<label class="form-label" for="password">Password</label>
					<div class="input-group input-group-lg">
						<div class="input-group-text"><?=icon('lock')?></div>
						<input class="form-control<?=( in_array( 'password', $causes ) ? ' is-invalid' : '' )?>" type="text" id="password" name="password" value="<?=$config->password?>" />
					</div>
				</div>
				<button name="submit" type="submit" class="btn btn-primary float-end">Submit</button>
			</form>
		</div>
		<?php
		break;
			default:
		/**
		 * An Introductory / Welcome Page
		 */
		?>
		<h3 class="card-header">Before getting started</h3>
		<div class="card-body pb-1">
			<p class="card-text">Welcome to Sevida. Before getting started, we need some information on the database. You will need to know the following items before proceeding.</p>
		</div>
		<ol class="list-group">
			<li class="list-group-item">Database name</li>
			<li class="list-group-item">Database userName</li>
			<li class="list-group-item">Database password</li>
			<li class="list-group-item">Database host</li>
		</ol>
		<div class="card-body pt-1">
			<span>We’re going to use this information to create a <code>Manifest.php</code> file. If for any reason this automatic file creation doesn’t work, don’t worry. All this does is fill in the database information to a configuration file.<br>
			In all likelihood, these items were supplied to you by your Web Host. If you don’t have this information, then you will need to contact them before you can continue. If you’re all ready…</span>
			<br><br>
			<a href="?step=2" class="btn btn-primary float-end">Proceed</a>
		</div>
		<?php
			break;
		}
		?>
	</div>
</div>
<?php
include( 'html-footer.php' );
