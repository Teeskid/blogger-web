<?php
/**
 * Blog Base Components Loader
 *
 * Loads base libraries and does some house-keeping
 *
 * @package Sevida
 */
// Deny head pings
if ( 'HEAD' === $_SERVER['REQUEST_METHOD'] )
	exit;
// Favicon request
if ( false !== strpos( $_SERVER['REQUEST_URI'], '/favicon.ico' ) ) {
	header( 'Content-Type: image/vnd.microsoft.icon' );
	exit;
}

ob_start();
if( ! defined('USE_SESSION') || USE_SESSION )
	session_start();

/**
 * Absolute root directory where the blog is installed
 * @var string
 */
define( 'ABSPATH', __DIR__ );
/**
 * Relative root utitlities directory
 * @var string
 */
define( 'BASE_UTIL', '/utils' );

/** Version Information */
global $_blogVersion, $_dbVersion, $_phpVersion, $_mySQLVersion;
// Loads the blog version and minimal requirements information
require( ABSPATH . BASE_UTIL . '/Blog.php' );
// Very Bases functions which are PHP-5 compatible functions
require( ABSPATH . BASE_UTIL . '/Base.php' );

date_default_timezone_set( 'UTC' );
checkVersions();
bindErrorHandlers();
fixRequestVars();
fixServerVars();
startTimer();

/**
 * Relative user-based utitlities directory
 * @var string
 */
define( 'USER_UTIL', '/user-cp' . BASE_UTIL );

/**
 * Checks an existing Manifest.php file and caches the check (saves memory)
 * 
 * Loads the Manifest.php file provided one exists
 *
 * Manifest.php does some contants definition of the blog url, blog path
 * and unique generated authentication tokens
 *
 * If the manifest.php file does not exist, it destroys any active sessoon and
 * and redirects to a configuration file if it is not already there
 */
/**
 * A configuration file exists
 * @var bool
 */
define( 'SE_KONFIG', file_exists( ABSPATH . '/Manifest.php' ) );
/**
 * Load blog manifest file
 */
if( SE_KONFIG ) {
	require( ABSPATH . '/Manifest.php' );
	if( ! SE_DEBUG )
		error_reporting(0);
} else {
	// Try running a configuration
	if ( ! defined('SE_SETUP') ) {
		if( session_id() )
			session_destroy();
		redirect( getBaseUri() . '/user-cp/setup.php' );
	}
	/**
	 * @var bool
	 */
	define( 'SE_DEBUG', true );
	/**
	 * @var string
	 */
	define( 'ROOTURL', getOrigin() );
	/**
	 * @var string
	 */
	define( 'BASEURI', getBaseUri() );
}
/**
 * Absolute uri to user-cp
 * @var string
 */
define( 'USERURI', BASEURI . '/user-cp' );

/**
 * Relative images directory
 * @var string
 */
define( 'DIR_IMAGES', '/images/' );
/**
 * Directory for timely generated backups
 * @var string
 */
define( 'DIR_BACKUP', '/storage/backup/' );
/**
 * Directory for saving cached pages, relative to root
 * @var string
 */
define( 'DIR_CACHES', '/storage/caches/' );
/**
 * Directory where uploaded files a saved, relative to root
 * @var string
 */
define( 'DIR_UPLOAD', '/storage/uploads/' );

// Registers and auto loader for hamdling unincluded classes loading
spl_autoload_register( 'classLoader' );

// Loads general functions for performing little tasks and parsing 
require( ABSPATH . BASE_UTIL . '/Utils.php' );

// If we are told to stop here
if( defined('SHORT_INIT') && SHORT_INIT )
	return false;

/** Conect to database */
global $_db;
if( SE_KONFIG ) {
	$_db = new Database( DB_HOST, DB_NAME, DB_CHRS, DB_USER, DB_PASS );
	if( ! $_db->dbConnect() ) {
		unset($_db);
		sendError(
			'Connection to database could not be initialized. Maybe error incorrect parameters provided or the database is down.',
			'Database Connection Error'
		);
	}
}

/** Fetch blog configuration */
global $_cfg;
if( $_db->connect ) {
	$_cfg = new Config();
	$_cfg->loadFields( 'blogName', 'blogDesc','blogDate','searchable','permalink','installed' );
	if( $_cfg->installed === null ) {
		unset($_cfg);
		if ( ! defined('SE_INSTALL') ) {
			if( session_id() )
				session_destroy();
			redirect( USERURI . '/install.php' );
		}
	}
	$GLOBALS['_cfg'] = $_cfg;
}

/** Current logged user object */
global $_usr;
if( isset($_cfg) ) {
	$tmpLogin = getUserLogin();
	if( $tmpLogin !== false && $tmpLogin->userId !== 0 ) {
		$_usr = new User();
		$_usr->id = $tmpLogin->userId;
		
	}
	unset($tmpLogin);
}
