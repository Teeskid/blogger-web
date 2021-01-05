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
$copyright = sprintf( '© Copyright %1s %2s', $cfg->blogName, date( 'Y', $cfg->installed ) );
$copyright = htmlspecialchars($copyright);
?>
	</main>
</div>
<footer class="container-fluid bg-primary" style="padding-top:20px">
	<div class="container">
		<p><?=$copyright?></p>
	</div>
</footer>
<script src="<?=BASEPATH?>/js/ie-10-fix.js"></script>
<script src="<?=(SE_DEBUG?BASEPATH.'/js/jquery-3.5.1.min.js':'https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous')?>"></script>
<script src="<?=USERPATH?>/js/jquery.bootstrap.min.js"></script>
<?php
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
doFootJsTag();
?>
</script>
</body>
</html>