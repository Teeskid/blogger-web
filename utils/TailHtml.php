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
?>
			</div>
			<!--/col-main-->
<?php
include( ABSPATH . BASE_UTIL . '/AsideHtml.php' )
?>
		</div>
		<!--/grid-outer-->
	</main>
	<footer id="footer">
		<p class="copyright"><?=htmlspecialchars('Copyright © ' . date('Y'))?> <a href="<?=BASEPATH?>/"><?=$_blogName?> <?=$cfg->about?></a></p>
		<ul class="link-menu">
			<li><a href="<?=BASEPATH?>/privacy-policy/">Privacy Policy</a></li>
			<li><a href="<?=BASEPATH?>/about-us/">About Us</a></li>
			<li><a href="<?=BASEPATH?>/contact-us/">Contact Us</a></li>
			<li><a href="<?=BASEPATH?>/sitemap.xml">Site Map</a></li>
		</ul>
	</footer>
</div>
<!--/container-->
<button id="backTop" class="btn btn-fab"><?=icon('arrow-up fa-2x')?></button>
<div id="cookies" class="card">
	<h2>This website uses cookies</h2>
	<p>We use cookies to offer you the most relevant information and best experience on our website. By continuing to browse this site, you give consent for cookies to be used. For more details, please read our Privacy and Cookie Policies.</p>
	<div class="btn-group">
		<a href="<?=BASEPATH?>/privacy-policy/" class="btn">FIND OUT MORE</a>
		<button type="button" class="btn close">ACCEPT</button>
	</div>
	<button type="button" class="close" aria-label="Close"><?=htmlspecialchars('×')?></button>
</div>
<script src="<?=BASEPATH?>/js/jquery-3.5.1.min.js"></script>
<script src="<?=BASEPATH?>/js/jquery.owlcarousel.min.js"></script>
<script src="<?=BASEPATH?>/js/jquery.mousewheel.min.js"></script>
<script src="<?=BASEPATH?>/js/sevida.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function(){
	var cookieDiv = document.getElementById("cookies");
	if(!sessionStorage.acceptCookie) {
		cookieDiv.style.display = "block";
		cookieDiv.querySelectorAll("button.close").forEach(function(element){
			element.onclick = function() {
				sessionStorage.acceptCookie = true;
				cookieDiv.remove();
			};
		});
	} else {
		cookieDiv.remove();
	}
	FlexiMenu.call(document.getElementById("mainNav"));
	document.querySelectorAll("a.nav-btn[data-target]").forEach(function(element){
		MobPopup.call(element);
	});
	document.querySelectorAll("ul.nav-popup li.popup").forEach(function(element){
		DropDown.call(element);
	});
	document.querySelectorAll("div#feedView button").forEach(function(element){
		FeedButton.call(element);
	});
	document.querySelectorAll("ul#tabs li.tab a").forEach(function(element) {
		TabWidget.call(element);
	});
	document.getElementById("archive").onchange = function(e) {
		window.location = this.value;
	};
});
<?php
doFootJsTag()
?>
</script>
</body>
</html>
<?php
stopTimer();
