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
if(isset($_POST['submit']))
{
	$user = getPostData();
	$user->role  = parseInt($user->role);
	$user->state = checked(isset($user->state));
	$user->password = empty($user->password) ? $user->_password : md5($user->password);
	prepare_update($user, ['userName', 'email','password'], $holders, $values);
	try
	{
		$stmt = $db->quote((int) $user->id);
		$stmt = $db->prepare( 'UPDATE users SET $holders WHERE id=$stmt' );
		$stmt->execute($values);
	}
	catch(Exception $e)
	{
		$error[] = $e->getMessage();
	}
	if(count($error) == 0){
		message('success', 'Updated successfully.');
		header('location:'.getReturnUrl('user.php'));
		exit;
	}
}
else
{

	$user = $db->quote((int) $_GET['id']);
	$user = $db->query( 'SELECT id,userName,email,password,state FROM users WHERE id=$user LIMIT 1' )->fetch();
	if(!$user){
		header('location:'.getReturnUrl('404.php'));
		exit;
	}
}
$user->state = checked($user->state);

$error = implode($error);

$_page = new Page( 'Edit User', "/user-cp/user-cp-edit.php?id=$user->id" );
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
	$("#userName").change(function(e){

	});
	$("select#displayName").formSelect();
});
JS;
include( 'html-footer.php' );
