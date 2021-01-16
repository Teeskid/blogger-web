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
require( ABSPATH . BASE_UTIL . '/HtmlUtil.php' );

$sqlTrend = $_VARS['trend'];
$sqlWhere = [ 'a.rowType=?', 'a.status=?' ];
$sqlOrder = [ 'a.id DESC' ];
$theCrumb = [ [ 'index.php', 'Home' ] ];
switch( $sqlTrend ) {
	case 'tag':
		$theTerm = $db->prepare( 'SELECT id, title FROM Term WHERE rowType=? AND permalink=? LIMIT 1' );
		$theTerm->execute( [ 'tag', $_VARS['value'] ] );
		if( 0 === $theTerm->rowCount() )
			redirect( BASEPATH . '/404.php' );
		$theTerm = $db->fetchClass( $theTerm, 'Term' );
		$theTerm->rowType = 'tag';
		$theTerm->permalink = Rewrite::termUri( $theTerm );
		$theCrumb[] = $theTerm->title;
		$sqlWhere[] = 'EXISTS(SELECT postId FROM TermLink WHERE TermLink.termId=' . $db->quote( $theTerm->id ) . ' AND TermLink.postId=a.id)';
		$sqlOrder[] = 'a.posted DESC';
		$_page = new Page( sprintf( 'TAGGED "%s"', $theTerm->title ), $theTerm->permalink );
		unset($theTerm);
		break;
	case 'category':
		$theTerms = $db->prepare( 'SELECT a.id, a.title, a.permalink FROM Term a WHERE a.rowType=:rowType AND (a.permalink=:permalink OR id=(SELECT b.master FROM Term b WHERE b.permalink=:permalink LIMIT 1)) ORDER BY a.master DESC' );
		$theTerms->execute( [ 'rowType' => 'cat', 'permalink' => $_VARS['value'] ] );
		if( 0 === $theTerms->rowCount() )
			redirect( BASEPATH . '/404.php' );
		$theTerms = $theTerms->fetchAll( PDO::FETCH_CLASS, 'Term' );
		if( isset($theTerms[1]) ) {
			$theTerm0 = $theTerms[1];
			$theTerm0->rowType = 'cat';
			$theTerm0->permalink = Rewrite::termUri( $theTerm0 );
			$theCrumb[] = [ $theTerm0->permalink, $theTerm0->title ];
			unset( $theTerm0, $theTerms[1] );
		}
		$theTerms = $theTerms[0];
		$theTerms->permalink = Rewrite::termUri( $theTerms );
		$theCrumb[] = $theTerms->title;
		$sqlWhere[] = 'a.category=' . $db->quote( $theTerms->id );
		$sqlOrder[] = 'a.posted DESC';
		$_page = new Page( $theTerms->title, $theTerms->permalink );
		unset($theTerms);
		break;
	case 'year':
		$dateYY = parseInt( $_VARS['value'] );
		$dateXX = date_create( $dateYY . '-01-01' );
		if( ! $dateXX )
			redirect( BASEPATH . '/404.php' );
		$dateZZ = date_create( $dateXX->format( 'Y-12-31' ) );
		$dateZZ = $dateZZ->format( 'Y-m-d' );
		$dateXX = $dateXX->format( 'Y-m-d' );
		$theCrumb[] = $dateYY;
		$sqlOrder[] = 'a.posted ASC';
		$sqlWhere[] = sprintf( 'a.posted BETWEEN %s AND %s', $db->quote( $dateXX ), $db->quote( $dateZZ ) );
		$_page = new Page( 'Posts In ' . $dateYY, '/' . $dateYY . '/' );
		unset( $dateXX, $dateYY, $dateZZ );
		break;
	case 'month':
		$dateYY = parseInt( $_VARS['year'] );
		$dateMM = parseInt( $_VARS['month'] );
		$dateXX = date_create( sprintf( '%s-%s-01', $dateYY, $dateMM ) );
		if( ! $dateXX )
			redirect( BASEPATH . '/404.php' );
		$dateZZ = date_create( $dateXX->format( 'Y-m-31' ) );
		$dateZZ = $dateZZ->format('Y-m-d');
		$sqlWhere[] = sprintf( 'a.posted BETWEEN %s AND %s', $db->quote( $dateXX->format('Y-m-d') ), $db->quote($dateZZ) );
		$sqlOrder[] = 'a.posted ASC';
		$theCrumb[] = [ BASEPATH . sprintf( '/%s/', $dateYY ), $dateYY ];
		$theCrumb[] = $dateXX->format('F');
		$_page = new Page( $dateXX->format('F Y'), $dateXX->format('/Y/m/') );
		unset( $dateMM, $dateXX, $dateYY, $dateZZ );
		break;
	case 'latest':
		$sqlOrder[] = 'a.posted DESC';
		$theCrumb[] = 'Latest';
		$_page = new Page( 'Latest', '/' );
		break;
	default:
		die();
}
$sqlWhere = implode( ' AND ', $sqlWhere );
$sqlOrder = implode( ', ', $sqlOrder );

