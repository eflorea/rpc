<?php

$dotenv = new \Dotenv\Dotenv( ROOT_PATH . '/config' );
$dotenv->load();

$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();

//set some default constants if they aren't defined
if( ! defined( 'PATH_APP' ) )
{
	define( 'PATH_APP', PATH_ROOT . '/APP' );
}

if( ! defined( 'PATH_CACHE' ) )
{
	define( 'PATH_CACHE', PATH_ROOT . '/tmp/cache' );
}

/**
 * Keeps history of queries in current call.
 * Can be referenced via $model->lastQuery() or via
 * $model->queries array
 */
define( 'DEBUG_QUERIES', getenv( 'DEBUG_QUERIES' ) );

?>
