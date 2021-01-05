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
define( 'SE_HTML', true );
define( 'SE_CONFIG', true );
define( 'MINI_LOAD', true );

require( dirname(__DIR__) . '/Load.php' );
require( ABSPATH . BASE_UTIL . '/UIUtil.php' );
require( ABSPATH . USER_UTIL . '/ConfigUtil.php' );

if ( SE_KONFIG )
	redirect( 'install.php' );

noCacheHeaders();

$cfg = new Config();
$cfg->blogName = 'Sevida';
$cfg->installed = '2020';

$step = request('step') ?? 1;
$errors = [];

if( isPostRequest() ) {
	$config = request( 'dbHost', 'dbUser', 'password', 'dbName', 'charset' );

	if( ! $config->dbHost )
		$errors[] = 'Invalid database host';
	if( ! $config->dbUser )
		$errors[] = 'Invalid database user';
	if( ! $config->dbName )
		$errors[] = 'Invalid database name';
	try {
		if( isset($errors[0]) )
			throw new Exception();

		$db = new Database( $config->dbHost, $config->dbName, $config->charset, $config->dbUser, (string) $config->password );
		if( ! $db->dbConnect() )
			throw new Exception( 'Server not running or details invalid' );

		$dstFile = ABSPATH . '/robots.txt';
		$content = [
			'User Agent *',
			'Allow '.BASEPATH.'/',
			'DisAllow '.BASEPATH.'/',
			'DisAllow '.BASEPATH.'/api/',
			'DisAllow '.BASEPATH.'/css/',
			'DisAllow '.BASEPATH.'/images/',
			'DisAllow '.BASEPATH.'/js/',
			'DisAllow '.BASEPATH.'/handlers/',
			'DisAllow '.BASEPATH.'/storage/',
			'DisAllow '.BASEPATH.'/user-cp/',
			'DisAllow '.BASEPATH.'/utils/',
			'DisAllow '.BASEPATH.'/.htaccess',
			'DisAllow '.BASEPATH.'/.lighttpd',
			'DisAllow '.BASEPATH.'/404.php',
			'DisAllow '.BASEPATH.'/501.php',
			'DisAllow '.BASEPATH.'/build.php',
			'DisAllow '.BASEPATH.'/Load.php',
			'DisAllow '.BASEPATH.'/Manifest.php',
			'DisAllow '.BASEPATH.'/Rewrite.php',
			'Sitemap: '.BASE_URL.'/sitemap.xml'
		];
		$content = implode( PHP_EOL, $content );
		if( ! file_put_contents( $dstFile, $content ) )
			throw new Exception( 'Failed to create <code>robots.txt</code> file' );
		chmod( $dstFile, 0666 );

		$isHttps = isHttps();
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
		if( ! file_put_contents( ABSPATH . '/.htaccess', $content ) )
			throw new Exception( 'Failed to create <code>.htaccess</code> file' );
		chmod( ABSPATH . '/.htaccess', 0666 );

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

		$authVars = generateToken(3);
		$content = [
			'<?php',
			'/**' ,
			' * Project: Blog Management System With Sevida-Like UI',
			' * Developed By: Ahmad Tukur Jikamshi',
			' *',
			' * @facebook: amaedyteeskid',
			' * @twitter: amaedyteeskid',
			' * @instagram: amaedyteeskid',
			' * @whatsapp: +2348145737179',
			' */', '',
			'// boolean to enable/disable debug mode',
			'define( \'SE_DEBUG\', true );', '',
			'// database host name',
			'define( \'DB_HOST\', \'' . $config->dbHost . '\' );', '',
			'// database name where the blog is installed',
			'define( \'DB_NAME\', \'' . $config->dbName . '\' );', '',
			'// the mysql userName',
			'define( \'DB_USER\', \'' . $config->dbUser . '\' );', '',
			'// the mysql password',
			'define( \'DB_PASS\', \'' . $config->password . '\' );', '',
			'// the database charset',
			'define( \'DB_CHRS\', \'' . $config->charset . '\' );', '',
			'// secured auth tokens',
			'define( \'AUTH_SALT\',        \'' . $authVars[0] . '\' );',
			'define( \'LOGGED_SALT\',   \'' . $authVars[1] . '\' );',
			'define( \'NONCE_SALT\',       \'' . $authVars[2] . '\' );', '',
			'// the site full url without ending slash',
			'define( \'BASE_URL\', \'' . BASE_URL . '\' );',
			'// the site root path',
			'define( \'BASEPATH\', \'' . BASEPATH . '\' );', ''
		];
		$content = implode( PHP_EOL, $content );
		if( ! file_put_contents( ABSPATH . '/Manifest.php', $content ) )
			throw new Exception( 'Failed to create <code>Manifest.php</code>' );
		chmod( ABSPATH . '/Manifest.php', 0666 );

		redirect( 'install.php' );
	} catch ( Exception $e ) {
		$errors[] = $e->getMessage();
		unlinkUserFiles();
	}
} else {
	$config = [
		'dbHost' => 'localhost',
		'dbUser' => 'root',
		'dbName' => 'sevida',
		'password' => ''
	];
	$config = (object) array_map( 'htmlentities', $config );
}
$_page = new Page( 'Setup Configuration', '/user-cp/config.php?step=' . $step );
$_page->setMetaItem( Page::META_CSS_FILE, 'css/compact.css' );
include( 'html-header.php' );
?>
<div class="container">
	<div id="logo"><a href="http://techify.ng/">Sevida</a></div>
	<div class="container-sm">
