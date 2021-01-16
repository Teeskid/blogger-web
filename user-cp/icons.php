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
$iconPack = @file_get_contents('icons.json');
if( $iconPack ) {
	$iconPack = json_decode( $iconPack, true );
} else {
	$iconPack = file_get_contents('../css/fa-all.min.css');
	$iconNums = preg_match_all( '#\.fa-([a-z-]+).*?\{#', $iconPack, $iconPack );
	$iconPack = $iconPack[1];
	sort($iconPack);
	file_put_contents( 'icons.json', json_encode($iconPack) );
}
?><!DOCTYPE html>
<html lang="en">
<head>
<title>Font Awesome</title>
<link media="all" rel="stylesheet" href="css/bootstrap.min.css">
<link media="all" rel="stylesheet" href="css/bootstrap-theme.min.css" />
<link media="all" rel="stylesheet" href="../css/fontawesome.min.css">
<link media="all" rel="stylesheet" href="../css/fa-all.min.css">
<link media="all" rel="stylesheet" href="../css/fa-solid.min.css">
<style type="text/css">

</style>
</head>
<body>
<div class="container">
	<div class="page-header"><h1>Font Awesome Cheatsheet</h1></div>
	<div class="row text-center">
<?php
foreach( $iconPack as $entry ) {
	$entry = 'fa-' . $entry;
?>
		<div class="col-xs-4 col-sm-3 col-md-2">
			<span class="fas fa-3x <?=escHtml($entry)?>"></span>
			<p><?=escHtml($entry)?></p>
		</div>
<?php
}
?>
	</ul>
</div>
<script></script>
</body>
</html>