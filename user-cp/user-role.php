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

require( __DIR__ . '/Load.php' );
require( ABSPATH . BASE_UTIL . '/HtmlUtil.php' );

$error = [];

if( isset($_POST['submit']) ) {
	$user = getPostData();
	$user->role  = parseInt($user->role);
	try
	{
		$stmt = $_db->prepare( 'UPDATE users SET role=? WHERE id=?' );
		$stmt->execute([$user->role, $user->id]);
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
} else {
	$user = parseInt($_GET['id']);
	$user = $_db->quote($user);
	$user = sprintf('SELECT id,userName,role FROM %s WHERE id=%s LIMIT 1', users, $user);
	$user = $_db->fetchObject($user);
	if( !$user ) {
		header('location:'.getReturnUrl('404.php'));
		exit;
	}
}
$user->role = parseInt($user->role);

$error = implode($error);

initHtmlPage( 'Edit Role', "/user-cp/admin-role.php?id=$user->id" );
$HTML->meta = <<<'EOS'
<script src="js/admin.js"></script>
<link media="all" rel="stylesheet" href="css/admin.css" />
EOS;
include_once( __DIR__ . '/header.php' );
?>
<div class="card">
	<div class="card-body">
		<div class="card-title">Change <?=$user->userName?>'s role to:</div>
		<form id="post" class="form" action="<?=$_SERVER['REQUEST_URI']?>" method="post">
			<?=alert($error, 'error')?>
			<input type="hidden" name="id" value="<?=$user->id?>" />
			<div class="input-field">
				<select name="role" id="role">
					<option disabled>SELECT ROLE</option>
					<option value="<?=ADMIN_LEVEL_AUTHOR?>"<?=($user->role==ADMIN_LEVEL_GLOBAL?' selected':'')?>>An Author</option>
					<option value="<?=ADMIN_LEVEL_GLOBAL?>"<?=($user->role==ADMIN_LEVEL_GLOBAL?' selected':'')?>>A Global Admin</option>
				</select>
			</div>
			<div class="mb-3 text-center">
				<a href="admin.php" class="btn-flat" role="button">Cancel</a>
				<button type="submit" class="btn" name="submit">UPDATE</button>
			</div>
		</form>
	</div>
</div>
<?php
$HTML->readyjs = <<<JS
$(document).ready(function(){
	$("select#role").formSelect();
});
JS;
include_once( __DIR__ . '/footer.php' );
