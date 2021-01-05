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
define( 'MINI_LOAD', false );
require( dirname(__FILE__) . '/Load.php' );
redirect( BASEPATH . '/404.php' );
showError( 'Object Not Found', 'The requested URL was not found on this server. If you entered the URL manually please check your spelling and try again.<br>If you think this is a server error, please contact the Site Admin' );