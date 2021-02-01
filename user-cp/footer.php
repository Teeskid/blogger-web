<?php
/**
 * HTML Page Footer File
 * @package Sevida
 * @subpackage Administration
 */
if( ! defined('ABSPATH') )
	die();
$copyright = sprintf( '© Copyright %1s %2s', $_cfg->blogName, date( 'Y', $_cfg->installed ) );
$copyright = escHtml($copyright);
?>
</main>
<?php
if( isset($_usr) ) {
?>
<footer class="bg-primary">
	<div class="container-fluid">
		<div class="text-light py-3"><?=$copyright?></div>
	</div>
</footer>
<?php
}
?>
<?php
/** Use local files in debug mode */
if( SE_DEBUG ) {
?>
<script src="<?=BASEURI . '/js/jquery-3.5.1.min.js'?>"></script>
<script src="<?=USERURI . '/js/bootstrap.bundle.min.js'?>"></script>
<?php
} else {
?>
<script src="<?=BASEURI?>/js/ie-10-fix.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-ygbV9kiqUc6oa4msXn9868pTtWMgiQaeYH7/t7LECLbyPA2x65Kgf80OJFdroafW" crossorigin="anonymous"></script>
<?php
}
?>
<script src="<?=USERURI . '/js/functions.js'?>"></script>
<?php
doPageJsFiles()
?>
<script>
(function($){
	$.ajaxSetup({
		type: "POST",
		cache: false,
		dataType: "json",
		error: function(xhr) {
			console.error(xhr.responseText);
		}
	})
})(jQuery);
<?php
// Additional javascript code embedded by the page
doPageJsCodes();
?>
</script>
</body>
</html>