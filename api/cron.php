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
require_once dirname(__DIR__).'/ini.php';

require_once ABSCPATH.'/monitor.php';

define('ACTION', isset($_REQUEST['action']) ? $_REQUEST['action'] : null);

switch(ACTION)
{
	case 'ping-sitemap':
		$res = null;
		set_error_handler(function($code, $error) use(&$res){
			$res = $error;
		});
		$bool = file_get_contents(sprintf('https://google.com?sitemap=%s/sitemap/', _v('url')));
		if($bool) {
			echo 'Success !';
		}
		else {
			echo 'Network Error: '.$res;
		}
	break;
	case 'clear-cache':
		$FILES = glob(PATH_CACHE.'*.*');
		$FILES = array_map('unlink', $FILES);
		$FILES = glob(PATH_UPLOAD.'*.tmp');
		$FILES = array_map('unlink', $FILES);
		die(json_encode(['msg'=>'Cache Clean Success !']));
	break;
	default:
		$res = [];
		set_error_handler(function($code, $error) use(&$res){
			$res[] = $error;
		});
		$res[] = file_get_contents(_v('url').$_SERVER['PHP_SELF'].'?action=ping-sitemap');
		$res[] = file_get_contents(_v('url').$_SERVER['PHP_SELF'].'?action=clear-cache');
		$res = implode("\n", ($res));
		die(json_encode(['msg'=>'Success !']));
	break;
}
