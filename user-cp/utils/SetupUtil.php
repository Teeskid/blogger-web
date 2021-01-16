<?php
/**
 * Setup Configuartion Utilities
 *
 * Functions here used in setup.php
 * 
 * @package Sevida
 * @subpackage Administration
 */
/**
 * Generates Manifest.php which marks our blog configuration
 * @param object $config
 * @throws Exception Provided the file creation failed
 */
function generateManifestFile( object $config ) {
	$authVars = generateToken(3);
	$content = [
		'<?php',
		'/**' ,
		' * Blog Setup Configuration',
		' *',
		' * This file is generated during its configuration. So, unless the blog is reset,',
		' * this file should be regenerated. Do not touch the authentication tokens.',
		' *',
		' * @package Sevida',
		' */', '',
		'/**',
		' * @var bool Toggles debug mode on / off',
		' */',
		'define( \'SE_DEBUG\', true );', '',
		'/**',
		' * @var string Database host name',
		' */',
		'define( \'DB_HOST\', \'' . $config->dbHost . '\' );', '',
		'/**',
		' * @var string Database name where the blog is installed',
		' */',
		'define( \'DB_NAME\', \'' . $config->dbName . '\' );', '',
		'/**',
		' * @var string The MYSQL Database userName',
		' */',
		'define( \'DB_USER\', \'' . $config->dbUser . '\' );', '',
		'/**',
		' * @var string The MYSQL Database password',
		' */',
		'define( \'DB_PASS\', \'' . $config->password . '\' );', '',
		'/**',
		' * @var string The character sets our blog database should use',
		' */',
		'define( \'DB_CHRS\', \'' . $config->charset . '\' );', '',
		'/**',
		' * - Generated and secured authentication tokens (do not edit).',
		' * - Editing these tokens may lead to all users having to reset their passwords',
		' */',
		'/**',
		' * @var string Token used to encrypt user password and password-reset tokens',
		' */',
		'define( \'AUTH_KEY\',        \'' . $authVars[0] . '\' );',
		'/**',
		' * @var string Token used to encrypt user session token',
		' */',
		'define( \'LOGIN_KEY\',   \'' . $authVars[1] . '\' );',
		'/**',
		' * @var string Token used to encrypt nonce keys. Nonce are for encrypting actions like editing a post',
		' */',
		'define( \'NONCE_SALT\',       \'' . $authVars[2] . '\' );', '',
		'/**',
		' * @var string Protocol and domain without and ending slash e.g http(s)://example.com',
		' */',
		'define( \'BASE_URL\', \'' . BASE_URL . '\' );',
		'/**',
		' * @var string The blog root path, if it\'s installed in a subdirectory of the server root,',
		' * 	 or it is an alias. Leave empty if you installed it in the server root.',
		' */',
		'define( \'BASEPATH\', \'' . BASEPATH . '\' );', ''
	];
	$content = implode( PHP_EOL, $content );
	if( ! file_put_contents( ABSPATH . '/Manifest.php', $content ) )
		throw new Exception( 'Failed to create <code>Manifest.php</code>' );
	chmod( ABSPATH . '/Manifest.php', 0666 );
}
/**
 * Generates root .htaccess file
 * @throws Exception Provided the file creation failed
 */
 function generateHtaccessFile() {
	// detect if blog is using https
	$isHttps = isHttps();
	// Path to the file
	$dstFile = ABSPATH . '/.htaccess';
	$content = [
		'<IfModule mod_rewrite.c>',
		'RewriteEngine On',
		'RewriteBase ' . BASEPATH . '/',
		(function() use( $isHttps ) {
			$lines = [];
			if( $isHttps )
				$lines[] = 'RewriteCond %{HTTPS} off';
			if( false !== strpos( BASE_URL, 'www.' ) )
				$lines[] = 'RewriteCond %{HTTP_HOST} !^www\.';
			$lines = implode( ' [OR] ' . PHP_EOL, $lines );
			if( $lines ) {
				$lines .= $lines . ' [NC]';
				$lines .= 'RewriteRule (.*) ' . BASE_URL . '%{REQUEST_URI} [L]';
			}
			return $lines;
		})(),
		'RewriteRule ^' . BASEPATH . '/index\.php$ - [L]',
		'RewriteCond %{REQUEST_FILENAME} !-f',
		'RewriteCond %{REQUEST_FILENAME} !-d',
		'RewriteRule . ' . BASEPATH . '/index.php [L]',
		'</IfModule>'
	];
	$content = implode( PHP_EOL, $content );
	if( ! file_put_contents( $dstFile, $content ) )
		throw new Exception( 'Failed to create <code>.htaccess</code> file' );
	chmod( $dstFile, 0666 );
 }
