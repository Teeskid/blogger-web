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
$theValue = $_VARS['p'] ?? $_VARS['name'] ?? null;
$theValue = [ 'value' => $theValue ];
$postType = $db->prepare( 'SELECT subject FROM Post WHERE id=:value OR permalink=:value LIMIT 1' );
$postType->execute( $theValue );
$postType = $postType->fetchColumn();
if( ! $postType )
	redirect( BASEPATH . '/404.php' );

if( $postType === 'page' ) {
	require( ABSPATH . HANDLERS . '/page.php' );
	return false;
}

$post  = $db->prepare( 'SELECT * FROM Post WHERE id=:value OR permalink=:value LIMIT 1' );
$post->execute( $theValue );

$theCrumb = [ [ BASEPATH . '/', 'Home' ] ];

$post = $db->fetchClass( $post, 'Post' );
$post->posted = parseDate( $post->posted );
$post->permalink = Rewrite::postUri( $post );
$post->category = $post->category ?? 1;
$post->author = $post->author ?? 1;

$theTerms = $db->prepare( 'SELECT a.title, a.permalink FROM Term a WHERE a.id=:id OR id=(SELECT b.master FROM Term b WHERE b.id=:id LIMIT 1) ORDER BY a.master ASC' );
$theTerms->execute( [ 'id' => $post->category ] );
$theTerms = $theTerms->fetchAll( PDO::FETCH_CLASS, 'Term' );
foreach( $theTerms as $entry ) {
	$entry->subject = 'cat';
	$entry->permalink = Rewrite::termUri( $entry );
	$theCrumb[] = [ $entry->permalink, $entry->title ];
}
$theCrumb[] = $post->title;
unset($theTerms);

$postAuthor = $db->prepare( 'SELECT userName, IFNULL(fullName, userName) as fullName FROM Person WHERE id=? LIMIT 1' );
$postAuthor->execute( [ $post->author ] );
$postAuthor = $db->fetchClass( $postAuthor, 'User' );

if( $post->thumbnail ) {
	$postThumbnail = $db->prepare( 'SELECT metaValue FROM PostMeta WHERE postId=? AND metaKey=\'media_metadata\' LIMIT 1' );
	$postThumbnail->execute( [ $post->thumbnail ] );
	$postThumbnail = $postThumbnail->fetchColumn();
	$postThumbnail = json_decode($postThumbnail);
	$postThumbnail = BASEPATH . DIR_UPLOAD . $postThumbnail->name;
} else {
	$postThumbnail = ABSPATH . DIR_IMAGES . 'thumbnail.png';
}

// Fetch the attached labels
$postTags = $db->prepare( 'SELECT title, permalink FROM Term WHERE EXISTS(SELECT postId FROM TermLink WHERE TermLink.postId=? AND TermLink.termId=Term.id)' );
$postTags->execute( [ $post->id ] );
$postTags = $postTags->fetchAll( PDO::FETCH_CLASS, 'Term' );
foreach( $postTags AS &$entry ) {
	$entry->subject = 'tag';
	$entry->title = htmlspecialchars($entry->title);
	$entry->permalink = Rewrite::termUri( $entry );
	$entry->permalink = htmlentities($entry->permalink);
	$entry = sprintf( '<a href="%s">%s</a>', $entry->permalink, $entry->title );
}
$postTags = implode( ', ', $postTags );

// Fetch the attached media objects
preg_match_all( '#\[media=([0-9]+)\](?:\[\/media\])#', $post->content, $postMedia );
$postMedia = $postMedia[1] ?? [];
if( isset($postMedia[0]) ) {
	$postMedia = array_unique($postMedia);
	$postMedia = array_map( 'parseInt', $postMedia );
	$postMedia = array_map( 'escQuote', $postMedia );
	$postMedia = implode( ',', $postMedia );
	$postMedia = $db->prepare( 'SELECT a.title, a.mimeType, b.metaValue AS metadata FROM Post a LEFT JOIN PostMeta b ON b.postid=a.id AND b.metaKey=? WHERE a.id IN (' . $postMedia . ')' );
	$postMedia->execute( [ 'media_metadata' ] );
	$postMedia = $postMedia->fetchAll( PDO::FETCH_CLASS, 'Media' );
}

