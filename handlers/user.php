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
require( ABSPATH . BASE_UTIL . '/UIUtil.php' );

$user  = $db->prepare( 'SELECT * FROM Person WHERE userName=? LIMIT 1' );
$user->execute( [ $_VARS['value'] ] );
if( ! 0 === $user->rowCount() )
	redirect( BASEPATH . '/404.php' );

$user = $db->fetchClass( $user, 'User' );
$user->fullName = $user->fullName ?? $user->userName;
$user->permalink = Rewrite::userUri( $user->userName );

$_page = new Page( $user->fullName, $user->permalink );
include( ABSPATH . BASE_UTIL . '/HeadHtml.php' );
htmBreadCrumb( [ [ BASEPATH . '/', 'Home' ], 'Profile' ] );
?>
<div class="post">
	<div class="post-head">
		<h2><?=htmlspecialchars($user->fullName)?></h2>
	</div>
	<div id="postBar" class="btn-group">
<?php
if( isLoggedIn() ) {
	$return = rawurlencode($_page->path);
?>
		<a class="btn btn-sm" href="<?=USERPATH?>/page-edit.php?id=<?=$user->id?>&action=modify&redirect=<?=$return?>" target="_blank"><?=icon('edit')?> Edit</a>
		<a class="btn btn-sm" href="<?=USERPATH?>/page-edit.php?id=<?=$user->id?>&action=delete&redirect=<?=$return?>" target="_blank"><?=icon('trash')?> Delete</a>
<?php
}
?>
		<a class="btn btn-sm" href="#" onclick="window.print()"><?=icon('print')?> Print</a>
	</div>
</div>
<?php
include( ABSPATH . BASE_UTIL . '/TailHtml.php' );
