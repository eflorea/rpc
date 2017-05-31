<?php

function RPC_Shutdown() { 
    echo 'Something went wrong. Our amazing team of developers have been notified. Please try again later.';
}
register_shutdown_function( 'RPC_Shutdown' );

$dotenv = new \Dotenv\Dotenv( ROOT_PATH . '/config' );
$dotenv->load();

error_reporting( E_ALL );
ini_set( 'display_errors', 0 );

if( getenv( "SHOW_ERRORS" ) === "true" )
{
	ini_set( 'display_errors', 1 );
	$whoops = new \Whoops\Run;
	$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
	$whoops->register();
}

//set some default constants if they aren't defined
if( ! defined( 'APP_PATH' ) )
{
	define( 'APP_PATH', ROOT_PATH . '/APP' );
}

if( ! defined( 'CACHE_PATH' ) )
{
	define( 'CACHE_PATH', ROOT_PATH . '/tmp/cache' );
}


?>
