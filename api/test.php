<?php
// require_once( __DIR__ . '/Load.php' );
die( json_encode( getallheaders()['Authorization'] ) );
