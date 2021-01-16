<?php
/**
 * User Login and Password Reset Page
 *
 * @package Sevida
 * @subpackage: Administration
 */
/**
 * Tells the bootstrap file that this is a login page
 * @var bool
 */
define( 'SE_LOGIN', true );

// Loads the blog bootstrap file
require( dirname(__FILE__) . '/Load.php' );

/** If the user is logged in, then no need to display a login page. Hence redirect to home */
if( LOGGED_IN )
	redirect( getReturnUrl( 'index.php' ) );

/** @var string What part of the page we a accessing  */
$action = request( 'action' );
if( ! in_array( $action, [ 'login', 'lostpass', 'recover' ] ) )
	$action = 'login';

switch( $action ) {
	/** Handle the recover password request */
	case 'recover':
		/** @var object The token we sent to the user's email address */
		$login = request( 'token' );
		try {
			$login = \Firebase\JWT\JWT::decode( $login, AUTH_KEY, [ 'HS256' ] );
			if( ! $login )
				throw new Exception( 'Invalid Token' );
			// Tokens expire after five mintes [-300 = -60 x 5]
			if( ( $login->iat - time() ) < -300 )
				throw new Exception( 'Expired Token' );
		} catch( Exception $e ) {
			// An error occured, we can not continue with the operation
			showError( 'Authenticated Failed', 'The reset password has either expired or is totally invalid, please consider resending a new reset link.<br><a href="?action=lostpass" class="">Retry Sending</a>' );
		}
		/** Escape html tags */
		$login->uid = escHtml($login->uid);
		$login->aut = escHtml($login->aut);
		break;
}
require( ABSPATH . BASE_UTIL . '/HtmlUtil.php' );

$_page = new Page( 'Login', USERPATH . '/login.php?action=' . $action );
$_page->addPageMeta( Page::META_CSS_FILE, 'css/compact.css' );
require( 'html-header.php' );
?>
<div class="col-sm-7 col-md-6 col-lg-5 col-xl-4 mb-3 mx-auto">
	<div id="logo"><a href="http://techify.ng/">Sevida</a></div>
	<form id="loginForm" class="needs-validation" novalidate>
		<input type="hidden" name="action" value="<?=escHtml($action)?>" />
		<div class="card bg-white text-dark mb-3">
			<?php
			switch( $action ) {
				/** User login page */
				case 'recover':
			?>
			<input type="hidden" name="userId" value="<?=$login->uid?>" />
			<input type="hidden" name="authKey" value="<?=$login->aut?>" />
			<div class="card-body">
				<h2 class="card-heading">Create A New Password</h2>
				<div class="mb-3">
					<label class="form-label" for="password">New Password</label>
					<div class="input-group input-group-lg">
						<span class="input-group-text"><?=icon('lock')?></span>
						<input class="form-control" type="text" id="password" name="password" spellcheck="false" required />
					</div>
				</div>
				<button type="submit" name="submit" class="btn btn-primary btn-lg mb-2">Change Password</button><br>
				<a href="login.php" class="btn btn-danger btn-lg mb-2">Cancel</a>
			</div>
			<?php
				break;
			/** Handle lost password */
			case 'lostpass':
				?>
			<div class="card-body">
				<h2 class="card-title">Forgotten Password</h2>
				<div class="alert alert-danger d-none"></div>
				<div class="mb-3">
					<label class="form-label" for="userName">Username or Email</label>
					<div class="input-group input-group-lg">
						<span class="input-group-text"><?=icon('user')?></span>
						<input class="form-control" type="text" id="userName" name="userName" spellcheck="false" required />
					</div>
				</div>
				<div class="mb-2">
					<button type="submit" name="submit" class="btn btn-primary btn-lg mb-2">Find Password</button><br>
					<a href="login.php">&larr; Back to Login</a>
				</div>
			</div>
			<?php
				break;
			default:
			?>
			<div class="card-body">
				<h2 class="card-title">Login</h2>
				<div class="alert alert-danger d-none"></div>
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
				<button type="submit" name="submit" class="btn btn-primary btn-lg mb-2" aria-label="Submit Data">Login</button><br>
				<a href="?action=lostpass" id="loastPass" class="mb-1">Lost your password?</a>
			</div>
			<?php
			}
			?>
		</div>
	</form>
</div>
<?php
// javascript functions and variables
$_page->addPageMeta( Page::META_JS_FILE, USERPATH . '/js/async-form.js' );
if( $action === 'recover' ) {
	$_page->addPageMeta( Page::META_JS_CODE, <<<'EOS'
	document.addEventListener("DOMContentLoaded", function() {
		SeForm.call(
			document.getElementById("loginForm"), {
				url: "../api/login.php", success: function(message) {

				}
			}
		);
		SeForm.call(document.getElementById("loginForm"), { url: "../api/login.php", target: "login.php" } );
	});
EOS
	);
} elseif( $action === 'lostpass' ) {
	$_page->addPageMeta( Page::META_JS_CODE, <<<'EOS'
	document.addEventListener("DOMContentLoaded", function() {
		var loginForm = document.getElementById("loginForm");
		if(typeof window.sessionStorage !== "undefined") {
			document.getElementById("userName").value = (sessionStorage.userName || "");
			delete sessionStorage.userName;
		}
		SeForm.call(loginForm, { url: "../api/login.php", success: function(message) {
			var alertDiv = loginForm.querySelector("div.alert");
			alertDiv.innerHTML = message;
			alertDiv.classList.remove("d-none");
		}});
	});
EOS
	);
} elseif( $action === 'login' ) {
	$_page->addPageMeta( Page::META_JS_CODE, <<<'EOS'
	document.addEventListener("DOMContentLoaded", function() {
		SeForm.call(document.getElementById("loginForm"), { url: "../api/login.php", target: "login.php" } );
		document.getElementById("loastPass").addEventListener("click", function(){
			if(typeof window.sessionStorage !== "undefined")
				sessionStorage.userName = document.getElementById("userName").value;
		});
	});
EOS
	);
}
include( 'html-footer.php' );