$curpage = $_VARS['page'] ?? 1;
$curpage = parseInt( $curpage );

$paging = $db->prepare( 'SELECT COUNT(a.id) FROM Post a WHERE ' . $sqlWhere );
$paging->execute( [ 'post', 'public' ]);
$paging =  parseInt( $paging->fetchColumn() );
$paging = new Paging( isMobileClient() ? 8 : 9, $paging );

$thePosts = $db->prepare(
	'SELECT a.id, a.title, a.permalink, a.excerpt, a.posted, IFNULL(b.title, ?) AS category, c.metaValue AS thumbnail, IFNULL(d.userName, ?) AS author '.
	'FROM Post a LEFT JOIN Term b ON b.id=a.category LEFT JOIN PostMeta c ON c.postId=a.thumbnail AND c.metaKey=? '.
	'LEFT JOIN Person d ON d.id=a.author WHERE ' . $sqlWhere . ' ORDER BY ' . $sqlOrder . ' LIMIT ' . $paging->getLimit()
);
$thePosts->execute( [ 'Uncategorized', 'Anonymous', 'media_metadata', 'post', 'public' ] );
$thePosts = $thePosts->fetchAll( PDO::FETCH_CLASS, 'Post' );

$w_carousel = [];

include( ABSPATH . BASE_UTIL . '/HeadHtml.php' );

echo '<ul class="breadcrumb">';
foreach( $theCrumb as $entry ) {
	if( is_array($entry) ) {
		$index = escHtml($entry[0]);
		$entry = escHtml($entry[1]);
		echo '<li><a href="', $index, '">', $entry, '</a></li>';
	} else {
		$entry = escHtml($entry);
		echo '<li class="active">', $entry, '</li>';
	}
}
unset( $theCrumb, $index, $entry );
echo '</ul>';
?>
<div class="heading">
	<h2><a href="#main-feed"><?=escHtml($_page->title)?></a></h2>
	<div id="feedView" class="btn-group" role="group">
		<button type="button" data-view="list" class="active"><?=icon('list-ul')?></button>
		<button type="button" data-view="grid"><?=icon('th')?></button>
	</div>
</div>
<?php
$iconSh = icon('share-alt');
$iconFb = icon('facebook');
$iconTw = icon('twitter');
$iconWa = icon('whatsapp');
if( isset($thePosts[0]) ) {
	echo '<div id="mainFeed" class="feed feed-list">';
	foreach( $thePosts as $post ) {
		$post->posted = parseDate($post->posted);
		$post->permalink = Rewrite::postUri( $post );
		$post->thumbnail = json_decode($post->thumbnail);
		$post->thumbnail = Media::getImage( $post->thumbnail, 'large' );
		$post->thumbnail = escHtml($post->thumbnail);
		$post->excerpt = escHtml($post->excerpt);
		$post->permalink = escHtml($post->permalink);
?>
	<article>
		<div class="feed-image">
			<img alt="<?=escHtml($post->title)?>" src="<?=$post->thumbnail?>" />
			<div class="feed-date">
				<span class="dd"><?=$post->posted->day?></span>
				<span class="mm"><?=$post->posted->month?></span>
				<span class="yy"><?=$post->posted->year?></span>
			</div>
			<div class="feed-share" role="group">
				<a href="#"><?=$iconFb?></a>
				<a href="#"><?=$iconTw?></a>
				<a href="#"><?=$iconWa?></a>
			</div>
		</div>
		<h3><a href="<?=$post->permalink?>"><?=escHtml($post->title)?></a></h3>
		<p>
			<span class="excerpt"><?=$post->excerpt?>...</span>
			<a href="<?=$post->permalink?>" class="btn">READ MORE</a>
		</p>
	</article>
	<div class="clear"></div>
<?php
	}
	unset($post);
	echo '</div>';
}
else {
	echo '<p class="message">No records found</p>';
}

