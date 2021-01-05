<?php
/**
 * HTML pages related helper functions
 *
 * @package Sevida
 * @global $_page;
 */
/**
 * Loads additional javascript files if any was set
 *
 * @global $_page
 */
function doFootJsInc() {
	global $_page;
	$option = $_page->getMetaItem(Page::META_JS_FILE);
	if( isset($option[0]) )
		foreach( $option as $entry )
			echo '<script src="' . htmlentities($entry) . '"></script>';
	unset( $option, $entry );
}
/**
 * Outputs additional page javascript code if any has been set
 *
 * @global $_page
 */
function doFootJsTag() {
	global $_page;
	$option = $_page->getMetaItem(Page::META_JS_CODE);
	if( isset($option[0]) )
		foreach( $option as $entry )
			echo $entry;
	unset( $option, $entry );
}
/**
 * Loads additional css stylesheet files if any has been set
 *
 * @global $_page
 */
function doHeadCssInc() {
	global $_page;
	$option = $_page->getMetaItem(Page::META_CSS_FILE);
	if( isset($option[0]) )
		foreach( $option as $entry )
			echo "\r\t" . '<link rel="stylesheet" href="' . htmlentities($entry) . '" />' . PHP_EOL;
	unset( $option, $entry );
}
/**
 * Outputs additional page stylesheet code if any has been set
 *
 * @global $_page
 */
function doHeadCssTag() {
	global $_page;
	$option = $_page->getMetaItem(Page::META_CSS_CODE);
	if( isset($option[0]) )
		foreach( $option as $entry )
			echo '<style type="text/css">' . htmlspecialchars($entry) . '</style>' . PHP_EOL;
	unset( $option, $entry );
}
/**
 * Outputs additional page meta (head) tags if any has been set
 *
 * @global $_page
 */
function doHeadMetaTag() {
	global $_page;
	$option = $_page->getMetaItem(Page::META_HEAD_TAG);
	if( isset($option[0]) )
		foreach( $option as $entry )
			echo $entry;
	unset( $option, $entry );
}
/**
 * Outputs a navigation breadcrumb using an array of links and a final
 * text-only destination name
 *
 * @global $_page
 * @param array $theCrumb An array in a format [ [ 'Previous', '/page/to/previous'], 'Current' ]
 */
function htmBreadCrumb( array $theCrumb ) {
	echo '<ul class="breadcrumb">';
	foreach( $theCrumb as $entry )
		if( is_array($entry) )
			echo '<li><a href="', htmlentities($entry[0]), '">', htmlspecialchars($entry[1]), '</a></li>';
		else
			echo '<li class="active">', htmlspecialchars($entry), '</li>';
	echo '</ul>';
}
/**
 * Outputs a pagination list using a Paging object created, and a page URL
 *
 * @param Paging $paging
 * @param string $pgUrl The url of the page that is to be paginated
 */
function doHtmlPaging( Paging $paging, string $pgUrl ) {
	$pagePrev = $paging->pageNow - 1;
	$pageNext = $paging->pageNow + 1;
	if( $pagePrev < 1 )
		$pagePrev = 1;
	if( $pageNext > $paging->pageLast )
		$pageNext = $paging->pageLast;
	echo '<nav aria-label="Page Navigation"><ul class="pagination">';
	if( $paging->pageNow === 1 ) {
		echo '<li class="disabled"><span>FIRST</span></li>';
	} else {
		echo '<li><a href="', pageUrl( $pgUrl, 1 ), '">FIRST</a></li>';
		if( $pagePrev !== 1 )
			echo '<li><a href="', pageUrl( $pgUrl, $pagePrev ), '">PREV</a></li>';
	}
	echo '<li class="active"><span>', $paging->pageNow, '</span></li>';
	if( $paging->pageNow === $paging->pageLast ) {
		echo '<li class="disabled"><span>LAST</span></li>';
	} else {
		if( $pageNext !== $paging->pageLast )
			echo '<li><a href="', pageUrl( $pgUrl, $pageNext ), '">NEXT</a></li>';
		echo '<li><a href="', pageUrl( $pgUrl, $paging->pageLast ), '">LAST</a></li>';
	}
	echo '</ul></nav>';
}
function checked( bool $check = true ) : string {
	return $check ? 'checked' : '';
}
function selected( bool $check = true ) : string {
	return $check ? ' selected' : '';
}
function icon( string $icon, string $type = null, string $class = null ) : string {
	if( ! $type )
		$type = 's';
	$classes = [ 'fa' . $type, 'fa-' . $icon ];
	if( $class )
		$classes[] = $class;
	$classes = implode( ' ', $classes );
	$icon = sprintf( '<span class="%s" aria-hidden="true"></span>', $classes );
	return $icon;
}
function isMobileClient() : bool {
	$browser = new Browser();
	return $browser->isMobile();
}
function mediaIcon( string $mime ) : string {
		 if( false !== strpos( $mime, 'image/' ) )
		$icon = 'file-image-o';
	else if( false !== strpos( $mime, 'audio/' ) )
		$icon = 'music';
	else if( false !== strpos( $mime, 'video/' ) )
		$icon = 'file-video-o';
	else if( false !== strpos( $mime, '/zip' ) || false !== strpos( $mime, 'x-rar' ) )
		$icon = 'file-archive-o';
	else if( false !== strpos( $mime, '/pdf' ) )
		$icon = 'file-pdf-o';
	else if( false !== strpos( $mime, '/java-archive' ) || false !== strpos( $mime, 'x-rar' ) )
		$icon = 'android';
	else if( preg_match('#^application/#i', $mime) )
		$icon = 'media-default';
	else
		$icon = '';
	return $icon;
}
function pageUrl( string $url, int $page ) : string {
	$page = 'page=' . $page;
	if( false !== strpos( $url, 'page=' ) )
		$url = preg_replace( '#page=[0-9]+?#i', $page, $url, 1, $tmp );
	else if( false !== strpos( $url, '?' ) )
		$url .= '&' . $page;
	else
		$url .= '?' . $page;
	return $url;
}
function message( string $context, string $message = null ) {
	if( ! $message ) {
		$message = $context;
		$context = 'info';
	}
	if( $message )
		$_SESSION['__MESSAGE__'] = jsonSerialize( [ 'context' => $context, 'message' => $message ] );
	else
		unset($_SESSION['__MESSAGE__']);
}
function thumbnail( $image, $caption, $size, $active = false ) {
	$image = image_drawable( $image, $size );
	$siga = IMAGE_ORIENTATION[$size];
	$active = $active ? 'active' : '';
	$image = sprintf( '<li class="%s"><img class="%s" alt="%s" src="%s" /></li>', $active, $siga, $caption, $image );
	return $image;
}