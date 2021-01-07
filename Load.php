<?php
/**
 * Blog Base Components Loader
 * 
 * Connection to the blog database is done here. Provided not haltered
 *
 * Loads base libraries and does some house-keeping
 *
 * @package Sevida
 */
// Start output buffering
ob_start();
/**
 * Wether or not to start/resume session
 * @global bool NO_SESS Toggles creating or resumin session on/off
 */
if( ! defined( 'NO_SESS' ) || NO_SESS )
	session_start();

if( ! defined( 'ABSPATH' ) )
	/**
	 * Absolute root directory where the blog is installed
	 *
	 * @var string ABSPATH Provided it is not defined
	 */
	define( 'ABSPATH', dirname( __FILE__ ) );

if( ! defined( 'BASE_UTIL' ) )
	/**
	 * Relative root utitlities directory 
	 *
	 * @var string BASE_UTIL Provided it is not defined
	 */
	define( 'BASE_UTIL', '/utils' );

if( ! defined( 'USER_UTIL' ) )
	/**
	 * Relative user-based utitlities directory 
	 *
	 * @var string USER_UTIL Provided it is not defined
	 */
	define( 'USER_UTIL', '/user-cp' . BASE_UTIL );

// We do not wish to accept requests with 'HEAD' as method
if ( 'HEAD' === $_SERVER['REQUEST_METHOD'] )
	exit;
// In case a request to a non-existing favicon.ico file
if ( false !== strpos( $_SERVER['REQUEST_URI'], '/favicon.ico' ) ) {
	header( 'Content-Type: image/vnd.microsoft.icon' );
	exit;
}

/**
 * Globalize version vars now because they will be defined 
 * in a sub-directory file.
 */
global $_blogVersion, $_dbVersion, $_phpVersion, $_mySQLVersion;
// Loads the blog version and minimal requirements information
require( ABSPATH . BASE_UTIL . '/Blog.php' );
// Very Bases functions which are PHP-5 compatible functions
require( ABSPATH . BASE_UTIL . '/Base.php' );

/**
 * Binds a custom error and exception handlers pretty-print errors and to avoid html/json
 * response header problems
 *
 * @global showError
 */
$errorHandler = function( $errCode, $errText, $errFile, $errLine ) {
	$errText = sprintf( '%s<br>Location: <code>%s</code> at line <code>%s</code>', $errText, $errFile, $errLine );
	showError( internalServerError(), $errText );
};
set_error_handler( $errorHandler );
set_exception_handler( function( $exception ) use( $errorHandler ) {
	$errorHandler( $exception->getCode(), $exception->getMessage(), $exception->getFile(), $exception->getLine() );
} );
unset($errorHandler);

/**
 * PHP and mysql version check : If they do not meet the requiremnt, and error page
 * displays with an appropriate response header
 *
 * @var string $phpVersion The current php version
 */
$phpVersion = phpversion();
if ( version_compare( $_phpVersion, $phpVersion, '>' ) )
	showError( internalServerError(), sprintf( 'Your server is running PHP version %1$s but Blog Software %2$s requires at least %3$s.', $phpVersion, $_blogVersion, $_phpVersion ) );
if ( ! extension_loaded( 'mysql' ) && ! extension_loaded( 'mysqli' ) && ! extension_loaded( 'mysqlnd' ) )
	showError( internalServerError(), 'Your PHP installation appears to be missing the MySQL extension which is required by Blog.' );

/**
 * Checks globals and standardize them due to server software difference
 * Prohibits global variable override
 */
