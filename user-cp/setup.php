<?php
/**
 * Setup Configuration Page
 * 
 * Generates base configuration files and blog manifest file
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
define( 'SHORT_INIT', true );
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
$_cfg = new Config( [
	'blogName' => 'Sevida',
	'installed' => '2020'
] );

$errors = [];
$causes = [];
if( isPostRequest() ) {
	/**
	 * @var array
	 */
	$config = request( 'dbHost', 'dbUser', 'dbPass', 'dbName', 'charset' );
	/** Validate the submitted data  */
	if( 5 > strlen($config['dbHost']) ) {
		$errors[] = 'invalid database host';
		$causes[] = 'dbHost';
	}
	if( 4 > strlen($config['dbUser']) ) {
		$errors[] = 'invalid database user';
		$causes[] = 'dbUser';
	}
	if( 3 > strlen($config['dbName']) ) {
		$errors[] = 'Invalid database name';
		$causes[] = 'dbName';
	}
	if( empty($errors) ) {
		try {
			/** Fork a trial connection */
			$_db = new Database( $config['dbHost'], $config['dbName'], $config['charset'], $config['dbUser'], (string) $config['dbPass'] );
			if( ! $_db->dbConnect() )
				throw new Exception( 'Server not running or details details provided are incorrect' );
			/** Embedd blog url and path in $config */
			$config['isHttps'] = isHttps();
			$config['rootUrl'] = ROOTURL;
			$config['baseUri'] = BASEURI;
			$config['userPath'] = USERURI;
			$config['httpHost'] = $_SERVER['HTTP_HOST'];
			// Lets generate robots.txt file in the root directory of our blog
			generateSearchRobot( $config );
			// Generate .htaccess file in the root
			generateApacheRules( $config );
			// Generate .lighttpd file
			generateHttpdRules( $config );
			// Generate Manifest.php file
			generateManifest( $config );
			// all is well, head to installation
			redirect( 'install.php' );
		} catch ( Exception $e ) {
			unlinkUserFiles();
			$errors[] = $e->getMessage();
		}
	}
} else {
	$config = [
		'dbHost' => '',
		'dbUser' => '',
		'dbName' => '',
		'dbPass' => '',
	];
}
require( ABSPATH . BASE_UTIL . '/HtmlUtil.php' );

$config = array_map( 'escHtml', $config );
$errors = implode( '<br>', $errors );

initHtmlPage( 'Setup Configuration', 'setup.php' );
addPageCssFile( 'css/compact.css' );
include_once( __DIR__ . '/header.php' );
?>
<div class="col-sm-6 offset-sm-3 col-xl-4 offset-xl-4 mb-3">
	<div id="logo"><a href="http://techify.ng/">Sevida</a></div>
	<div class="card bg-light text-dark">
		<h2 class="card-header">Setup Configuration</h2>
		<div class="card-body">
			<form id="config" method="post" action="" autocomplete="off" novalidate>
				<input type="hidden" name="language" value="en_US" />
				<input type="hidden" name="charset" value="utf8mb4" />
				<?php
				/** Print out form errors */
				if( ! empty($errors) )
					echo '<div class="alert alert-danger text-center">', $errors, '</div>';
				unset($errors);
				?>
				<div class="alert alert-info">Below you should enter your database connection details. If you&#8217;re not sure about these, contact your host.</div>
				<div class="mb-3">
					<label class="form-label" for="dbHost">Database Host Name</label>
					<div class="input-group input-group-lg">
						<div class="input-group-text"><?=icon('server')?></div>
						<input class="form-control<?=( in_array( 'dbHost', $causes ) ? ' is-invalid' : '' )?>" type="text" id="dbHost" name="dbHost" value="<?=$config['dbHost']?>" minlength="3" maxlength="20" />
					</div>
				</div>
				<div class="mb-3">
					<label class="form-label" for="dbUser">Username</label>
					<div class="input-group input-group-lg">
						<div class="input-group-text"><?=icon('user-secret')?></div>
						<input class="form-control<?=( in_array( 'dbUser', $causes ) ? ' is-invalid' : '' )?>" type="text" id="dbUser" name="dbUser" value="<?=$config['dbUser']?>" minlength="3" maxlength="20" />
					</div>
				</div>
				<div class="mb-3">
					<label class="form-label" for="dbPass">Password</label>
					<div class="input-group input-group-lg">
						<div class="input-group-text"><?=icon('lock')?></div>
						<input class="form-control<?=( in_array( 'dbPass', $causes ) ? ' is-invalid' : '' )?>" type="text" id="dbPass" name="dbPass" value="<?=$config['dbPass']?>" />
					</div>
				</div>
				<div class="mb-3">
					<label class="form-label" for="dbName">Database Name</label>
					<div class="input-group input-group-lg">
						<div class="input-group-text"><?=icon('database')?></div>
						<input class="form-control<?=( in_array( 'dbName', $causes ) ? ' is-invalid' : '' )?>" type="text" id="dbName" name="dbName" value="<?=$config['dbName']?>" minlength="3" maxlength="20" />
					</div>
				</div>
				<button name="submit" type="submit" class="btn btn-primary float-end">Submit</button>
			</form>
		</div>
	</div>
</div>
<?php
include_once( __DIR__ . '/footer.php' );
