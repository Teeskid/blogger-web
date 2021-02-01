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
 * @param array $options
 * @throws Exception Provided the file creation failed
 */
function generateManifest( array $options ) {
	$uniqueKeys = generateUniqueKeys(3);
	$dstFile = ABSPATH . '/Manifest.php';
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
		'define( \'DB_HOST\', \'' . $options['dbHost'] . '\' );', '',
		'/**',
		' * @var string Database name where the blog is installed',
		' */',
		'define( \'DB_NAME\', \'' . $options['dbName'] . '\' );', '',
		'/**',
		' * @var string The MYSQL Database userName',
		' */',
		'define( \'DB_USER\', \'' . $options['dbUser'] . '\' );', '',
		'/**',
		' * @var string The MYSQL Database Password',
		' */',
		'define( \'DB_PASS\', \'' . $options['dbPass'] . '\' );', '',
		'/**',
		' * @var string The character sets our blog database should use',
		' */',
		'define( \'DB_CHRS\', \'' . $options['charset'] . '\' );', '',
		'/**',
		' * - Generated and secured authentication tokens (do not edit).',
		' * - Editing these tokens may lead to all users having to reset their passwords',
		' */',
		'/**',
		' * @var string Token used to encrypt user password and password-reset tokens',
		' */',
		'define( \'AUTH_KEY\',        \'' . $uniqueKeys[0] . '\' );',
		'/**',
		' * @var string Token used to encrypt user session token',
		' */',
		'define( \'LOGIN_KEY\',   \'' . $uniqueKeys[1] . '\' );',
		'/**',
		' * @var string Token used to encrypt nonce keys. Nonce are for encrypting actions like editing a post',
		' */',
		'define( \'NONCE_SALT\',       \'' . $uniqueKeys[2] . '\' );', '',
		'/**',
		' * @var string Protocol and domain without and ending slash e.g http(s)://example.com',
		' */',
		'define( \'ROOTURL\', \'' . $options['rootUrl'] . '\' );',
		'/**',
		' * @var string The blog root path, if it\'s installed in a subdirectory of the server root,',
		' * 	 or it is an alias. Leave empty if you installed it in the server root.',
		' */',
		'define( \'BASEURI\', \'' . $options['baseUri'] . '\' );', ''
	];
	$content = implode( PHP_EOL, $content );
	if( ! file_put_contents( $dstFile, $content ) )
		throw new Exception( 'Failed to create <code>Manifest.php</code>' );
	chmod( $dstFile, 0666 );
}
/**
 * Generates root .htaccess file
 * @param array $options
 * @throws Exception Provided the file creation failed
 */
 function generateApacheRules( array $options ) {
	$dstFile = ABSPATH . '/.htaccess';
	$content = [
		'<IfModule mod_rewrite.c>',
		'RewriteEngine On',
		'RewriteBase ' . $options['baseUri'] . '/',
		(function() use( $options ) {
			$lines = [];
			if( $options['isHttps'] )
				$lines[] = 'RewriteCond %{HTTPS} off';
			if( false !== strpos( $options['rootUrl'], 'www.' ) )
				$lines[] = 'RewriteCond %{HTTP_HOST} !^www\.';
			$lines = implode( ' [OR] ' . PHP_EOL, $lines );
			if( $lines ) {
				$lines .= $lines . ' [NC]';
				$lines .= 'RewriteRule (.*) ' . $options['rootUrl'] . '%{REQUEST_URI} [L]';
			}
			return $lines;
		})(),
		'RewriteRule ^' . $options['baseUri'] . '/index\.php$ - [L]',
		'RewriteCond %{REQUEST_FILENAME} !-f',
		'RewriteCond %{REQUEST_FILENAME} !-d',
		'RewriteRule . ' . $options['baseUri'] . '/index.php [L]',
		'</IfModule>'
	];
	$content = implode( PHP_EOL, $content );
	if( ! file_put_contents( $dstFile, $content ) )
		throw new Exception( 'Failed to create <code>.htaccess</code> file' );
	chmod( $dstFile, 0666 );
 }
/**
 * Generates root lighttpd file which contains lighttpd rewrite rules
 * @param array $options
 * @throws Exception Provided the file creation failed
 */
function generateHttpdRules( array $options ) {
	$dstFile = ABSPATH . '/.lighttpd';
	$content = [
		'$HTTP["host"] = "(' . $options['httpHost'] . '.+)" {',
		'  url.rewrite-final = (',
		'	"^' . $options['baseUri'] . '/index.php$" => "' . $options['baseUri'] . '"',
		'  )',
		'  url.rewrite-if-not-file = (',
		'	"^' . $options['baseUri'] . '/.*$" => "' . $options['baseUri'] . '/index.php"',
		'  )',
		'}'
	];
	$content = implode( PHP_EOL, $content );
	if( ! file_put_contents( $dstFile, $content ) )
		throw new Exception( 'Failed to create <code>.lighttpd</code> file' );
	chmod( $dstFile, 0666 );
}
/**
 * Generates root robots.txt file for search engines
 * @param array $options
 * @throws Exception Provided the file creation failed
 */
function generateSearchRobot( array $options ) {
	$dstFile = ABSPATH . '/robots.txt';
	$content = [
		'User Agent *',
		'Allow ' . $options['baseUri'] . '/',
		'DisAllow ' . $options['baseUri'] . '/',
		'DisAllow ' . $options['baseUri'] . '/api/',
		'DisAllow ' . $options['baseUri'] . '/css/',
		'DisAllow ' . $options['baseUri'] . '/fonts/',
		'DisAllow ' . $options['baseUri'] . '/images/',
		'DisAllow ' . $options['baseUri'] . '/js/',
		'DisAllow ' . $options['baseUri'] . '/indexes/',
		'DisAllow ' . $options['baseUri'] . '/storage/',
		'DisAllow ' . $options['userPath'] . '/',
		'DisAllow ' . $options['baseUri'] . '/utils/',
		'DisAllow ' . $options['baseUri'] . '/.htaccess',
		'DisAllow ' . $options['baseUri'] . '/.lighttpd',
		'DisAllow ' . $options['baseUri'] . '/404.php',
		'DisAllow ' . $options['baseUri'] . '/build.php',
		'DisAllow ' . $options['baseUri'] . '/Cache.php',
		'DisAllow ' . $options['baseUri'] . '/Load.php',
		'DisAllow ' . $options['baseUri'] . '/Manifest.php',
		'DisAllow ' . $options['baseUri'] . '/Handler.php',
		'Sitemap: ' . $options['rootUrl'] . $options['baseUri'] . '/sitemap.xml'
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
function generateUniqueKeys( int $maxlen = 1, int $size = 32 ) : array {
	$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	$secretKey = [];
	$max = strlen( $chars ) - 1;
	for ( $i = 0; $i < $maxlen; $i++ ) {
		$key = '';
		for ( $j = 0; $j < $size; $j++ ) {
			$key .= substr( $chars, rand( 0, $max ), 1 );
		}
		$secretKey[] = $key;
	}
	return $secretKey;
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
