<?php
/**
 * Blog Cache Loader
 * 
 * Loads request cache for only GET requests and non-logged users
 * 
 * @package Sevida
 */
// Check login status and request method; we are not caching for POST and rest
if( 'GET' === $_SERVER['REQUEST_METHOD'] && ! LOGGED_IN ) {
    /**
     * @var string $cacheKey Unique cache file name generated using the whole request url
     */
	$cacheKey = ABSPATH . DIR_CACHES . md5($_SERVER['REQUEST_URI']) . '.html';
	if( file_exists($cacheKey) ) {
        /**
         * @var string $cacheUse When the cache was created
         */
        $cacheUse = time() - filemtime($cacheKey);
        // Reject cache if older than n-seconds
		if( $cacheUse <= 10 /*86400*/ ) {
			readfile($cacheKey);
			exit;
		} else {
            // Clear the cache entry
			unlink($cacheKey);
			unset($cacheKey);
		}
	}
}
