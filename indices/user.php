<?php
/**
 * User Profiles Handler
 * @package Sevida
 * @subpackage Handlers
 */
if( ! defined('ABSPATH') )
	die();
require( ABSPATH . BASE_UTIL . '/HtmlUtil.php' );

$user  = $_db->prepare( 'SELECT * FROM Uzer WHERE userName=? LIMIT 1' );
$user->execute( [ $_GET['value'] ] );
if( ! 0 === $user->rowCount() )
	redirect( BASEURI . '/404.php' );

$user = $_db->fetchClass( $user, 'User' );
$user->fullName = $user->fullName ?? $user->userName;
$user->permalink = Rewrite::userUri( $user->userName );

initHtmlPage( $user->fullName, $user->permalink );
include( __DIR__ . '/header.php' );
BreadCrumb( [ [ BASEURI . '/', 'Home' ], 'Profile' ] );
?>
<div class="post">
	<div class="post-head">
		<h2><?=escHtml($user->fullName)?></h2>
	</div>
	<div id="postBar" class="btn-group">
<?php
if( isLoggedIn() ) {
	$return = rawurlencode($HTML->path);
?>
		<a class="btn btn-sm" href="<?=USERURI?>/page-edit.php?id=<?=$user->id?>&action=modify&redirect=<?=$return?>" target="_blank"><?=icon('edit')?> Edit</a>
		<a class="btn btn-sm" href="<?=USERURI?>/page-edit.php?id=<?=$user->id?>&action=delete&redirect=<?=$return?>" target="_blank"><?=icon('trash')?> Delete</a>
<?php
}
?>
		<a class="btn btn-sm" href="#" onclick="window.print()"><?=icon('print')?> Print</a>
	</div>
</div>
<?php
include( __DIR__ . '/footer.php' );
