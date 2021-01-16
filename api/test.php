<?php
$input = fopen('php://input','r');
$stream = stream_get_contents( $input );
fclose($input);
echo $stream;