doHtmlPaging( $paging, $_page->path );
?>
<div class="heading">
	<h2><a href="#">Related Posts</a></h2>
</div>
<!--CAROUSEL-->
<div class="owl-carousel owl-theme feed feed-carousel">
<?php
foreach( $w_carousel as $post ) {
	$post->permalink = postUri($post->permalink, 1);
	$post->thumbnail = feed_image($post->thumbnail, '200x200', 0, 'active');
?>
	<div>
		<article>
			<div class="image">
				<a href="$post->permalink">
					<ul><?=$post->thumbnail?></ul>
					<div class="w-date"><?=$post->posted?></div>
				</a>
			</div>
			<div class="feed-body">
				<strong class="name"><a href="<?=$post->permalink?>"><?=$post->title?></a></strong>
			</div>
		</article>
	</div>
<?php
}
unset($post);
?>
</div>
<!--/CAROUSE-->

<!--POPULAR CAT TABS-->
<div class="row">
<?php
if(false){
foreach($quer3 as $cat)
{
?>
	<div class="col-xs-12 col-md-6">
		<div class="heading">
			<h2 class="heading-left title"><a href="#"><?=$cat->title?></a></h2>
		</div>
		<div class="feed ccountpat">
<?php
	if($post = $cat->posts->fetch())
	{
		$post->permalink = postUri($post->permalink, 1);
		$post->thumbnail = explode('|', $post->thumbnail);
		$post->thumbnail[0] = uri_image($post->thumbnail[0], '300x200', 1);
		$post->thumbnail[2] = (int) $post->thumbnail[2];
		$post->thumbnail[2] = $post->thumbnail[2] >=1 ? 'landscape' : 'portrait'; 
		$post->thumbnail = "<li class=\"active\"><img  class=\"{$post->thumbnail[2]}\" alt=\"{$post->thumbnail[1]}\" src=\"{$post->thumbnail[0]}\" /></li>";
?>
			<div class="feed-item legend">
				<article>
					<div class="image">
						<a href="<?=$post->permalink?>">
							<ul><?=$post->thumbnail?></ul>
							<div class="w-date"><?=$post->posted?></div>
						</a>
					</div>
					<strong class="name"><a href="<?=$post->permalink?>"><?=$post->title?></a></strong>
				</article>
			</div>
<?php
	}
	while($post = $cat->posts->fetch()) {
		$post->permalink = postUri($post->permalink, 1);
		$post->thumbnail = explode('|', $post->thumbnail);
		$post->thumbnail[0] = uri_image($post->thumbnail[0], '300x200', 1);
		$post->thumbnail = "<li class=\"active\"><img  class=\"landscape\" alt=\"{$post->thumbnail[1]}\" src=\"{$post->thumbnail[0]}\" /></li>";
?>
			<div class="feed-item x-legend">
				<article>
					<div class="image">
						<a href="<?=$post->permalink?>">
							<ul><?=$post->thumbnail?></ul>
						</a>
					</div>
					<div class="feed-body"><strong class="name"><a href="<?=$post->permalink?>"><?=$post->title?></a></strong></div>
				</article>
			</div>
<?php
	}
	unset($post);
?>
		</div>
	</div>
<?php
}
unset($cat);
}
?>
</div>
<?php
$_page->addPageMeta( Page::META_JS_CODE, <<<'EOS'
	$(document).ready(function(){
		$("ul#feedView a[data-view]").click(function(e){
			e.preventDefault();
			var elem = $(this);
			if(elem.attr("data-view") == 'list') {
				$("ul#feedView a[data-view='grid']").removeClass("active");
				$("#main-feed").removeClass("grid").addClass("list");
			} else {
				$("ul#feedView a[data-view='list']").removeClass("active");
				$("#main-feed").removeClass("list").addClass("grid");
			}
			elem.addClass("active");
		});
		var owl = $('.owl-carousel');
		owl.owlCarousel({
			autoplay:true,
			autoplayTimeout:5000,
			autoplayHoverPause:true,
			dotsEach:false,
			loop:false,
			center:false,
			rewind:true,
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
	});
EOS
);
include( ABSPATH . BASE_UTIL . '/TailHtml.php' );
