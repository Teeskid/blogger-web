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
class Media extends Post {
	public static function getAvatar( string $size ) : string {
		$default = sprintf( '%s%sthumbnail-%s.png', BASEPATH, DIR_IMAGES, $size );
		return $default;
	}
	public static function getImage( $imageSrc, string $size ) : string {
		if( isset($imageSrc->images->$size) ) {
			$imageSrc = $imageSrc->images->$size;
			$imageSrc = BASEPATH . DIR_UPLOAD . $imageSrc->fileName;
		} else {
			$imageSrc = self::getAvatar($size);
		}
		return $imageSrc;
	}
	public static function formatSize( int $size ) : string {
		if( $size < 1024 )
			return $size . ' bytes';
		$kilos = floor( $size / 1024 );
		$bytes = floor( $size % 1024 );
		if( $kilos < 1024 ) {
			$kilos = sprintf( '%dkb', $kilos );
			return $kilos;
		}
		$mega = $kilos / 1024;
		$mega = sprintf( '%dMB', $mega );
		return $mega;
	}
}