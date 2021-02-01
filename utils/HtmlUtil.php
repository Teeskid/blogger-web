<?php
/**
 * HTML Page Helper Functions
 * @package Sevida
 * @subpackage Utilities
 */
/**
 * Creates new HTML object instance
 * 
 */
function initHtmlPage( string $title, string $path ) {
	global $HTML;
	$HTML = new Html( $title, $path );
	$GLOBALS['HTML'] = $HTML;
}
/**
 * Adds meta tag to page object
 * @param string $metaTag
 * @global $HTML
 */
function addPageMetaTag( string $metaTag ) {
	global $HTML;
	$HTML->metaTags[] = $metaTag;
}
/**
 * Adds javascript files to page object
 * @param string $path
 * @global $HTML
 */
function addPageJsFile( string $path ) {
	global $HTML;
	$HTML->jsFiles[] = $path;
}
/**
 * Adds javascript codes to page object
 * @param string $code
 * @global $HTML
 */
function addPageJsCode( string $code ) {
	global $HTML;
	$HTML->jsCodes[] = $code;
}
/**
 * Adds stylesheet fiile to page object
 * @param string $path
 * @global $HTML
 */
function addPageCssFile( string $path ) {
	global $HTML;
	$HTML->cssFiles[] = $path;
}
/**
 * Adds raw stylesheet to page object
 * @param string $style
 * @global $HTML
 */
function addPageCssStyle( string $style ) {
	global $HTML;
	$HTML->cssStyles[] = $style;
}
/**
 * Outputs additional page meta (head) tags if any has been set
 * @global $HTML
 */
function doPageMetaTags() {
	global $HTML;
	if( isset($HTML->metaTags[0]) )
		foreach( $HTML->metaTags as $entry )
			echo $entry;
}
/**
 * Loads javascript files in page
 * @global $HTML
 */
function doPageJsFiles() {
	global $HTML;
	if( isset($HTML->jsFiles[0]) )
		foreach( $HTML->jsFiles as $entry )
			echo '<script src="' . escHtml($entry) . '"></script>';
}
/**
 * Loads additional javascript raw code
 * @global $HTML
 */
function doPageJsCodes() {
	global $HTML;
	if( isset($HTML->jsCodes[0]) )
		array_walk( 'print', $HTML->jsCodes );
	if( function_exists('onPageJsCode') )
		onPageJsCode();
}
/**
 * Loads additional css stylesheet files if any has been set
 * @global $HTML
 */
function doPageCssFiles() {
	global $HTML;
	if( isset($HTML->cssFiles[0]) )
		foreach( $HTML->cssFiles as $entry )
			echo '<link rel="stylesheet" href="' . escHtml($entry) . '" />' . PHP_EOL;
}
/**
 * Outputs additional page stylesheet code if any has been set
 * @global $HTML
 */
function doPageCssTags() {
	global $HTML;
	if( isset($HTML->cssTags[0]) )
		foreach( $HTML->cssTags as $entry )
			echo '<style type="text/css">' . escHtml($entry) . '</style>' . PHP_EOL;
}
/**
 * Outputs a navigation breadcrumb using an array of links and a final text-only destination name
 * @global $HTML
 * @param array $theCrumb An array in a format [ [ 'Previous', '/page/to/previous'], 'Current' ]
 */
function BreadCrumb( array $theCrumb ) {
	echo '<ol class="breadcrumb">';
	foreach( $theCrumb as $entry )
		if( is_array($entry) )
			echo '<li><a href="', escHtml($entry[0]), '">', escHtml($entry[1]), '</a></li>';
		else
			echo '<li class="active" aria-current="page">', escHtml($entry), '</li>';
	echo '</ol>';
}
/**
 * Outputs a pagination list using a Paging object created, and a page URL
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
/**
 * Return a single class to validate user input
 * @return string
 */
function feedBack( bool $feedBack ) {
	return $feedBack ? ' is-valid' : ' is-invalid';
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
	elseif( false !== strpos( $mime, 'audio/' ) )
		$icon = 'music';
	elseif( false !== strpos( $mime, 'video/' ) )
		$icon = 'file-video-o';
	elseif( false !== strpos( $mime, '/zip' ) || false !== strpos( $mime, 'x-rar' ) )
		$icon = 'file-archive-o';
	elseif( false !== strpos( $mime, '/pdf' ) )
		$icon = 'file-pdf-o';
	elseif( false !== strpos( $mime, '/java-archive' ) || false !== strpos( $mime, 'x-rar' ) )
		$icon = 'android';
	elseif( preg_match('#^application/#i', $mime) )
		$icon = 'media-default';
	else
		$icon = '';
	return $icon;
}
function pageUrl( string $url, int $page ) : string {
	$page = 'page=' . $page;
	if( false !== strpos( $url, 'page=' ) )
		$url = preg_replace( '#page=[0-9]+?#i', $page, $url, 1, $tmp );
	elseif( false !== strpos( $url, '?' ) )
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
