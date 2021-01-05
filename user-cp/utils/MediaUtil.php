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
function mediaConstants() {
	define( 'FORMAT_IMAGE', ['jpg','png','gif','bmp'] );
	define( 'FORMAT_AUDIO', ['mp3','wav','ogg','wav','amr','aac'] );
	define( 'MEDIA_FORMATS', [ 'jpg','png','gif','bmp','mp3','wav','ogg','wav','amr','aac','mp3','mp4','flv','3gpp','3gp','mkv','apk','zip','rar','iso','jar','pdf','docx','csv' ] );
	define( 'MAX_FILE_SIZE', 1024 * 1024 * 1024 * 100 );
	define( 'ERR_PRE_UPLOAD', 0x1 );
	define( 'ERR_POST_UPLOAD', 0x2 );
}
function parseFileName( string $fileName, &$nameBody, &$extension ) : bool {
	if( preg_match( '#^(.+)\.([a-z0-9]+)$#i', $fileName, $match ) ) {
		$nameBody = $match[1];
		$extension = strtolower($match[2]);
		return true;
	}
	return false;
}
function fileShortName( string $fileName ) : string {
	$fileName = substr( $fileName, 0, 7 );
	$fileName = str_pad( $fileName, 10, '.' );
	return $fileName;
}
function createGDImage( string $srcFile, string $format, &$gdImage ) : bool {
	if( $format === 'png' )
			$gdImage = imagecreatefrompng($srcFile);
	else if( $format === 'jpg' )
		$gdImage = imagecreatefromjpeg($srcFile);
	else
		$gdImage = null;
	if( is_resource($gdImage) || is_object($gdImage) )
		return true;
	return false;
}
function saveGDImage( string $srcFile, string $mimeType, &$gdImage ) : bool {
	$gdImage = str_replace( 'image/', 'image', $mimeType );
	if( ! function_exists($gdImage) )
		return false;
	$gdImage = $gdImage( $srcFile );
	return true;
}
function isImage( string $ext ) : bool {
	if( in_array( $ext, FORMAT_IMAGE ) )
		return true;
	return false;
}
function is_audio($ext) : bool {
	if( in_array($ext, FORMAT_AUDIO) )
		return true;
	return false;
}
function is_video($ext) : bool {
	if( in_array($ext, FORMAT_VIDEO) )
		return true;
	return false;
}
function is_archive( string $ext ) : bool {
	if( in_array($ext, FORMAT_ARCHIVE) )
		return true;
	return false;
}
function createThumbnail( $gdImage, string $srcBody, array $measure ) : array {
	$imageSX = $measure['width'];
	$imageSY = $measure['height'];
	$imRatio = $measure['aspect'];
	// imagefilledrectangle( $gdImage, 5, 5, (imagefontwidth(5) * 5) + 2, (imagefontheight(5) * 1) + 2, imagecolorallocate($gdImage, 255, 255, 255) );
	$dimens = [ 'small' => [72, 72], 'medium' => [150, 150], 'large' => [240, 160] ];
	foreach( $dimens as $index => &$dimen ) {
		$imageDX = (int) $dimen[0] ?? 0;
		$imageDY = (int) $dimen[1] ?? 0;
		if( $imageDX > $imageSX || $imageDY > $imageSY ) {
			$dimen = null;
			continue;
		}
		if ( $imRatio >= ( $imageDX / $imageDY ) ) {
			// If image is wider than dimens (in aspect ratio sense)
			$imageTY = $imageDY;
			$imageTX = $imageSX / ( $imageSY / $imageDY );
		} else {
			// If the dimens is wider than the image
			$imageTX = $imageDX;
			$imageTY = $imageSY / ( $imageSX / $imageDX );
		}
		$imageCX = ( $imageTX - $imageDX ) / 2;
		$imageCY = ( $imageTY - $imageDY ) / 2;
		$imageGD = imagescale( $gdImage, $imageTX, $imageTY );
		$imageGD = imagecrop( $imageGD, [ 'x'=> $imageCX, 'y' => $imageCY, 'width' => $imageDX, 'height' => $imageDY ] );
		$imageGD = imagescale( $imageGD, $imageDX, $imageDY );
		$srcName = sprintf( '%s_%sx%s.jpg', $srcBody, $imageDX, $imageDY );
		$srcFile = ABSPATH . DIR_UPLOAD . $srcName;
		imagejpeg( $imageGD, $srcFile, 100 );
		imagedestroy( $imageGD );
		$dimen = [ 'fileName' => $srcName, 'width' => $imageDX, 'height' => $imageDY, 'mimeType' => 'image/jpeg' ];
	}
	$dimens = array_filter( $dimens, 'notEmpty' );
	return $dimens;
}