// Fetch Related posts
$postRelated = $db->prepare( 'SELECT a.id, a.title, a.permalink, a.posted, b.metaValue AS thumbnail FROM Post a LEFT JOIN PostMeta b ON b.postId=a.thumbnail AND b.metaKey=? WHERE a.subject=? AND a.category=? LIMIT 9' );
$postRelated->execute( [ 'media_metadata', 'post', $post->category ] );
$postRelated = $postRelated->fetchAll( PDO::FETCH_CLASS, 'Post' );

// Fetch posted comments
$postReplies = $db->prepare( 'SELECT master, id, fullName, email, website, content, replied FROM Reply WHERE postId=? ORDER BY replied DESC' );
$postReplies->execute( [ $post->id ] );
$postReplies = $postReplies->fetchAll(PDO::FETCH_GROUP);
$postReplies[0] = isset($postReplies[0]) ? $postReplies : [];

if( isset($postThumbnail) )
	$post->content = str_replace( '[thumbnail]', sprintf( '[img="%s"]%s[/img]', $postThumbnail, $post->title), $post->content );
if( isset($postMedia) ) {
	$regexReplace = function( $match ) use($post) {
		$metadata = jsonUnserialize( $entry->metadata );
		$entry->source = BASEPATH . uri_upload( $metadata->filename ?? '' );
		if( false !== strpos( $entry->mimeType, 'image/' ) ) {
			$entry = sprintf( '[img="%s"]%s[/img]', $entry->source, $post->title );
		} else {
			if( empty($entry->drawables) ) {
				$entry->drawables = mediaIcon( $entry->mime );
				$entry->drawables = icon( $entry->drawables );
			} else {
				$entry->drawables = get_drawable( $entry->drawables, DRAWABLE_TINY );
				$entry->name = htmlentities( $entry->name );
				$entry->drawables = htmlentities( $entry->drawables );
				$entry->drawables = sprintf( '<img src="%s" alt="%s" />', $entry->drawables, $entry->name );
			}
			$entry->permalink = '$1/attachment/'.$entry->permalink.'/';
			$entry->permalink = preg_replace( '#(.+?)/+?$#', $entry->permalink, $post->permalink );
			$entry->permalink = esc_dir( $entry->permalink );
			$entry->permalink = htmlentities( $entry->permalink );
			$entry->size = formatSize( $entry->size );
			$entry->size = htmlspecialchars( $entry->size );
			$entry->mime = htmlspecialchars( $entry->mime );
			$entry->source = htmlentities( $entry->source );
			$entry = sprintf(
				'</p><div class="media">%s'.
					'<a href="%s"><h5>%s</h5></a>'.
					'<p>Size: %s<br>Type: %s<br><a href="%s" class="btn-small" download>Download</a></p>'.
				'</div><p>',
				$entry->drawables, $entry->permalink, $entry->name, $entry->size, $entry->mime, $entry->source
			);
		}
		$post->content = str_replace( $index, $entry, $post->content, $count );
		if( $count !== 0 )
			$index = $index + 1;
		return $post->content;
	};
	$postMedia = array_filter( $postMedia, 'notEmpty' );
	$post->content = preg_replace_callback( '#\[media=([0-9]+)\]\[\/media\]#', $regexReplace, $post->content );
}
$post->content = preg_replace( '#\[media-[0-9]+\]#i', '<span class="message error">Attachemt has been removed / was not uploaded.</span>', $post->content );
$post->content = preg_replace_callback( '#\[img="(.+?)"\]((.+?)\[/img\])?#s', function( $tmp ) {
	$tmp[1] = htmlentities( $tmp[1] );
	$tmp[2] = htmlentities( $tmp[2] );
	$tmp = sprintf( '</p><a class="image" href="%s" ><img src="%s" alt="%s" /></a><p>', $tmp[1], $tmp[1], $tmp[2] );
	return $tmp;
}, $post->content );
// parseBBCode( $entry->content );

$_page = new Page( $post->title, $post->permalink );
$_page->setMetaItem( Page::META_CSS_LOAD, 'post' );
$post->title = htmlspecialchars($post->title);
require( ABSPATH . BASE_UTIL . '/UIUtil.php' );
include( ABSPATH . BASE_UTIL . '/HeadHtml.php' );
htmBreadCrumb( $theCrumb );
?>
<div class="post">
	<div class="post-head">
		<div class="feed-date">
			<span class="dd"><?=$post->posted->day?></span>
			<span class="mm"><?=$post->posted->month?></span>
			<span class="yy"><?=$post->posted->year?></span>
		</div>
		<h2><?=$post->title?></h2>
	</div>
	<div id="postBar" class="btn-group">
