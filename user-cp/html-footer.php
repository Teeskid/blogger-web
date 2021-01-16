<?php
/**
 * HTML Page Footer File
 * 
 * This file must be included at the end of html file
 * This file knows what type of page it is being included in 
 * @package Sevida
 * @subpackage Administration
 */
/** Incase the page object was not instatiated */
if( ! ( isset($_page) && is_object($_page) ) ) {
	ob_clean();
	objectNotFound();
	exit;
}
$copyright = sprintf( '© Copyright %1s %2s', $cfg->blogName, date( 'Y', $cfg->installed ) );
$copyright = escHtml($copyright);
?>
</main>
<footer class="bg-primary">
	<div class="container-fluid">
		<div class="text-light py-3"><?=$copyright?></div>
	</div>
</footer>
<script src="<?=BASEPATH?>/js/ie-10-fix.js"></script>
<?php
/** Use local files in debug mode */
if( SE_DEBUG ) {
?>
<script src="<?=BASEPATH . '/js/jquery-3.5.1.min.js'?>"></script>
<script src="<?=USERPATH . '/js/bootstrap.bundle.min.js'?>"></script>
<?php
} else {
?>
<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-ygbV9kiqUc6oa4msXn9868pTtWMgiQaeYH7/t7LECLbyPA2x65Kgf80OJFdroafW" crossorigin="anonymous"></script>
<?php
}
// Additional javascript files embedded by the page
doFootJsInc()
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
	});
})(jQuery);
<?php
// Additional javascript code embedded by the page
doFootJsTag();
?>
</script>
</body>
</html>