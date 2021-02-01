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
		$stmt = $_db->prepare($stmt);
		$stmt->execute(['id' => $user->id]);
		$stmt = sprintf(implode([
			'DELETE FROM %s WHERE id=:id LIMIT 1;',
			'DELETE FROM %s WHERE adminId=:id;'
		]), users, users_);
		$stmt = $_db->prepare($stmt);
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
	$user = $_db->quote((int) trim($_GET['id']));
	$user = $_db->query( 'SELECT id,userName FROM users WHERE id=$user' )->fetch();

	if(!$user) {
		not_found();
	}
}
$error = implode('<br/>', $error);

initHtmlPage( 'Delete User', 'user-cp-delete.php?id='.$user->id );
include_once( __DIR__ . '/header.php' );
?>
<nav aria-label="breadcrumb">
	<ol class="breadcrumb my-3">
		<li class="breadcrumb-item"><a href="index.php">Home</a></li>
		<li class="breadcrumb-item"><a href="users.php">Users</a></li>
		<li class="breadcrumb-item active" aria-current="page">Delete</li>
	</ol>
</nav>
<div class="card card-small">
	<div class="card-body">
		<span class="card-title">Confirm Action</span>
		<form class="form text-center" action="<?=$_SERVER['REQUEST_URI'];?>" method="post">
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
$HTML->readyjs = <<<JS
JS;
include_once( __DIR__ . '/footer.php' );