<?php
if( isLoggedIn() ) {
	$return = rawurlencode($_page->path);
?>
		<a class="btn btn-sm" href="<?=USERPATH?>/post-edit.php?id=<?=$post->id?>&action=modify&redirect=<?=$return?>" target="_blank"><?=icon('edit')?> Edit</a>
		<a class="btn btn-sm" href="<?=USERPATH?>/post-edit.php?id=<?=$post->id?>&action=delete&redirect=<?=$return?>" target="_blank"><?=icon('trash')?> Delete</a>
<?php
}
?>
		<a class="btn btn-sm" href="#" onclick="window.print()"><?=icon('print')?> Print</a>
	</div>
	<div class="post-content"><p><?=$post->content?></p></div>
<?php
if( $postType === 'post' ) {?>
	<div class="post-foot">
		<div class="post-share">
			<span>SHARE ON:</span>
			<a class="btn btn-sm" href="#"><?=icon('share-alt')?></a>
			<a class="btn btn-sm" href="#"><?=icon('facebook-official')?></a>
			<a class="btn btn-sm" href="#"><?=icon('twitter')?></a>
			<a class="btn btn-sm" href="#"><?=icon('google-plus')?></a>
			<a class="btn btn-sm" href="#"><?=icon('facebook')?></a>
			<a class="btn btn-sm" href="#"><?=icon('envelope')?></a>
		</div>
		<p>Posted By: <?=$postAuthor->fullName?><br/>Post Tags: <?=$postTags?></p>
	</div>
<?php
}
?>
</div>
<?php
if( $postType === 'post' ) {
?>
<div class="heading"><h3><a href="#">RELATED POSTS</a></h3></div>
<div class="feed feed-slide owl-carousel owl-theme">
<?php
	foreach( $postRelated AS &$entry ) {
		$entry->posted = parseDate( $entry->posted );
		$entry->permalink = Rewrite::postUri( $entry );
		$entry->thumbnail = json_decode($entry->thumbnail);
		$entry->thumbnail = Media::getImage( $entry->thumbnail, 'large' );
		$entry->thumbnail = htmlentities($entry->thumbnail);
?>
	<article>
		<div class="feed-image">
			<img alt="<?=htmlentities($entry->title)?>" src="<?=$entry->thumbnail?>" />
			<div class="feed-date">
				<span class="dd"><?=$entry->posted->day?></span>
				<span class="mm"><?=$entry->posted->month?></span>
				<span class="yy"><?=$entry->posted->year?></span>
			</div>
		</div>
		<h3><a href="<?=$entry->permalink?>"><?=htmlspecialchars($entry->title)?></a></h3>
	</article>
<?php
	}
?>
</div>
<div class="heading">
	<h3><a href="#">Post A Comment</a></h3>
	<div class="btn-group">
		<a href="#" class="active">Blogger</a>
		<a href="#">Facebook</a>
	</div>
</div>
<div class="comments-outer">
	<div id="comments" class="comments">
<?php
		$GLOBALS['htm_comment'] = function( $entry ) use( $postReplies ) {
			$entry->content = htmlspecialchars( $entry->content );
			parseBBCode( $entry->content );
			$entry->name = htmlspecialchars( $entry->name );
			$entry->website = htmlentities( $entry->website );
			if( empty($entry->website) ) {
				$entry->website = sprintf( '<span class="name">%s</span>', $entry->name );
			} else {
				$entry->website = sprintf( '<a class="name" href="%s">%s</a>', $entry->website, $entry->name );
			}
			$children = isset($postReplies[$entry->id]) ? $postReplies[$entry->id] : [];
			$iis_papa = ! empty( $children );
			printf(
				'<div class="comment" id="comment-%s">'.
					'<div class="image holder"><img src="" /></div>'.
					'<p class="meta">'.
						'%s <span>%s</span><br><span class="content">%s</span><br><a href="#comment" data-comment="%s">Reply</a>'.
					'</p>',
				$entry->id, $entry->website, $entry->date, $entry->content, $entry->id
			);
			if( $iis_papa )
				echo '<div class="replies">';
			array_map( $GLOBALS['htm_comment'], $children );
			if( $iis_papa )
				echo '</div>';
			echo( '</div>' );
		};
		foreach( $postReplies[0] as $entry )
			$htm_comment($entry);
		?>
	</div>
	<form id="comment" class="comment-form reply" role="form" action="<?=BASEPATH?>/comment.php" method="post">
		<input id="comment-parent" type="hidden" name="master" value="0" />
		<div class="right-align no-margin"><button id="comment-stop" class="btn" type="button">Cancel Reply</button></div>
		<p class="text-center message">Your email address will not be published</p>
		<input id="comment-redirect" type="hidden" name="redirect" value="<?=$post->permalink?>" />
		<input id="comment-postid" type="hidden" name="postid" value="<?=$post->id?>" />
		<div class="input-field">
			<label for="comment-content">Your Comment</label>
			<textarea id="comment-content" spellcheck="false" autocomplete="false" name="content" required="true"></textarea>
		</div>
<?php
		if( isLoggedIn() ) {
?>
		<input id="comment-author" type="hidden" name="author" value="<?=$_login->userId?>" />
<?php
	} else {
?>
		<input id="comment-author" type="hidden" name="author" value="0" />
		<div class="input-field select">
			<label for="author">Comment As:</label>
			<select id="comment-mode" autocomplete="false" type="text" name="mode">
				<option value="" selected>Select profile...</option>
				<option value="anonymous" selected>Anonymous</option>
				<option value="account">Name and email address</option>
				<option value="facebook">Facebook Account</option>
				<option value="google">Google Account</option>
			</select>
		</div>
		<div id="comment-account" style="display:none">
			<div class="row">
				<div class="input-field col s6">
					<input id="comment-name" placeholder="Your Name" autocomplete="false" type="text" name="name" required="true" disabled="true" /></div>
				<div class="input-field col s6">
					<input id="comment-email" placeholder="Your Email" autocomplete="false" type="email" name="email" required="true" disabled="true" /></div>
			</div>
			<div class="input-field">
				<input id="comment-website" placeholder="Your Website" autocomplete="false" type="text" name="website" value="http://" optional="true" disabled="true" />
			</div>
			<p class="checkbox">
				<label for="comment-autosave"><input id="comment-autosave" type="checkbox" />
				<span> Save my name, email, and website in this browser for the next time I comment.</label>
			</p>
		</div>
		<?php
}
?>
		<div class="input-field submit">
			<button type="submit" class="btn-small" name="submit">Publish</button>
			<button type="submit" class="btn-small" name="previu">Preview</button>
		</div>
	</form>
</div>
<?php
$_page->setMetaItem( Page::META_JS_CODE, <<<'EOS'
$(document).ready(function(){
	var owl = $('.feed.owl-carousel');
	owl.owlCarousel({
		autoplay:true,
		autoplayTimeout:5000,
		autoplayHoverPause:true,
		center:false,
		rewind:true,
		loop:false,
		nav:true,
		margin:10,
		slideTransition:'ease-in-out',
		fluidSpeed: true,
		stagePadding:10,
		smartSpeed:450,
		animateOut: 'slideOutDown',
		animateIn: 'bounce',
		responsive:{
			0:{
				items:1
			},
			600:{
				items:2
			},            
			980:{
				items:3
			},
			1200:{
				items:4
			}
		}
	});
	owl.on('mousewheel', '.owl-stage', function (e) {
		if (e.deltaY>0) {
			owl.trigger('next.owl');
		} else {
			owl.trigger('prev.owl');
		}
		e.preventDefault();
	});
	/*
	$(".comment a[data-comment]").map(function(e, elem){
		$(this).click(function(e){
			var master = parseInt(e.target.closest("a").dataset.comment);
			$("form#comment").addClass("reply");
			$("input#comment-parent").val(master);
		});
	});
	$("#comment-stop").click(function(e){
		$("form#comment").removeClass("reply");
		$("input#comment-parent").val("0");
	});
	(function(content){
		content.keyup(function(e){
			alert(event.which);
		});
	})($("#comment-content"));
	(function(commentMode){
		commentMode[0].selectedIndex = 0;
		$(commentMode).change(function(e){
			var commentMode = e.target.closest("select").value;
			switch(commentMode) {
				case 'account':
					$("input#comment-email, input#comment-name, input#comment-website").removeAttr("disabled");
					$("#comment-account").slideDown();
					break;
				default:
					$("#comment-email, #comment-name, #comment-website").attr("disabled", "disabled");
					$("#comment-account").slideUp();
			}
		});
	})($("#comment-mode"));
	*/
});
EOS
);
}
include( ABSPATH . BASE_UTIL . '/TailHtml.php' );
