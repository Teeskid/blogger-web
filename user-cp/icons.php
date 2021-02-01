<?php
/**
 * Icon Cheesheet
 * @package Sevida
 * @subpackage Administration
 */
$icons = @file_get_contents('icons.json');
if( file_exists('icon-data.php') ) {
	require('icon-data.php');
} else {
    $cache = '<?php $icons=[';

	$icons = file_get_contents('../css/fa-all.min.css') . file_get_contents('../css/fa-brands.min.css');
	preg_match_all( '#\.fa-([a-z-]+).*?\{#', $icons, $icons );
    $icons = $icons[1];
    $icons = array_unique($icons);
    sort($icons);
    foreach ( $icons as $key => $value ) {
        $cache .= "'$value',";
    }

    $cache .= '];';
	$cache = file_put_contents( 'icon-data.php', $cache );
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <title>Font Awesome CheetSheet</title>
    <link media="all" rel="stylesheet" href="css/bootstrap.min.css">
    <link media="all" rel="stylesheet" href="css/bootstrap-theme.min.css" />
    <link media="all" rel="stylesheet" href="../css/fontawesome.min.css">
    <link media="all" rel="stylesheet" href="../css/fa-all.min.css">
    <link media="all" rel="stylesheet" href="../css/fa-solid.min.css">
    <link media="all" rel="stylesheet" href="../css/fa-brands.min.css">
</head>
<body>
<div class="container">
	<div class="page-header"><h1>Font Awesome Cheatsheet</h1></div>
	<div class="row text-center">
        <?php
        foreach( $icons as $entry ) {
            $entry = 'fa-' . $entry;
        ?>
		<div class="col-sm-4 col-md-3 col-lg-2">
			<span class="fas fa-3x <?=htmlentities($entry)?>"></span>
			<p><?=htmlspecialchars($entry)?></p>
		</div>
        <?php
        }
        ?>
	</div>
</div>
</body>
</html>