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
define( 'REQUIRE_LOGIN', true );
require( dirname(__FILE__) . '/Load.php' );
require( ABSPATH . USER_UTIL . '/MediaUtil.php' );

noCacheHeaders();
mediaConstants();

$response = [];
$action = request( 'action', 'id' );
switch( $action->action ) {
	case 'unlink':
		$mediaList = $action->id;
		if( ! is_array($mediaList) )
			$mediaList = [ $mediaList ];
		$mediaList = array_map( 'parseInt', $mediaList );
		$mediaList = $db->quoteList( $mediaList );
		try {
			$db->beginTransaction();
			$mediaFiles = $db->prepare( 'SELECT metaValue FROM PostMeta WHERE postId IN (' . $mediaList . ') AND metaKey=?' );
			$mediaFiles->execute( [ 'media_metadata' ] );
			$mediaFiles = $mediaFiles->fetchAll( PDO::FETCH_COLUMN|PDO::FETCH_ASSOC );
			
			$db->exec( 'DELETE FROM Post WHERE id IN (' . $mediaList . ')' );

			$db->commit();
			
			foreach( $mediaFiles AS &$entry ) {
				$entry = json_decode($entry);
				if( ! isset($entry->fileName) )
					continue;
				$entry->fileName = ABSPATH . DIR_UPLOAD . $entry->fileName;
				if( file_exists($entry->fileName) )
					unlink($entry->fileName);
				if( isset($entry->images) && is_array($entry->images) ) {
					foreach( $entry->images as $subEntry ) {
						$subEntry = ABSPATH . DIR_UPLOAD . $subEntry;
						if( file_exists($subEntry) )
							unlink($subEntry);
					}
				}
				$entry = null;
			}
			$response['success'] = true;
			$response['message'] = 'Deleted succesfully.';
		} catch( Exception $e ) {
			if( $db->inTransaction() )
				$db->rollBack();
			$response['success'] = false;
			$response['message'] = $e->getMessage();
		}
		break;
	case 'modify':
		$action->id = (int) $action->id;
		$media = request( 'permalink', 'title' );
		if( ! $media->permalink )
			$media->permalink = makePermalink( $media->title );
		$valid = $error = [];
		if( ! preg_match( '#^[a-z0-9\s\._-]{5,50}$#i', $media->title ) ) {
			$valid['title'] = false;
			$error[] = 'invalid / too short title';
		}
		if( ! preg_match( '#^[a-z0-9-_]{3,32}$#i', $media->permalink ) ) {
			$valid['fileName'] = false;
			$error[] = 'invalid file name';
		}
		
		if( ! isset($error[0]) )
		try {
			$db->beginTransaction();
			$tempId = Media::findId( $media->permalink );
			if( $tempId && $tempId !== $action->id )
				throw new Exception( 'Duplicate permalink' );
			$insert = $db->prepare( 'UPDATE Post SET permalink=?,title=?,modified=DATE(?) WHERE subject=? AND id=? LIMIT 1' );
			$insert->execute( [ $media->permalink, $media->title, time(), 'media', $action->id ] );
			$db->commit();
		} catch(Exception $e) {
			if( $db->inTransaction() )
				$db->rollBack();
			$error[] =  $e->getMessage();
		}
		$response['uiValid'] = $valid;
		if( isset($error[0]) ) {
			$response['success'] = false;
			$response['message'] = implode( PHP_EOL, $error );
		} else {
			$response['success'] = true;
		}
		unset( $error, $valid );
		break;
	case 'upload':
		$uploads = request( 'files' );
		if( ! isset($uploads[0]) )
			die();
		foreach( $uploads as $index => &$entry ) {
			if( $entry['error'] ) {
				$error[] = fileShortName( $entry['name'] ) . ' not uploaded' );
				continue;
			}
			$error = [];
			$srcName = $entry['name'];
			$srcFile = $entry['tmp_name'];
			$srcMime = mime_content_type($srcFile);
			$srcSize = parseInt( $entry['size'] );
			if( ! preg_match( '#^[a-z0-9-_]{3,32}$#i', $srcName ) )
				$error[] = 'invalid file name: ' . fileShortName( $entry['name'] );
			if( $srcSize > MAX_FILE_SIZE || $srcSize === 0 )
				$error[] = fileShortName( $entry['name'] ) . ' has invalid size';
			if( ! parseFileName( $srcName, $srcBody, $mFormat ) || ! in_array( $mFormat, MEDIA_FORMATS ) )
				$error[] = fileShortName( $entry['name'] ) . ' has unsupported format: ' . $mFormat;
			if( ! empty($error) )
			$mUpload = new Media();
			$mUpload->title = $srcBody;
			
			$srcBody = makeNameBody( $srcBody );
			$srcName = $srcBody . '.' . $mFormat;
			$dstFile = ABSPATH . DIR_UPLOAD . $srcName;
			
			$mUpload->permalink = makePermalink( $srcBody );
			$mUpload->mimeType = $srcMime;
			try {

				 if( file_exists($dstFile) )
				 	throw new Exception( fileShortName( $entry['name'] ) . ' is already uploaded', ERR_PRE_UPLOAD );
				if( ! move_uploaded_file( $srcFile, $dstFile ) || ! file_exists($dstFile) )
					throw new Exception( fileShortName( $entry['name'] ) . ' not uploaded, unknown error' );
				$srcFile = $dstFile;
				
				$mUpload->meta = [ 'fileName' => $srcName, 'fileSize' => $srcSize, 'format' => $mFormat ];
				
				if( $mFormat === 'mp3' ) {
					$mUpload->mimeType = 'audio/mpeg';
				}
				if( $mFormat === 'apk' ) {
					$mUpload->mimeType = 'application/java-archive';

					$aParser = new \ApkParser\Parser( $srcFile );
					$appInfo = $aParser->getManifest();
					$appData = $appInfo->getApplication();
					$appName = $aParser->getResources( $appData->getLabel() );
					$appName = $appName[0];

					$mUpload->meta['appName'] = $appName;
					$mUpload->meta['package'] = $appInfo->getPackageName();
					$mUpload->meta['versionName'] = $appInfo->getVersionName();
					$mUpload->meta['versionCode'] = $appInfo->getVersionCode();

					$appIcon = $aParser->getResources( $appData->getIcon() );
					$appIcon = $appIcon[count($appIcon)-1] ?? null;
					if( $appIcon ) {
						$dstBody = $srcBody;
						$appIcon = stream_get_contents($aParser->getStream( $appIcon ));
						$appIcon = imagecreatefromstring($appIcon);
						if( ! empty($appIcon) ) {
							$imageSX = (int) imagesx($appIcon);
							$imageSY = (int) imagesy($appIcon);
							$imRatio = $imageSX / $imageSY;
							$measure = [ 'width' => $imageSX, 'height' => $imageSY, 'aspect' => $imRatio ];
							if( $tmpData = createThumbnail( $appIcon, $dstBody, $measure ) ) {
								$mUpload->meta['images'] = $tmpData;
								unset( $measure, $tmpData );
							}
							if( $imageSX > 150 || $imageSY > 150 ) {
								$imageSX = $imageSY = 150;
								$appIcon = imagescale( $appIcon, $imageSX, $imageSY );
							}
							$dstName = $dstBody . '.jpg';
							$dstFile = ABSPATH . DIR_UPLOAD . $dstName;
							imagejpeg( $appIcon, $dstFile, 100 );
							imagedestroy($appIcon);
							$mUpload->meta['appIcon'] = [ 'name' => $dstName, 'width' => $imageSX, 'height' => $imageSY, 'mimeType' => 'image/jpeg' ];
						}
						unset( $dstBody, $dstName, $dstFile, $imageSX, $imageSY, $imRatio );
					}
					unset( $aParser, $appInfo, $appData, $appName, $appIcon );
				}
				if( isImage( $mFormat ) ) {
					if( createGDImage( $srcFile, $mFormat, $gdImage ) ) {
						$imageSX = (int) imagesx($gdImage);
						$imageSY = (int) imagesy($gdImage);
						$imRatio = $imageSX / $imageSY;
						$measure = [ 'width' => $imageSX, 'height' => $imageSY, 'aspect' => $imRatio ];
						if( $tmpData = createThumbnail( $gdImage, $srcBody, $measure ) ) {
							$mUpload->meta['images'] = $tmpData;
							unset($tmpData);
						}
						/*
						if( $imageSX > 640 ) {
							$imageSX = 640;
							$imageSY = $imageSX / $imRatio;
							$gdImage = imagescale( $gdImage, $imageSX, $imageSY );
						}
						imagejpeg( $gdImage, $srcFile, 100 );
						*/
						imagedestroy($gdImage);
						$mUpload->meta['width'] = $imageSX;
						$mUpload->meta['height'] = $imageSY;
						$mUpload->meta['aspect'] = $imRatio;
						unset( $imageSX, $imageSY, $imRatio, $measure, $tmpData );
					}
					unset($gdImage);
				}
				$mUpload->meta = json_encode($mUpload->meta);
				$mUpload->uploaded = $mUpload->modified = time();
				try {
					$db->beginTransaction();
					if( false && Post::findId( $mUpload->permalink ) )
						throw new Exception( '"%1s" already exists' );
					$insert = $db->prepare( 'REPLACE INTO Post (author, title, permalink, mimeType, posted, modified, subject, status) VALUES (?,?,?,?,DATE(?),DATE(?),?,?)' );
					$insert->execute( [ $_login->userId, $mUpload->title, $mUpload->permalink, $mUpload->mimeType, $mUpload->uploaded, $mUpload->modified, 'media', 'public' ] );
					$mUpload->id = parseInt( $db->lastInsertId() );
					
					$insert = $db->prepare( 'REPLACE INTO PostMeta (postId, metaKey, metaValue) VALUES (?,?,?)' );
					$insert->execute( [ $mUpload->id, 'media_metadata', $mUpload->meta ] );
					$db->commit();
					
					$entry = $mUpload->id;
				} catch( Exception $e ) {
					if( $db->inTransaction() )
						$db->rollBack();
					throw $e;
				}
			} catch( Exception $e ) {
				$response['message'] = $e->getMessage();
				if( $e->getCode() !== ERR_PRE_UPLOAD ) {
					$srcFile = glob( ABSPATH . DIR_UPLOAD . $srcBody . '*' );
					foreach( $srcFile as $tmp ) {
						@unlink($tmp);
					}
				}
				$entry = 0;
			}
			unset( $mUpload, $dstFile, $dstName, $dstBody, $srcSize, $srcMime, $srcFile, $srcName, $srcBody, $insert );
		}
		$allFiles = count($uploads);
		$numFiles = array_filter( $uploads, 'notEmpty' );
		$numFiles = count($numFiles);
		$errFiles = $allFiles - $numFiles;
		$response['success'] = $errFiles === 0;
		$response['message'] = $response['message'] ?? '';
		$response['message'] = sprintf( '%d/%d uploaded succesfully. %s', $numFiles, $allFiles, $response['message'] );
		break;
	default:
		die();
}
jsonOutput( $response );