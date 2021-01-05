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

define('ACTION_ID', (int) trim($_GET['id']));

$error = [];
if(isset($_POST['submit']))
{
	$page = getPostData();
	try
	{
		$db->prepare( 'DELETE FROM pages WHERE id=?' ->execute([$page->id]);
		$db->prepare( 'DELETE FROM $db->pages_ WHERE id=?' )->execute([$page->id]);
	}
	catch(Exception $e)
	{
		$error[] = $e->getMessage();
	}
	if(count($error) === 0)
	{
		header('location:'.getReturnUrl('page.php'));
		exit;
	}

}
else
{
	$page = $db->quote(ACTION_ID);
	$page = $db->query( 'SELECT id,title FROM pages WHERE id=$page' )->fetch();
	if(!$page) {
		not_found();
	}
}
$error = array_values($error);
$error = implode('<br/>', $error);
$_page = new Page( 'Delete Post', '/user-cp/page-edit.php?id='.$page->id );
include_once( ABSPATH . USER_UTIL . '/htm-sidenav.php' );
require ABSCPATH._INC_.'HeadHtml.php'?>
<nav>
<div class="nav-wrapper breadcrumb-wrapper">
<div class="col s12">
<a href="index.php" class="breadcrumb">Home</a>
<a href="page.php" class="breadcrumb">Pages</a>
<a href="#" class="breadcrumb active">Delete</a>
</div>
</div>
</nav>
<div class="card">
	<div class="card-content">
		<div class="alert alert-danger"><?=$error;?></div>
		<form class="form text-center" role="form" action="<?=$_SERVER['REQUEST_URI'];?>" method="post">
			<input type="hidden" name="id" value="<?=$page->id;?>" />
			<p>Confirm your action: Delete <?=$page->title;?></p>
			<div class="form-group center-block">
				<button type="submit" class="btn red" name="submit">Delete</button>
				<a href="page.php" class="btn-flat" role="button">Cancel</a>
			</div>
		</form>
	</div>
</div>
<?php
$_page->readyjs = <<<JS
JS;
include( 'html-footer.php' );
