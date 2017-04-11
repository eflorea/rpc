<?php

namespace RPC\Db;

use RPC\Db;
use RPC\Db\Table\Adapter\MySQL;
use Exception;

class Migration
{
	
	protected $model;

	public function construct()
	{
		$this->model = new \RPC\Db\Table\Adapter\MySQL();
	}


	public function run()
	{
		echo "Initializing migration...\n";
		$model = new  \RPC\Db\Table\Adapter\MySQL( 'system' );
		$db_scheme = $model->find( 'name', 'db_scheme' );

		if( ! $db_scheme )
		{
			$db_scheme = $model->create();
			$db_scheme->name( 'db_scheme' );
			$db_scheme->value( '0' );
			$db_scheme->save();
		}

		if( ! defined( 'MIGRATION_FILES_PATH' ) )
		{
			throw new \Exception( "MIGRATION_FILES_PATH is not defined" );
			
		}

		echo "Current db_scheme number: " . $db_scheme->value() . "\n";

		$files = scandir( MIGRATION_FILES_PATH . '/', SCANDIR_SORT_ASCENDING );

		if( $files )
		{
			foreach( $files as $file )
			{
				if( preg_match( '/^(.*)?_([0-9]+)\.php/', $file, $matches ) )
				{
					if( isset( $matches[2] ) &&
						(int)$matches[2] > (int)$db_scheme->value() )
					{
						require_once MIGRATION_FILES_PATH . '/' . $file;

						echo "Run migration file: $file";
						$class = 'Migrate_' . $matches[2];
						$class = new $class();
						$class->run();
						echo " - Done\n";
						$db_scheme->value( $matches[2] );
					}
				}
			}
		}
		$db_scheme->save();
		echo "Done running migration. New db_scheme number is: " . $db_scheme->value() . "\n";
	}
}

?>
