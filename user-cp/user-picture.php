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
require( ABSPATH . USER_UTIL . '/media.php' );

define('REQUEST_ID', (int) trim($_GET['id']));
define('REQUIRED_SIZES', ['xs','s']);

$error = [];

if(isset($_POST['submit']))
{
	$user = getPostData();
	$media = $_FILES['picture'];
	$media['name'] = media_rename($media['name'], md5($user->id.time()));
	if(upload_image($media, $error, REQUIRED_SIZES, true)){
		delete_media(parseInt($user->_media_id));
		try
		{
			$stmt = $db->prepare(<<<EOS
			DELETE FROM users_ WHERE adminId=? AND metaKey='_media_id';
			INSERT INTO Person_ (adminId,metaKey,val) VALUES (?,'_media_id',?);
EOS
);
			$stmt->execute([$user->id, $user->id, $media->id]);
		}
		catch(Exception $e)
		{
			$error[] = $e->getMessage();
		}
		if(count($error) == 0){
			message('success', 'Uploaded successfully.');
			header('location:'.getReturnUrl('user.php'));
			exit;
		}
	}
}
else
{

	$user = $db->quote(REQUEST_ID);
	$user = $db->query( 'SELECT a.id, b.val AS _media_id FROM users a LEFT JOIN users_ b ON a.id=b.adminId AND b.prop='_media_id' WHERE a.id=$user;' )->fetch();
	if(!$user){
		header('location:'.getReturnUrl('404.php'));
		exit;
	}
	$user->_media_id = $db->quote($user->_media_id);
	$user->_media_img = $db->query( 'SELECT metaValue FROM media_ WHERE id=$user->_media_id AND metaKey='drawables'' )->fetchColumn();
	$user->_media_img = json_decode($user->_media_img);
	$user->_media_img = $user->_media_img->s;
}
$error = implode($error);
$_page = new Page( 'Profile Picture', "/user-cp/user-cp-picture.php?id=$user->id" );
include( 'html-header.php' );
?>
<div class="card card-small">
	<div class="card-body">
		<div class="card-title">Choose Picture</div>
		<div class="alert alert-danger text-center"><?=$error;?></div>
		<form id="post" class="form" action="<?=$_SERVER['REQUEST_URI'];?>" method="post" enctype="multipart/form-data">
			<input type="hidden" name="id" value="<?=$user->id?>" />
			<input type="hidden" name="_media_id" value="<?=$user->_media_id?>" />
			<div class="card-panel"><img src="../storage/upload/<?=$user->_media_img?>" class="responsive-img" /></div>
			<div class="input-field file-field">
				<div class="btn">
					<span>Browse</span>
					<input type="file" name="picture" id="picture" accept='image/*' />
				</div>
				<div class="file-path-wrapper">
					<input class="file-path" type="text" placeholder="Upload Featured Image" />
				</div>
			</div>
			<div class="mb-3 text-center">
				<a href="user.php" class="btn-flat" role="button">Cancel</a>
				<button type="submit" class="btn" name="submit">Update</button>
			</div>
		</form>
	</div>
</div>

<?php
$_page->readyjs = <<<JS
JS;
include( 'html-footer.php' );
