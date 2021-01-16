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
require( ABSPATH . BASE_UTIL . '/HtmlUtil.php' );

$error = [];
if(isset($_POST['submit']))
{
	$admin = getPostData();
	$admin->role  = parseInt($admin->role);
	try
	{
		$stmt = $db->prepare( 'UPDATE users SET role=? WHERE id=?' );
		$stmt->execute([$admin->role, $admin->id]);
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

	$admin = $db->quote((int) $_GET['id']);
	$admin = $db->query( 'SELECT id,userName,role FROM users WHERE id=$admin LIMIT 1' );
	$admin = $db->fetchClass($admin, 'Admin');
	if(!$admin){
		header('location:'.getReturnUrl('404.php'));
		exit;
	}
}
$admin->role = parseInt($admin->role);

$error = implode($error);

$_page = new Page( 'User Profile', sprintf('/user-cp/user-cp-profile.php?id=%s', $admin->id) );
$_page->meta = <<<META
<script src="js/admin.js"></script>
<link media="all" rel="stylesheet" href="css/admin.css" />
META;

include( 'html-header.php' );
?>
<div class="card">
	<div class="card-body">
		<?=alert($error, 'error')?>
		<div class="card-title capitalize"><?=strtoupper($admin->userName)?></div>
		<ul class="collection">
			<li class="collection-item">Role <span class="badge">Author</span></li>
			<li class="collection-item">Role <span class="badge">Author</span></li>
			<li class="collection-item">Role <span class="badge">Author</span></li>
			<li class="collection-item">Role <span class="badge">Author</span></li>
			<li class="collection-item">Role <span class="badge">Author</span></li>
			<li class="collection-item">Role <span class="badge">Author</span></li>
			<li class="collection-item">Role <span class="badge">Author</span></li>
			<li class="collection-item">Role <span class="badge">Author</span></li>
		</ul>
	</div>
</div>
<?php
$_page->readyjs = <<<JS
$(document).ready(function(){
	$("select#role").formSelect();
});
JS;
include( 'html-footer.php' );
