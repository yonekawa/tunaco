<?php

require_once 'common.inc.php';

date_default_timezone_set( 'Asia/Tokyo' );

$lib = dirname( __FILE__ ) . '/lib';
$pear = dirname( __FILE__ ) . '/lib/PEAR';
$tunacoLib = dirname( __FILE__ ) . '/lib/Tunaco';
set_include_path( get_include_path() . PATH_SEPARATOR . $tunacoLib );
set_include_path( get_include_path() . PATH_SEPARATOR . $pear );
set_include_path( get_include_path() . PATH_SEPARATOR . $lib );

require_once 'TwitterBot.class.php';
$tunaco = new TwitterBot( 'tunaco' );
$response = $tunaco->reply();

var_dump( $response );
