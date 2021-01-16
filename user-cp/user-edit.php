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

$person = $db->prepare( 'SELECT a.id, a.userName, a.picture, a.fullName, a.email, a.role, a.status, GROUP_CONCAT(?, b.metaValue) FROM Person a LEFT JOIN PersonMeta b ON b.userId=a.id WHERE a.id=? GROUP BY a.id LIMIT 1' );
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
$_page = new Page( 'My Profile', USERPATH . '/profile.php?id=' . $user->id );
$_page->addPageMeta( Page::META_CSS_FILE, 'css/compact.css' );
include( 'html-header.php' );
?>
<nav aria-label="breadcrumb">
	<ol class="breadcrumb">
		<li class="breadcrumb-item"><a href="index.php">Home</a></li>
		<li class="breadcrumb-item active" aria-current="page">My Profile</li>
	</ol>
</nav>
<div class="page-header">
	<h2>Profile <small><a href="#" class="label label-primary">Edit</a></small></h2>
</div>
<div class="container">
	<div class="container-sm">
		<div class="card bg-light text-dark">
			<div class="card-header">Edit Profile</div>
			<ul role="tablist" class="nav nav-tabs">
				<li role="presentation" class="active"><a href="#names" role="tab" data-bs-toggle="tab" aria-controls="names">Names</a></li>
				<li role="presentation"><a href="#contact" role="tab" data-bs-toggle="tab" aria-controls="contact">Contact</a></li>
				<li role="presentation"><a href="#about" role="tab" data-bs-toggle="tab" aria-controls="about">About</a></li>
				<li role="presentation"><a href="#account" role="tab" data-bs-toggle="tab" aria-controls="account">Account Settings</a></li>
			</ul>
			<div class="card-body">
				<form id="profile" class="form-horizontal">
					<input type="hidden" name="picture" value="" />
					<br>
					<div class="tab-content">
						<div role="tabpanel" id="names" class="tab-pane active fade in">
							<div class="mb-3">
								<label for="userName" class="col-sm-4 form-label">Username</label>
								<div class="col-sm-8">
									<input class="form-control" id="userName" type="text" value="<?=$user->userName?>" readonly />
									<div class="form-text">Usernames connot be changed</div>
								</div>
							</div>
							<div class="mb-3">
								<label for="firstName" class="col-sm-4 form-label">First Name</label>
								<div class="col-sm-8">
									<input class="form-control" id="firstName" name="firstName" type="text" value="<?=$user->firstName?>" />
								</div>
							</div>
							<div class="mb-3">
								<label for="firstName" class="col-sm-4 form-label">Last Name</label>
								<div class="col-sm-8">
									<input class="form-control" id="lastName" name="lastName" type="text" value="<?=$user->lastName?>" />
								</div>
							</div>
							<div class="mb-3">
								<label for="firstName" class="col-sm-4 form-label">Nick Name</label>
								<div class="col-sm-8">
									<input class="form-control" id="lastName" name="lastName" type="text" value="<?=$user->lastName?>" />
								</div>
							</div>
						</div>
						<div role="tabpanel" id="contact" class="tab-pane fade">
							<div class="mb-3">
								<label for="email" class="col-sm-4 form-label">Email (required)</label>
								<div class="col-sm-8">
									<input class="form-control" id="email" name="email" type="email" value="<?=$user->email?>" />
									<div class="form-text">Email must be validated and verified</div>
								</div>
							</div>
							<div class="mb-3">
								<label for="phoneNo" class="col-sm-4 form-label">Phone / WhatsApp</label>
								<div class="col-sm-8">
									<div class="input-group">
										<div class="input-group-text"><?=icon('whatsapp')?></div>
										<input class="form-control" id="phoneNo" name="phoneNo" type="text" value="<?=$user->phoneNo?>" />
									</div>
								</div>
							</div>
							<div class="mb-3">
								<label for="fbUserName" class="col-sm-4 form-label">Facebook Username</label>
								<div class="col-sm-8">
									<div class="input-group">
										<div class="input-group-text"><?=icon('facebook')?></div>
										<input class="form-control" id="fbUserName" name="fbUserName" type="text" value="<?=$user->fbUserName?>" />
									</div>
								</div>
							</div>
							<div class="mb-3">
								<label for="twUserName" class="col-sm-4 form-label">Twitter Handle</label>
								<div class="col-sm-8">
									<div class="input-group">
										<div class="input-group-text"><?=icon('twitter')?></div>
										<input class="form-control" id="twUserName" name="twUserName" type="text" value="<?=$user->twUserName?>" />
									</div>
								</div>
							</div>
						</div>
						<div role="tabpanel" id="about" class="tab-pane fade">
							<div class="mb-3">
								<label for="bioInfo" class="col-sm-4 form-label">Biological Info</label>
								<div class="col-sm-8">
									<textarea class="form-control" id="bioInfo" name="bioInfo" rows="5"><?=$user->bioInfo?></textarea>
								</div>
							</div>
							<div class="mb-3">
								<label for="" class="col-sm-4 form-label">Profile Picture</label>
								<div class="col-sm-8 mb-3">
									<button type="button" class="form-control btn btn-primary btn-block">Change</button>
								</div>
							</div>
						</div>
						<div role="tabpanel" id="account" class="tab-pane fade">
							<div class="mb-3">
								<label for="password" class="col-sm-4 form-label">Password</label>
								<div class="col-sm-8 input-group">
									<div class="input-group-text"><?=icon('lock')?></div>
									<input class="form-control" id="password" name="password" type="text" value="<?=$user->twUserName?>" />
								</div>
							</div>
							<div class="col-xs-12 col-sm-6">
								<p class="form-check">
									<label>
										<input type="checkbox" id="chk-pass" autocomplete="off" />
										<span>Change Password</span>
									</label>
								</p>
								<div id="pass-out" class="mb-3" style="display:none">
									<label class="form-label" for="password">New Password</label>
									<input class="form-control" id="password" name="password" type="text" value="" disabled />
									<br>
									<label class="form-label" for="password2">Verify Password</label>
									<input class="form-control" id="password2" name="password2" type="text" value="" disabled />

								</div>
							</div>
						</div>
					</div>
					<div class="mb-3"><button type="submit" class="btn btn-primary" name="submit">Save Changes</button></div>
				</form>
			</div>
		</div>
	</div>
</div>
<?php
$_page->addPageMeta( Page::META_JS_CODE, <<<'EOS'
$(document).ready(function(){

});
EOS
);
include( 'html-footer.php' );
