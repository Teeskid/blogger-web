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

if(isset($_POST['submit'])) {
	$user = getPostData();
	$user->id = parseInt($user->id);
	try
	{
		$stmt = sprintf(implode([
			'UPDATE %s SET author=0 WHERE author=:id;',
			'UPDATE %s SET author=0 WHERE author=:id;',
			'UPDATE %s SET author=0 WHERE author=:id;'
		]), media, posts, replies);
		$stmt = $db->prepare($stmt);
		$stmt->execute(['id' => $user->id]);
		$stmt = sprintf(implode([
			'DELETE FROM %s WHERE id=:id LIMIT 1;',
			'DELETE FROM %s WHERE adminId=:id;'
		]), users, users_);
		$stmt = $db->prepare($stmt);
		$stmt->execute(['id' => $user->id]);

	}
	catch(Exception $e)
	{
		$error[] = $e->getMessage();
	}
	if(count($error) == 0){
		message('success', 'Deleted successfully.');
		header("location:$ref".getReturnUrl('user.php'));
		exit;
	}
}
else
{
	$user = $db->quote((int) trim($_GET['id']));
	$user = $db->query( 'SELECT id,userName FROM users WHERE id=$user' )->fetch();

	if(!$user) {
		not_found();
	}
}
$error = implode('<br/>', $error);

$_page = new Page( 'Delete User', '/user-cp/user-cp-delete.php?id='.$user->id );
include( 'html-header.php' );
?>
<nav>
	<div class="nav-wrapper breadcrumb-wrapper">
		<div class="col s12">
			<a href="index.php" class="breadcrumb">Home</a>
			<a href="user.php" class="breadcrumb">Users</a>
			<a href="#" class="breadcrumb active">Delete</a>
		</div>
	</div>
</nav>
<div class="card card-small">
	<div class="card-content">
		<span class="card-title">Confirm Action</span>
		<form class="form text-center" role="form" action="<?=$_SERVER['REQUEST_URI'];?>" method="post">
			<?=alert($error, 'error')?>
			<input type="hidden" name="id" value="<?=$user->id;?>" />
			<div class="card-panel red lighten-4">Are you sure you want to delete <?=$user->userName;?> ?</div>
			<div class="input-group">
				<a href="admin.php" class="btn-flat" role="button">Cancel</a>
				<button type="submit" class="btn red darken-1" name="submit">Confirm</button>
			</div>
		</form>
	</div>
</div>
<?php
$_page->readyjs = <<<JS
JS;
include( 'html-footer.php' );
