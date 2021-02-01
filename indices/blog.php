<?php
/**
 * Blog Request Handler
 * @package Sevida
 * @subpackage Handlers
 */
if( ! defined('ABSPATH') )
	die();
require_once( ABSPATH . BASE_UTIL . '/HtmlUtil.php' );

$sqlTrend = $_GET['blog'];
$sqlWhere = [ 'a.rowType=?', 'a.status=?' ];
$sqlOrder = [ 'a.id DESC' ];
$theCrumb = [ [ 'index.php', 'Home' ] ];
switch( $sqlTrend ) {
	case Term::TYPE_TAG:
		$theTerm = $_db->prepare( 'SELECT id, title FROM Term WHERE rowType=? AND permalink=? LIMIT 1' );
		$theTerm->execute( [ Term::TYPE_TAG, $_GET['value'] ] );
		if( 0 === $theTerm->rowCount() )
			redirect( BASEURI . '/404.php' );
		$theTerm = $_db->fetchClass( $theTerm, 'Term' );
		$theTerm->rowType = Term::TYPE_TAG;
		$theTerm->permalink = Rewrite::termUri( $theTerm );
		$theCrumb[] = $theTerm->title;
		$sqlWhere[] = 'EXISTS(SELECT postId FROM TermLink WHERE TermLink.termId=' . $_db->quote( $theTerm->id ) . ' AND TermLink.postId=a.id)';
		$sqlOrder[] = 'a.datePosted DESC';
		initHtmlPage( sprintf( 'TAGGED "%s"', $theTerm->title ), $theTerm->permalink );
		unset($theTerm);
		break;
	case 'category':
		$theTerms = $_db->prepare( 'SELECT a.id, a.title, a.permalink FROM Term a WHERE a.rowType=:rowType AND (a.permalink=:permalink OR id=(SELECT b.inTerm FROM Term b WHERE b.permalink=:permalink LIMIT 1)) ORDER BY a.inTerm DESC' );
		$theTerms->execute( [ 'rowType' => Term::TYPE_CAT, 'permalink' => $_GET['name'] ] );
		if( 0 === $theTerms->rowCount() )
			redirect( BASEURI . '/404.php' );
		$theTerms = $theTerms->fetchAll( PDO::FETCH_CLASS, 'Term' );
		if( isset($theTerms[1]) ) {
			$theTerm0 = $theTerms[1];
			$theTerm0->rowType = Term::TYPE_CAT;
			$theTerm0->permalink = Rewrite::termUri( $theTerm0 );
			$theCrumb[] = [ $theTerm0->permalink, $theTerm0->title ];
			unset( $theTerm0, $theTerms[1] );
		}
		$theTerms = $theTerms[0];
		$theTerms->permalink = Rewrite::termUri( $theTerms );
		$theCrumb[] = $theTerms->title;
		$sqlWhere[] = 'a.category=' . $_db->quote( $theTerms->id );
		$sqlOrder[] = 'a.datePosted DESC';
		initHtmlPage( $theTerms->title, $theTerms->permalink );
		unset($theTerms);
		break;
	case 'year':
		$dateYY = parseInt( $_GET['value'] );
		$dateXX = date_create( $dateYY . '-01-01' );
		if( ! $dateXX )
			redirect( BASEURI . '/404.php' );
		$dateZZ = date_create( $dateXX->format( 'Y-12-31' ) );
		$dateZZ = $dateZZ->format( 'Y-m-d' );
		$dateXX = $dateXX->format( 'Y-m-d' );
		$theCrumb[] = $dateYY;
		$sqlOrder[] = 'a.datePosted ASC';
		$sqlWhere[] = sprintf( 'a.datePosted BETWEEN %s AND %s', $_db->quote( $dateXX ), $_db->quote( $dateZZ ) );
		initHtmlPage( 'Posts In ' . $dateYY, '/' . $dateYY . '/' );
		unset( $dateXX, $dateYY, $dateZZ );
		break;
	case 'month':
		$dateYY = parseInt( $_GET['year'] );
		$dateMM = parseInt( $_GET['month'] );
		$dateXX = date_create( sprintf( '%s-%s-01', $dateYY, $dateMM ) );
		if( ! $dateXX )
			redirect( BASEURI . '/404.php' );
		$dateZZ = date_create( $dateXX->format( 'Y-m-31' ) );
		$dateZZ = $dateZZ->format('Y-m-d');
		$sqlWhere[] = sprintf( 'a.datePosted BETWEEN %s AND %s', $_db->quote( $dateXX->format('Y-m-d') ), $_db->quote($dateZZ) );
		$sqlOrder[] = 'a.datePosted ASC';
		$theCrumb[] = [ BASEURI . sprintf( '/%s/', $dateYY ), $dateYY ];
		$theCrumb[] = $dateXX->format('F');
		initHtmlPage( $dateXX->format('F Y'), $dateXX->format('/Y/m/') );
		unset( $dateMM, $dateXX, $dateYY, $dateZZ );
		break;
	case 'latest':
		$sqlOrder[] = 'a.datePosted DESC';
		$theCrumb[] = 'Latest';
		initHtmlPage( 'Latest', '/' );
		break;
	default:
		die();
}
$sqlWhere = implode( ' AND ', $sqlWhere );
$sqlOrder = implode( ', ', $sqlOrder );

$curpage = $_GET['page'] ?? 1;
$curpage = parseInt( $curpage );

$paging = $_db->prepare( 'SELECT COUNT(a.id) FROM Post a WHERE ' . $sqlWhere );
$paging->execute( [ 'post', 'public' ]);
$paging =  parseInt( $paging->fetchColumn() );
$paging = new Paging( isMobileClient() ? 8 : 9, $paging );

$thePosts = $_db->prepare(
	'SELECT a.id, a.title, a.permalink, a.excerpt, a.datePosted, IFNULL(b.title, ?) AS category, c.metaValue AS thumbnail, IFNULL(d.userName, ?) AS author '.
	'FROM Post a LEFT JOIN Term b ON b.id=a.category LEFT JOIN PostMeta c ON c.postId=a.thumbnail AND c.metaKey=? '.
	'LEFT JOIN Uzer d ON d.id=a.author WHERE ' . $sqlWhere . ' ORDER BY ' . $sqlOrder . ' LIMIT ' . $paging->getLimit()
);
$thePosts->execute( [ 'Uncategorized', 'Anonymous', 'media_metadata', 'post', 'public' ] );
$thePosts = $thePosts->fetchAll( PDO::FETCH_CLASS, 'Post' );

$w_carousel = [];

include_once( __DIR__ . '/header.php' );
BreadCrumb( $theCrumb );
?>
<div class="heading">
	<h2><a href="#main-feed"><?=escHtml($HTML->title)?></a></h2>
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
		$post->datePosted = parseDate($post->datePosted);
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
				<span class="dd"><?=$post->datePosted->day?></span>
				<span class="mm"><?=$post->datePosted->month?></span>
				<span class="yy"><?=$post->datePosted->year?></span>
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

doHtmlPaging( $paging, $HTML->path );
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
					<div class="w-date"><?=$post->datePosted?></div>
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
	<div class="col-md-6">
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
							<div class="w-date"><?=$post->datePosted?></div>
						</a>
					</div>
					<strong class="name"><a href="<?=$post->permalink?>"><?=$post->title?></a></strong>
				</article>
			</div>
<?php
	}
	while($post = $cat->posts->fetch()) {
		$post->permalink = Rewrite::postUri($post->permalink, 1);
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
function onPageJsCode() {
?>
document.addEventListener("DOMContentLoaded", function(){
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
<?php
}
include( __DIR__ . '/footer.php' );