if ( ini_get( 'register_globals' ) ) {
	if ( isset( $_REQUEST['GLOBALS'] ) )
		showError( internalServerError(), 'GLOBALS overwrite attempt detected.' );
	$noUnset = array( 'GLOBALS', '_GET', '_POST', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES' );
	$input = array_merge( $_GET, $_POST, $_COOKIE, $_SERVER, $_ENV, $_FILES, (isset( $_SESSION ) && is_array( $_SESSION ) ? $_SESSION : [] ) );
	foreach ( $input as $k => $v )
		if ( !in_array( $k, $noUnset ) && isset( $GLOBALS[$k] ) ) {
			unset( $GLOBALS[$k] );
		}
}

/**
 * Fix the $_SERVER variable to be as same as possible with all server softwares
 */
$defaultServerValues = array(
	'SERVER_SOFTWARE' => '',
	'REQUEST_URI' => '',
);
$_SERVER = array_merge( $defaultServerValues, $_SERVER );
// Fix for IIS when running with PHP ISAPI
if ( empty( $_SERVER['REQUEST_URI'] ) || ( PHP_SAPI != 'cgi-fcgi' && preg_match( '/^Microsoft-IIS\//', $_SERVER['SERVER_SOFTWARE'] ) ) ) {
	// IIS Mod-Rewrite
	if ( isset( $_SERVER['HTTP_X_ORIGINAL_URL'] ) ) {
		$_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_ORIGINAL_URL'];
	}
	// IIS Isapi_Rewrite
	elseif ( isset( $_SERVER['HTTP_X_REWRITE_URL'] ) ) {
		$_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_REWRITE_URL'];
	} else {
		// Use ORIG_PATH_INFO if there is no PATH_INFO
		if ( !isset( $_SERVER['PATH_INFO'] ) && isset( $_SERVER['ORIG_PATH_INFO'] ) )
			$_SERVER['PATH_INFO'] = $_SERVER['ORIG_PATH_INFO'];
		// Some IIS + PHP configurations puts the script-name in the path-info (No need to append it twice)
		if ( isset( $_SERVER['PATH_INFO'] ) ) {
			if ( $_SERVER['PATH_INFO'] == $_SERVER['SCRIPT_NAME'] )
				$_SERVER['REQUEST_URI'] = $_SERVER['PATH_INFO'];
			else
				$_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'] . $_SERVER['PATH_INFO'];
		}
		// Append the query if it exists and isn't null
		if ( ! empty( $_SERVER['QUERY_STRING'] ) ) {
			$_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
		}
	}
}
// Fix for PHP AS CGI hosts that set SCRIPT_FILENAME to something ending in php.cgi for all requests
if ( isset( $_SERVER['SCRIPT_FILENAME'] ) && ( strpos( $_SERVER['SCRIPT_FILENAME'], 'php.cgi' ) == strlen( $_SERVER['SCRIPT_FILENAME'] ) - 7 ) )
	$_SERVER['SCRIPT_FILENAME'] = $_SERVER['PATH_TRANSLATED'];
// Fix for Dreamhost and other PHP AS CGI hosts
if ( strpos( $_SERVER['SCRIPT_NAME'], 'php.cgi' ) !== false )
	unset( $_SERVER['PATH_INFO'] );
// Fix empty PHP_SELF
$PHP_SELF = $_SERVER['PHP_SELF'];
if ( empty( $PHP_SELF ) )
	$_SERVER['PHP_SELF'] = $PHP_SELF = preg_replace( '/(\?.*)?$/', '', $_SERVER["REQUEST_URI"] );
unset($defaultServerValues);

date_default_timezone_set( 'UTC' );

/**
 * Initialize a time to keep track of loading speed
 *
 * @global startTimer
 */
startTimer();

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
 *
 * @var bool SE_CONFIG
 */
define( 'SE_KONFIG', file_exists( ABSPATH . '/Manifest.php' ) );
if( SE_KONFIG ) {
	require( ABSPATH . '/Manifest.php' );
	ERROR_REPORTING( SE_DEBUG ? ( E_ALL | E_STRICT | E_WARNING ) : 0 );
} else {
	/**
	 * Fallback values for the contants that were to be defined in Manifest.php file
	 *
	 * Falls back to debug mode by default
	 */
	define( 'SE_DEBUG', true );
	define( 'BASE_URL', getBaseUrl() );
	define( 'BASEPATH', getBasePath() );
	
	/**
	 * Checks if we are not already running a configuration, then we redirect (once) to it
	 *
	 * Checks and destroys any active session
	 * 
	 * @global BASEPATH
	 */
	if ( ! defined('SE_CONFIG') ) {
		if( session_id() )
			session_destroy();
		header( getProtocol() . ' 307 Moved Temporarily', true, 307 );
		redirect( BASEPATH . '/user-cp/config.php' );
	}
}
/**
 * Relative images directory to be appended or prepended with another abspath
 * 
 * @var string DIR_IMAGES
 */
define( 'DIR_IMAGES', '/images/' );
/**
 * Directory for saving timely generated backups, relative to root
 * 
 * @var string DIR_BACKUP
 */
define( 'DIR_BACKUP', '/storage/backup/' );
/**
 * Directory for saving cached pages, relative to root
 * 
 * @var string DIR_CACHES 
 */
define( 'DIR_CACHES', '/storage/caches/' );
/**
 * Directory where uploaded files a saved, relative to root
 * 
 * @var string DIR_UPLOAD
 */
define( 'DIR_UPLOAD', '/storage/uploads/' );

// Registers and auto loader for hamdling unincluded classes loading
spl_autoload_register(
	/**
	 * The custom callback to be assigned
	 * @param mixed $className Class name of the required class
	 */
	function( $className ) {
    	$className = strNoBs( $className );
		require( ABSPATH . BASE_UTIL . DIRECTORY_SEPARATOR . $className . '.php' );
	}
);

// Loads general functions for performing little tasks and parsing 
require( ABSPATH . BASE_UTIL . '/Util.php' );

/**
 * @var string USERPATH Absolute path for logged-user-related pages and libraries
 */
define( 'USERPATH', BASEPATH . '/user-cp' );
/**
 * @global bool isLoggedIn()
 * @var string LOGGED_IN Caches user login status returned by the function call
 */
define( 'LOGGED_IN', isLoggedIn() );

/**
 * Checks if only minimum functions are required of this file, breaks if true
 * 
 * @global bool MINI_LOAD Tell if we are loading database and site options
 */
if( defined('MINI_LOAD') && MINI_LOAD )
	return false;

global $db;
/**
 * Instantiate a database connection using defined variables from Manifest.php
 *
 * Displays an error page on connection failure
 *
 * @global mixed Database class extending PHP PDO class
 * @global string DB_HOST
 * @global string DB_NAME
 * @global string DB_CHRS
 * @global string DB_USER
 * @global string DB_PASS
 * @global void showError
 * @var mixed $db Relative images directory to be appended or prepended with another abspath
 * @throws Exception Provided the connection failed
 */
try {
	$db = new Database( DB_HOST, DB_NAME, DB_CHRS, DB_USER, DB_PASS );
	if( ! $db->dbConnect() )
		throw new Exception();
	// Globalize $db variable
	$GLOBALS['db'] = $db;
} catch( Exception $e ) {
	unset($db);
	// Show user some nice connection error message
	showError(
		internalServerError(),
		'This either means that the userName and password information in your Manifest.php file is incorrect or we can’t contact the database server at localhost. This could mean your host’s database server is down.'.
		'<ul><li>Are you sure you have the correct userName and password?</li>'.
		'<li>Are you sure that you have typed the correct hostname?</li>'.
		'<li>Are you sure that the database server is running?</li></ul>'.
		'<p>If you’re unsure what these terms mean you should probably contact your host. If you still need help you can contact the developer at jikamshiahmad@gmail.com</p>'.
		'<p>You may also </p><p class="step"><a href="' . BASEPATH . '/" class="btn btn-large">Retry</a></p>'
	);
}
global $cfg;
/**
 * Load blog configuration from database
 *
 * If no installation is found, it clears any active session and redirects to an installation
 * page if we are not running any
 *
 * @var \Database $db Holding the database PDO object
 * @var \Config $cfg Holds the blog configuration
 * @var $GLOBALS['cfg']
 * @throws Exeption Provided the query failed
 */
try {
	/**
	 * 
	 */
	$cfg = $db->prepare( 'SELECT metaKey, metaValue FROM Config WHERE metaKey IN (?,?,?,?,?,?)' );
	$cfg->execute( [ 'blogName', 'blogDesc', 'blogDate', 'installed', 'searchable', 'permalink' ] );
	if( 0 === $cfg->rowCount() )
		throw new Exception();
	$cfg = new Config( $cfg->fetchAll() );
	$cfg->timeZone = timezone_open( 'UTC' );
	$GLOBALS['cfg'] = $cfg;
} catch( Exception $e ) {
	unset($cfg);
	// Go ahread to the installation
	if ( ! defined('SE_INSTALL') ) {
		if( session_id() )
			session_destroy();
		redirect( USERPATH . '/install.php' );
	}
}