/**
 * Generates root lighttpd file which contains lighttpd rewrite rules
 * @throws Exception Provided the file creation failed
 */
function generateLighttpdFile() {
	$content = [
		'$HTTP["host"] = "(' . $_SERVER['HTTP_HOST'] . '.+)" {',
		'  url.rewrite-final = (',
		'	"^' . BASEPATH . '/index.php$" => "' . BASEPATH . '"',
		'  )',
		'  url.rewrite-if-not-file = (',
		'	"^' . BASEPATH . '/.*$" => "' . BASEPATH . '/index.php"',
		'  )',
		'}'
	];
	$content = implode( PHP_EOL, $content );
	if( ! file_put_contents( ABSPATH . '/.lighttpd', $content ) )
		throw new Exception( 'Failed to create <code>.lighttpd</code> file' );
	chmod( ABSPATH . '/.lighttpd', 0666 );
}
/**
 * Generates root robots.txt file for search engines
 * @throws Exception Provided the file creation failed
 */
function generateRobotsFile() {
	$dstFile = ABSPATH . '/robots.txt';
	$content = [
		'User Agent *',
		'Allow ' . BASEPATH . '/',
		'DisAllow ' . BASEPATH . '/',
		'DisAllow ' . BASEPATH . '/api/',
		'DisAllow ' . BASEPATH . '/css/',
		'DisAllow ' . BASEPATH . '/fonts/',
		'DisAllow ' . BASEPATH . '/images/',
		'DisAllow ' . BASEPATH . '/js/',
		'DisAllow ' . BASEPATH . '/handlers/',
		'DisAllow ' . BASEPATH . '/storage/',
		'DisAllow ' . USERPATH . '/',
		'DisAllow ' . BASEPATH . '/utils/',
		'DisAllow ' . BASEPATH . '/.htaccess',
		'DisAllow ' . BASEPATH . '/.lighttpd',
		'DisAllow ' . BASEPATH . '/404.php',
		'DisAllow ' . BASEPATH . '/build.php',
		'DisAllow ' . BASEPATH . '/Cache.php',
		'DisAllow ' . BASEPATH . '/Load.php',
		'DisAllow ' . BASEPATH . '/Manifest.php',
		'DisAllow ' . BASEPATH . '/Rewrite.php',
		'Sitemap: ' . BASE_URL . BASEPATH . '/sitemap.xml'
	];
	$content = implode( PHP_EOL, $content );
	if( ! file_put_contents( $dstFile, $content ) )
		throw new Exception( 'Failed to create <code>robots.txt</code> file' );
	chmod( $dstFile, 0666 );
}
/**
 * Generates almost-unique tokens, you can adjust the size and if need to.
 * @param int $maxlen [optional] How many tokens do you want
 * @param int $size [optional] Lenght of the tokens. Default is 32
 * @return array An array n-length tokens
 */
function generateToken( int $maxlen = 1, int $size = 32 ) : array {
	$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	$secret_key = [];
	$max = strlen( $chars ) - 1;
	for ( $i = 0; $i < $maxlen; $i++ ) {
		$key = '';
		for ( $j = 0; $j < $size; $j++ ) {
			$key .= substr( $chars, rand( 0, $max ), 1 );
		}
		$secret_key[] = $key;
	}
	return $secret_key;
}
/**
 * Generate an almost-unique 2-bit token used as database name prefix
 * @return string The generated prefix
 */
function generatePrefix() : string {
	$chars = 'abcdefghijklmnopqrstuvwxyz';
	$max = strlen( $chars ) - 1;
	$pre = '';
	for( $x = 0; $x < 2; $x++ ) {
		$pre .= substr( $chars, rand( 0, $max ), 1 );
	}
	return $pre;
}
/**
 * Remove the blog configuration files
 * @param bool $withUpload [optional] Whether to remove generated and user uploaded files also
 */
function unlinkUserFiles( bool $withUpload = false ) {
	$files = [
		ABSPATH . '/.htaccess.php',
		ABSPATH . '/.lighttpd.php',
		ABSPATH . '/Manifest.php',
		ABSPATH . '/robots.txt'
	];
	$files = array_merge( $files, glob( ABSPATH . DIR_CACHES . '\*' ) );
	if( $withUpload === true )
		$files = array_merge( glob( ABSPATH . DIR_UPLOAD . '*.*' ), $files );
	foreach( $files as $file ) {
		if( file_exists($file) )
			unlink($file);
	}
}