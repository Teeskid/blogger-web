<?php
/**
 * User Login / Password Reset Page
 *
 * Registered users log in through this page with their username and password
 *
 * @package Sevida
 * @subpackage: Administration
 */
/**
 * Tells that it is a login page and so no need to redirect to it again
 *
 * @var bool
 */
define( 'SE_LOGIN', true );

/** Loads the blog configuration and database connection */
require( dirname(__FILE__) . '/Load.php' );

/** Loads some helper functions and widget makers */
require( ABSPATH . BASE_UTIL . '/UIUtil.php' );

/** If the user is logged in, then no need to display a login page. Hence redirect to home */
if( LOGGED_IN )
	redirect( getReturnUrl( 'index.php' ) );

$action = request( 'action' );
if( ! in_array( $action, [ 'login', 'lostpass', 'recover' ] ) )
	$action = 'login';

switch( $action ) {
	case 'recover':
		$payLoad = request( 'token' );
		try {
			$payLoad = \Firebase\JWT\JWT::decode( $payLoad, AUTH_SALT, [ 'HS256' ] );
			if( ! $payLoad )
				throw new Exception( 'Invalid / Expired Token' );
		} catch( Exception $e ) {
			showError( 'Authenticated Failed', 'The reset password has either expired or is totally invalid, please consider resending a new reset link.<br><a href="?action=lostpass" class="">Retry Sending</a>' );
		}
		break;
}
$_page = new Page( 'Login', '/user-cp/login.php?action=' . $action );
$_page->setMetaItem( Page::META_CSS_FILE, 'css/compact.css' );
include( 'html-header.php' );
?>
<div class="container-sm">
	<div id="logo"><a href="http://techify.ng/">Sevida</a></div>
	<form role="form" id="login" name="login" action="#">
		<input type="hidden" name="action" value="<?=htmlentities($action)?>" />
		<div class="panel panel-primary">
		<?php
switch( $action ) {
	case 'recover':
		?>
		<input type="hidden" name="userId" value="<?=$payLoad->uid?>" />
		<input type="hidden" name="authKey" value="<?=$payLoad->cod?>" />
			<div class="panel-heading">Create A New Password</div>
			<div class="panel-body">
				<div class="form-group form-group-lg has-feedback">
					<label class="control-label" for="password">New Password</label>
					<div class="input-group input-group-lg">
						<span class="input-group-addon"><?=icon('lock')?></span>
						<input class="form-control" type="password" id="password" name="password" required />
					</div>
					<?=icon('check form-control-feedback sr-only')?>
				</div>
				<p>
					<button type="submit" name="submit" class="btn btn-primary btn-lg btn-block">Change Password</button>
					<a href="login.php" class="btn btn-default btn-lg btn-block">Cancel</a>
				</p>
			</div>
<?php
		break;
	case 'lostpass':
?>
			<div class="panel-heading">Forgotten Password</div>
			<div class="panel-body">
				<div class="form-group form-group-lg has-feedback">
					<label class="control-label" for="userName">Username</label>
					<div class="input-group input-group-lg">
						<span class="input-group-addon"><?=icon('user')?></span>
						<input class="form-control" type="text" id="userName" name="userName" required />
					</div>
					<?=icon('check form-control-feedback sr-only')?>
				</div>
				<p class="alert alert-success hide"></p>
				<p>
					<button type="submit" name="submit" class="btn btn-primary btn-lg btn-block">Find Password</button><br>
					<a href="login.php">&larr; Back to Login</a>
				</p>
			</div>
<?php
		break;
	default:
?>
			<div class="panel-heading">Login</div>
			<div class="panel-body">
				<div class="alert hide"></div>
				<div class="form-group form-group-lg has-feedback">
					<label class="control-label" for="userName">Username</label>
					<div class="input-group input-group-lg">
						<span class="input-group-addon"><?=icon('user')?></span>
						<input class="form-control" type="text" id="userName" name="userName" required />
					</div>
					<?=icon('check form-control-feedback sr-only')?>
				</div>
				<div class="form-group form-group-lg has-feedback">
					<label class="control-label" for="password">Password</label>
					<div class="input-group input-group-lg">
						<span class="input-group-addon"><?=icon('lock')?></span>
						<input class="form-control" type="password" id="password" name="password" required />
					</div>
					<?=icon('check form-control-feedback sr-only')?>
				</div>
				<div class="checkbox"><label><input name="remember" type="checkbox" id="remember" value="forever"/> Remember Me</label></div>
				<p>
					<button type="submit" name="submit" class="btn btn-primary btn-lg btn-block">Login</button><br>
					<a href="?action=lostpass" id="loastPass">Lost your password?</a>
				</p>
			</div>
<?php
}
?>
		</div>
	</form>
</div>
<?php
// javascript functions and variables
$_page->setMetaItem( Page::META_JS_FILE, USERPATH . '/js/jquery.async-form.js' );
if( $action === 'login' ) {
	$_page->setMetaItem( Page::META_JS_CODE, <<<'EOS'
	$(document).ready(function() {
		$("form#login").asyncForm({url: "../api/login.php", target: "index.php"});
		$("#loastPass").click(function(event) {
			if(typeof window.sessionStorage !== "undefined")
				sessionStorage.userName = $("input#userName").val();
		});
	});
EOS
	);
} else if( $action === 'lostpass' ) {
	$_page->setMetaItem( Page::META_JS_CODE, <<<'EOS'
	$(document).ready(function() {
		if(typeof window.sessionStorage !== "undefined") {
			$("input#userName").val(sessionStorage.userName||"");
			//delete sessionStorage.userName;
		}
		$("form#login").asyncForm({
			url: "../api/login.php",
			success: function(response) {
				var html = $("<a>Reset Password</a>").attr("href", response.resLink);
				this.find("p.alert").html(html).removeClass("hide");
			}
		});
	});
EOS
	);
} else if( $action === 'recover' ) {
	$_page->setMetaItem( Page::META_JS_CODE, <<<'EOS'
	$(document).ready(function() {
		$("form#login").asyncForm({ url: "../api/login.php", target: "login.php" });
	});
EOS
	);
}
include( 'html-footer.php' );
