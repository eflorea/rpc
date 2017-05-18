<?php

$dotenv = new \Dotenv\Dotenv( ROOT_PATH . '/config' );
$dotenv->load();

$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();

//set some default constants if they aren't defined
if( ! defined( 'APP_PATH' ) )
{
	define( 'APP_PATH', ROOT_PATH . '/APP' );
}

if( ! defined( 'CACHE_PATH' ) )
{
	define( 'CACHE_PATH', ROOT_PATH . '/tmp/cache' );
}

/**
 * Keeps history of queries in current call.
 * Can be referenced via $model->lastQuery() or via
 * $model->queries array
 */
define( 'DEBUG_QUERIES', getenv( 'DEBUG_QUERIES' ) );

?>
