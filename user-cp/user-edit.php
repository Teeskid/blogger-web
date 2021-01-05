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

$person = $db->prepare( 'SELECT a.id, a.userName, a.picture, a.fullName, a.email, a.role, a.status, GROUP_CONCAT(?, b.metaValue) FROM Person a LEFT JOIN PersonMeta b ON b.personId=a.id WHERE a.id=? GROUP BY a.id LIMIT 1' );
$user->execute( [ '|', request( 'id' ) ] );
$person = $db->fetchClass( $person, 'User' );
if( ! $person )
	redirect( BASEPATH . '/404.php' );
$user->firstName = '';
$user->lastName = '';
$user->nickName = '';
$user->bioInfo = '';
$user->phoneNo = '';
$user->fbUserName = '';
$user->twUserName = '';

if( $user->picture ) {
	$picture = $db->prepare( 'SELECT metaValue FROM PostMeta WHERE postId=? AND metaKey=? LIMIT 1' );
	$picture->execute( [ $user->id, 'media_metadata' ] );
	$picture = $picture->fetchColumn();
	$picture = json_decode($picture);
	$picture = Media::getImage( $picture, 'medium' );
} else {
	$picture = Media::getAvatar( 'medium' );
}
$_page = new Page( 'My Profile', '/user-cp/profile.php?id=' . $user->id );
$_page->setMetaItem( Page::META_CSS_FILE, 'css/compact.css' );
include( 'html-header.php' );
?>
<ol class="breadcrumb">
	<li><a href="index.php">Home</a></li>
	<li class="active">My Profile</li>
</ol>
<div class="page-header">
	<h2>Profile <small><a href="#" class="label label-primary">Edit</a></small></h2>
</div>
<div class="container">
	<div class="container-sm">
		<div class="panel panel-primary">
			<div class="panel-heading">Edit Profile</div>
			<ul role="tablist" class="nav nav-tabs">
				<li role="presentation" class="active"><a href="#names" role="tab" data-toggle="tab" aria-controls="names">Names</a></li>
				<li role="presentation"><a href="#contact" role="tab" data-toggle="tab" aria-controls="contact">Contact</a></li>
				<li role="presentation"><a href="#about" role="tab" data-toggle="tab" aria-controls="about">About</a></li>
				<li role="presentation"><a href="#account" role="tab" data-toggle="tab" aria-controls="account">Account Settings</a></li>
			</ul>
			<div class="panel-body">
				<form role="form" id="profile" class="form-horizontal" action="#">
					<input type="hidden" name="picture" value="" />
					<br>
					<div class="tab-content">
						<div role="tabpanel" id="names" class="tab-pane active fade in">
							<div class="form-group">
								<label for="userName" class="col-sm-4 control-label">Username</label>
								<div class="col-sm-8">
									<input class="form-control" id="userName" type="text" value="<?=$user->userName?>" readonly />
									<p class="help-block">Usernames connot be changed</p>
								</div>
							</div>
							<div class="form-group">
								<label for="firstName" class="col-sm-4 control-label">First Name</label>
								<div class="col-sm-8">
									<input class="form-control" id="firstName" name="firstName" type="text" value="<?=$user->firstName?>" />
								</div>
							</div>
							<div class="form-group">
								<label for="firstName" class="col-sm-4 control-label">Last Name</label>
								<div class="col-sm-8">
									<input class="form-control" id="lastName" name="lastName" type="text" value="<?=$user->lastName?>" />
								</div>
							</div>
							<div class="form-group">
								<label for="firstName" class="col-sm-4 control-label">Nick Name</label>
								<div class="col-sm-8">
									<input class="form-control" id="lastName" name="lastName" type="text" value="<?=$user->lastName?>" />
								</div>
							</div>
						</div>
						<div role="tabpanel" id="contact" class="tab-pane fade">
							<div class="form-group">
								<label for="email" class="col-sm-4 control-label">Email (required)</label>
								<div class="col-sm-8">
									<input class="form-control" id="email" name="email" type="email" value="<?=$user->email?>" />
									<p class="help-block">Email must be validated and verified</p>
								</div>
							</div>
							<div class="form-group">
								<label for="phoneNo" class="col-sm-4 control-label">Phone / WhatsApp</label>
								<div class="col-sm-8">
									<div class="input-group">
										<?=icon('whatsapp','','input-group-addon')?>
										<input class="form-control" id="phoneNo" name="phoneNo" type="text" value="<?=$user->phoneNo?>" />
									</div>
								</div>
							</div>
							<div class="form-group">
								<label for="fbUserName" class="col-sm-4 control-label">Facebook Username</label>
								<div class="col-sm-8">
									<div class="input-group">
										<?=icon('facebook','','input-group-addon')?>
										<input class="form-control" id="fbUserName" name="fbUserName" type="text" value="<?=$user->fbUserName?>" />
									</div>
								</div>
							</div>
							<div class="form-group">
								<label for="twUserName" class="col-sm-4 control-label">Twitter Handle</label>
								<div class="col-sm-8">
									<div class="input-group">
										<?=icon('twitter','','input-group-addon')?>
										<input class="form-control" id="twUserName" name="twUserName" type="text" value="<?=$user->twUserName?>" />
									</div>
								</div>
							</div>
						</div>
						<div role="tabpanel" id="about" class="tab-pane fade">
							<div class="form-group">
								<label for="bioInfo" class="col-sm-4 control-label">Biological Info</label>
								<div class="col-sm-8">
									<textarea class="form-control" id="bioInfo" name="bioInfo" rows="5"><?=$user->bioInfo?></textarea>
								</div>
							</div>
							<div class="form-group">
								<label for="" class="col-sm-4 control-label">Profile Picture</label>
								<div class="col-sm-8 form-group">
									<button type="button" class="form-control btn btn-primary btn-block">Change</button>
								</div>
							</div>
						</div>
						<div role="tabpanel" id="account" class="tab-pane fade">
							<div class="form-group">
								<label for="password" class="col-sm-4 control-label">Password</label>
								<div class="col-sm-8 input-group">
									<?=icon('lock','','input-group-addon')?>
									<input class="form-control" id="password" name="password" type="text" value="<?=$user->twUserName?>" />
								</div>
							</div>
							<div class="col-xs-12 col-sm-6">
								<p class="checkbox">
									<label>
										<input type="checkbox" id="chk-pass" autocomplete="off" />
										<span>Change Password</span>
									</label>
								</p>
								<div id="pass-out" class="form-group" style="display:none">
									<label class="control-label" for="password">New Password</label>
									<input class="form-control" id="password" name="password" type="text" value="" disabled />
									<br>
									<label class="control-label" for="password2">Verify Password</label>
									<input class="form-control" id="password2" name="password2" type="text" value="" disabled />

								</div>
							</div>
						</div>
					</div>
					<div class="form-group"><button type="submit" class="btn btn-primary" name="submit">Save Changes</button></div>
				</form>
			</div>
		</div>
	</div>
</div>
<?php
$_page->setMetaItem( Page::META_JS_CODE, <<<'EOS'
$(document).ready(function(){

});
EOS
);
include( 'html-footer.php' );
