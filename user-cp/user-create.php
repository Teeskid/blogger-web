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
require( dirname(__FILE__) . '/Load.php' );
require( ABSPATH . BASE_UTIL . '/UIUtil.php' );

$error = [];

if( isset($_POST['submit']) ) {
	$user = getPostData();
	$user->uri = uri_prepare($user->userName);
	$user->role  = parseInt($user->role);
	$user->state = checked(isset($user->state));
	/*
	$user->password = preg_split('//', $user->password);
	$user->password = array_filter('');
	foreach($user->password AS &$c) {
		if($c) {
			$c = strpos($salt, $c);
		}
	}
	die(json_encode($user->password));
	*/
	try
	{
		if( strlen($user->userName) < 5 ) {
			throw new Exception('userName too short');
		}
		if( !preg_match('/^[a-z0-9-.]+\@[a-z]{3,10}\.[a-z]{2,5}$/i', $user->email) ) {
			throw new Exception('insert a valid email');
		}
		if( strlen($user->password) < 8 ) {
			throw new Exception('password too short');
		}
		if( !preg_match('/^[a-z0-9-.]{8,20}$/i', $user->password) ) {
			throw new Exception('password must contain letters and numbers');
		}
		$user->password = md5($user->password);
		$stmt = sprintf('INSERT INTO %s (userName,uri,email,role,state,password) VALUES (?,?,?,?,?,?)', users);
		$stmt = $db->prepare($stmt);
		$stmt->execute([
			$user->userName,
			$user->uri,
			$user->email,
			$user->role,
			$user->state,
			$user->password
		]);
	}
	catch(Exception $e)
	{
		$error[] = $e->getMessage();
	}
	if(count($error) == 0) {
		message('success', 'Created successfully.');
		header('location:'.getReturnUrl('user.php'));
		exit;
	}
}
else
{
	$user = new Class(){
		var $id, $userName, $email, $state;
	};
}
$user->state = checked($user->state);

$error = implode($error);
$_page = new Page( 'Add User', "/user-cp/user-cp-create.php" );
$_page->meta = <<<META
<link media="all" rel="stylesheet" href="css/admin.css" />
META;
include( 'html-header.php' );
?>
<div class="card">
	<div class="card-content">
		<blockquote class="alert"><?=$error?></blockquote>
		<form id="post" class="form" role="form" action="<?=$_SERVER['REQUEST_URI']?>" method="post">
			<input type="hidden" name="id" value="<?=$user->id?>" />
			<input type="hidden" name="role" value="1" />
			<div class="input-field">
				<input class="validate" type="text" name="userName" id="userName" value="<?=$user->userName?>" />
				<label for="userName">Username</label>
			</div>
			<div class="input-field">
				<input class="validate" type="email" name="email" id="email" value="<?=$user->email?>" />
				<label for="email">Email</label>
			</div>
			<div class="input-field hide">
				<select name="role" id="role">
					<option disabled selected>SELECT ROLE</option>
					<option value="4">AN AUTHOR</option>
					<option value="5">AN ADMINISTRATOR</option>
				</select>
			</div>
			<div class="input-field">
				<input class="validate" type="text" name="password" id="password" value="" />
				<label for="password">Password</label>
			</div>
			<div class="input-field center">
				<button type="submit" class="btn" name="submit">Submit</button>
				<a href="user.php" class="btn-flat" role="button">Cancel</a>
			</div>
		</form>
	</div>
</div>
<?php
$_page->readyjs = <<<JS
$(document).ready(function(){
	//$("select#role").formSelect();
});
JS;
include( 'html-footer.php' );
