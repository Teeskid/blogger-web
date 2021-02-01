<?php
/**
 * User Login and Password Reset Page
 * @package Sevida
 * @subpackage: Administration
 */
/**
 * Tells the bootstrap file that this is a login page
 * @var bool
 */
define( 'SE_LOGIN', true );

// Loads the blog bootstrap file
require( __DIR__ . '/Load.php' );
require( ABSPATH . BASE_UTIL . '/HtmlUtil.php' );

if( isset($_usr) )
	redirect( getReturnUrl( 'index.php' ) );

$action = request( 'action' );
if( ! in_array( $action, [ 'login', 'lostpass', 'recover' ] ) )
	$action = 'login';

switch( $action ) {
	case 'recover':
		$authToken = request('token');
		/** Let's try decrypting the token for validity sake */
		try {
			if( ! $authToken )
				throw new Exception( 'Invalid Token' );
			$authToken = \Firebase\JWT\JWT::decode( $authToken, AUTH_KEY, [ 'HS256' ] );
			if( ! is_object($authToken) )
				throw new Exception( 'Invalid Token' );
			if( $authToken->iss !== ROOTURL || $authToken->aud !== ROOTURL )
				throw new Exception( 'Token Compromised' );
			$authToken->iat = (int) $authToken->iat;
			if( parseInt( time() - $authToken->iat ) > 300 )
				throw new Exception( 'Expired Token' );
			$authToken->userId = (int) $authToken->userId;
			$authToken->aut = (int) $authToken->aut;
			if( ! $authToken->userId || ! $authToken->aut )
				throw new Exception( 'Invalid Token' );
		} catch( Exception $e ) {
			sendError(
				'The link has either expired or is totally invalid, please consider resending a new reset link.<br><a href="?action=lostpass" class="">Retry Sending</a>',
				'Invalid Token', 501
			);
		}
		initHtmlPage( 'Create A New Password', 'login.php?action=recover' );
		break;
	case 'lostpass':
		initHtmlPage( 'Find Your Account', 'login.php?action=lostpass' );
		break;
	default:
		initHtmlPage( 'Login', 'login.php?action=login' );
}
addPageCssFile( 'css/compact.css' );
require_once( __DIR__ . '/header.php' );
?>
<div class="col-sm-7 col-md-6 col-lg-5 col-xl-4 mb-3 mx-auto">
	<div id="logo"><a href="http://techify.ng/">Sevida</a></div>
	<form id="loginForm" method="post" action="#">
		<input type="hidden" name="action" value="<?=escHtml($action)?>" id="action" />
		<input type="hidden" name="client" value="web" />
		<div class="card bg-white text-dark mb-3">
			<?php
			switch( $action ) {
				case 'recover':
			?>
			<h2 class="card-header">Create A New Password</h2>
			<div class="card-body">
				<input type="hidden" name="userId" value="<?=$authToken->userId?>" />
				<input type="hidden" name="authCode" value="<?=$authToken->aut?>" />
				<p class="alert alert-info">Please use a strong password for security reason.</p>
				<label class="form-label" for="password">New Password</label>
				<div class="input-group input-group-lg mb-3">
					<span class="input-group-text"><?=icon('lock')?></span>
					<input class="form-control" type="text" id="password" name="password" minlength="8" maxlength="25" />
				</div>
				<button type="submit" id="submit" name="submit" class="btn btn-primary btn-lg mb-2"><?=icon('check me-1')?> Submit</button><br>
				<a href="login.php"><?=escHtml('← Back to login page')?></a> |
				<a href="?action=lostpass" class="mb-3">Resend code</a>
			</div>
			<?php
					break;
				/** Handle lost password */
				case 'lostpass':
			?>
			<h2 class="card-header">Find Your Account</h2>
			<div class="card-body">
				<p class="alert d-none"></p>
				<div id="request">
					<label class="form-label" for="userName">Username or Email</label>
					<div class="input-group input-group-lg mb-3">
						<span class="input-group-text"><?=icon('user')?></span>
						<input class="form-control" type="text" id="userName" name="userName" spellcheck="false" minlength="5" />
					</div>
					<div class="mb-3 form-check">
						<input class="form-check-input" id="notRobot" name="notRobot" type="checkbox" />
						<label class="form-check-label" for="notRobot">I am not a robot</label>
					</div>
					<button type="submit" id="submit" name="submit" class="btn btn-primary btn-lg mb-3"><?=icon('search me-1')?> Search</button>
				</div>
				<!--<span>Didn't receive?</span> <a href="?action=lostpass" class="mb-3">Resend code</a><br>-->
				<a href="login.php"><?=escHtml('← Back to login page')?></a>
			</div>
			<?php
					break;
				default:
			?>
			<h2 class="card-header">Login</h2>
			<div class="card-body">
				<p class="alert d-none text-center"></p>
				<div class="mb-3">
					<label class="form-label" for="userName">Username or Email</label>
					<div class="input-group input-group-lg">
						<span class="input-group-text"><?=icon('user')?></span>
						<input class="form-control" type="text" id="userName" name="userName" spellcheck="false" required />
					</div>
				</div>
				<div class="mb-3">
					<label class="form-label" for="password">Password</label>
					<div class="input-group input-group-lg">
						<span class="input-group-text"><?=icon('lock')?></span>
						<input class="form-control" type="password" id="password" name="password" required />
					</div>
				</div>
				<div class="form-check mb-3">
					<input class="form-check-input" name="remember" type="checkbox" id="remember" value="forever"/>
					<label class="form-check-label" for="remember">Remember Me</label>
				</div>
				<button type="submit" id="submit" name="submit" class="btn btn-primary btn-lg mb-3" aria-label="Submit Data"><?=icon('check me-1')?> Sign In</button><br>
				<a href="?action=lostpass" id="loastPass" class="mb-3">Lost your password?</a><br>
				<a href="<?=( BASEURI . '/' )?>"><?=escHtml('← Back to main site')?></a>
			</div>
			<?php
			}
			?>
		</div>
	</form>
</div>
<?php
addPageJsFile( 'js/async-form.js' );
function onPageJsCode() {
	global $action;
?>
document.addEventListener("DOMContentLoaded", function() {
	<?php
	if( $action === 'recover' ) {
	?>
	var asyncForm = AsyncForm(document.getElementById("loginForm"), { url: "../api/login.php", success: function() {
			window.location = "login.php";
			return true;
		}
	});
	<?php
	} elseif( $action === 'lostpass' ) {
	?>
	var asyncForm = AsyncForm(document.getElementById("loginForm"), {
		url: "../api/login.php", success: function(response) {
			this.formElem.querySelectorAll("input,button").forEach(function(element){
				element.disabled = true;
			});
			document.getElementById("request").className = "d-none";
			return false;
		}
	});
	<?php
	} else {
	?>
	var asyncForm = AsyncForm(document.getElementById("loginForm"), { url: "../api/login.php", success: function(response) {
			if(window.localStorage)
				localStorage.authToken = response.message.authToken;
			window.location = "index.php";
			return true;
		} 
	});
	document.getElementById("loastPass").addEventListener("click", function(){
		if(typeof window.sessionStorage !== "undefined")
			sessionStorage.userName = document.getElementById("userName").value;
	});
	<?php
	}
	?>
});
<?php
}
include( __DIR__ . '/footer.php' );
