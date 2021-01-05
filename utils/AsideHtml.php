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
<aside class="col-aside">
	<div class="heading"><h2><a href="#">ADVERTISEMENT</a></h2></div>
<?php
if( ! LOGGED_IN && ! SE_DEBUG ) {?>
	<div class="advert advert-aside">
		<!-- RESPONSIVE -->
		<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
		<ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-6077742528829558" data-ad-slot="9422817584" data-ad-format="auto"></ins>
		<script>
		(adsbygoogle = window.adsbygoogle || []).push({});
		</script>
	</div>
<?php
}
?>
	<div id="subscribe" class="card">
		<div class="img"><?=icon('envelope fa-4x')?></div>
		<form action="#" method="post" onsubmit="return false">
			<h6>Subscribe To Our News Letter</h6>
			<input id="subs-email" type="email" />
			<button class="btn" type="submit">Subscribe</button>
		</form>
	</div>
<?php
if( isset($_populars[0]) ) {
	echo <<<'EOS'
	<ul id="tabs">
		<li class="strip"></li>
		<li class="tab"><a href="#popular" class="active">Popular</a></li>
		<li class="tab"><a href="#comment">Comments</a></li>
		<li class="tab"><a href="#archive">Archive</a></li>
	</ul>
	<div class="feed feed-list feed-sm aside" id="popular">
EOS;
	foreach( $_populars as $entry ) {
		$entry->thumbnail = json_decode($entry->thumbnail);
		$entry->thumbnail = Media::getImage( $entry->thumbnail, 'small' );
		$entry->thumbnail = htmlentities($entry->thumbnail);
		$entry->excerpt = htmlspecialchars($entry->excerpt);
		$entry->posted = parseDate( $entry->posted );
		$entry->permalink = Rewrite::postUri( $entry );
?>
		<article>	
			<div class="feed-image"><img alt="<?=htmlentities($entry->title)?>" src="<?=$entry->thumbnail?>" /></div>
			<h3><a href="<?=$entry->permalink?>"><?=htmlspecialchars($entry->title)?></a></h3>
			<p><span class="excerpt"><?=$entry->excerpt?>...</span></p>
		</article>
<?php
	}
	echo '</div>';
}
?>
	<div id="archives">
		<label for="archive">Select Archive</label>
		<select id="archive">
			<option value="" selected>--Archive--</option>
<?php
foreach( $_archives as $entry ) {
	$entry = $entry->archive;
	$entry = explode( "|", $entry );
	$index = sprintf( '%s/%s/%s/', BASEPATH, $entry[0], $entry[2] );
	$entry = sprintf( '%s %s', $entry[1], $entry[0] );
	$entry = htmlspecialchars($entry);
	$index = htmlentities($index);
?>
			<option value="<?=$index?>"><?=$entry?></option>
<?php
}
?>
		</select>
	</div>
	<div id="replies">
		<div class="comment-item">
			<img alt="Amaedy" class="comment-img" src="<?=BASEPATH?>/images/owner.jpg" />
			<p class="comment-body">
				<span>Sombody</span>
				<span>Jan 25, 2019 12:12:01 AM</span>
				<span>This is a comment protorype I am showing, it was done yesterday.</span>
			</p>
		</div>
	</div>
	<div class="heading"><h2><a href="#">PAGES</a></h2></div>
<?php
if( isset($_pages[0]) ) {
	echo '<ul id="pages" class="card">';
	foreach( $_pages as $index => $entry ) {
		echo '<li><a href="#">Page ', $index, '</a></li>';
	}
	echo '</ul>';
}
?>
	<div class="heading"><h2><a href="#">LABELS</a></h2></div>
<?php
if( isset($_postTags[0]) ) {
	echo '<ul id="labels" class="card">';
	foreach( $_postTags as $entry ) {
		$entry->title = htmlspecialchars($entry->title);
		$entry->subject = 'tag';
		$entry->permalink = Rewrite::termUri( $entry );
		$entry->permalink = htmlentities($entry->permalink);
?>
		<li><a href="<?=$entry->permalink?>"><?=$entry->title?></a></li>
<?php
	}
	echo '</ul>';
}
?>
	<div id="about" class="about card">
		<img alt="Amaedy Teeskid" class="about-img" src="<?=BASEPATH?>/images/owner.jpg" />
		<h4 class="about-name">Amaedy Teeskid</h4>
		<p class="about-text">
			<span>Sombody</span>
			<span>Jan 25, 2019 12:12:01 AM</span>
			<span>This is a comment protorype I am showing, it was done yesterday.</span>
		</p>
		</div>
	</div>
</aside>