<?php
switch( $step ) {
	case 2:
?>
		<div class="panel panel-primary">
			<div class="panel-heading">Set up your database connection</div>
			<div class="panel-body">
				<form role="form" class="form" name="form" method="post" action="">
					<input type="hidden" name="step" value="2" />
<?php
	if( isset($errors[0]) ) {
		$errors = implode( '<br>', $errors );
		echo '<div class="alert alert-danger text-center">', $errors, '</div>';
	} else {
		echo '<p class="alert alert-info">Below you should enter your database connection details. If you&#8217;re not sure about these, contact your host.</p>';
	}
	unset($errors);
?>
					<div class="form-group form-group-lg">
						<label class="control-label" for="dbHost">Database Host</label>
						<div class="input-group">
							<?=icon('server','','input-group-addon')?>
							<input class="form-control" type="text" id="dbHost" name="dbHost" value="<?=$config->dbHost?>" required />
						</div>
					</div>
					<div class="form-group form-group-lg">
						<label class="control-label" for="dbName">Database Name</label>
						<div class="input-group">
							<?=icon('database','','input-group-addon')?>
							<input class="form-control" type="text" id="dbName" name="dbName" value="<?=$config->dbName?>" required />
						</div>
					</div>
					<div class="form-group form-group-lg">
						<label class="control-label" for="dbUser">Username</label>
						<div class="input-group">
							<?=icon('user-secret','','input-group-addon')?>
							<input class="form-control" type="text" id="dbUser" name="dbUser" value="<?=$config->dbUser?>" required />
						</div>
					</div>
					<div class="form-group form-group-lg">
						<label class="control-label" for="password">Password</label>
						<div class="input-group">
							<?=icon('lock','','input-group-addon')?>
							<input class="form-control" type="text" id="password" name="password" value="<?=$config->password?>" />
						</div>
					</div>
					<input type="hidden" name="language" value="en_US" />
					<input type="hidden" name="charset" value="utf8mb4" />
					<p>
						<button name="submit" type="submit" class="btn btn-primary">Submit</button>
					</p>
				</form>
			</div>
		</div>
<?php
	break;
	default:
?>
		<div class="panel panel-primary">
			<div class="panel-heading">Before getting started</div>
			<div class="panel-body">
				<p class="alert alert-success">Welcome to Sevida. Before getting started, we need some information on the database. You will need to know the following items before proceeding.</p>
				<ol class="list-group">
					<li class="list-group-item">Database name</li>
					<li class="list-group-item">Database userName</li>
					<li class="list-group-item">Database password</li>
					<li class="list-group-item">Database host</li>
				</ol>
				<span>We’re going to use this information to create a <code>Manifest.php</code> file. If for any reason this automatic file creation doesn’t work, don’t worry. All this does is fill in the database information to a configuration file.<br>
				In all likelihood, these items were supplied to you by your Web Host. If you don’t have this information, then you will need to contact them before you can continue. If you’re all ready…</span>
				<br><br>
				<a href="?step=2" class="btn btn-primary">Proceed</a>
			</div>>
		</div>
<?php
	break;
}
?>
	</div>
</div>
<?php
include( 'html-footer.php' );
