<?php

namespace RPC;

use PDO;
use RPC\Db\Adapter\MySQL;

/**
 * Static class meant as a factory for every connection in the project
 * 
 * @package Db
 */
class Db
{
	
	/**
	 * Columns are only numerically indexed
	 */
	const FETCH_NUM = \PDO::FETCH_NUM;
	
	/**
	 * Columns are mapped as associative keys of the rows
	 */
	const FETCH_ASSOC = \PDO::FETCH_ASSOC;
	
	/**
	 * Columns are mapped as properties of the rows
	 */
	const FETCH_OBJ = \PDO::FETCH_OBJ;
	
	/**
	 * Query is an insert statement
	 */
	const QUERY_INSERT = 'insert';
	
	/**
	 * Query is an update statement
	 */
	const QUERY_UPDATE = 'update';
	
	/**
	 * Array containing instances of database connections
	 * 
	 * @var array
	 */
	protected static $instances = array();
	
	/**
	 * Array containing the possible instatiable connections and their
	 * properties
	 * 
	 * @var array
	 */
	protected static $connections = array();
	
	/**
	 * Bidimensional array containing all the tables of each database
	 * 
	 * @var array
	 */
	protected static $tables = array();
	
	/**
	 * Represents the default connection
	 * 
	 * @var string
	 */
	protected static $defaultconnection = 'default';
	
	/**
	 * The class is a factory
	 * 
	 * @see self::factory()
	 */
	protected function __construct() {}
	
	/**
	 * Loads a connection given the database name
	 * 
	 * If nothing is given it will return the connection marked as default. If
	 * no connection is marked as default, it will return the first (this saves
	 * a few keystrokes in case there is only one connection).
	 * 
	 * @param mixed $db_name
	 * 
	 * @return RPC_Db_Adapter
	 */
	public static function factory( $connection = '' )
	{
		if( empty( self::$connections ) )
		{
			throw new \Exception( 'No connections loaded' );
		}
		
		/*
			If no parameter was given then the database name is set to the
			default database name
		*/
		if( ! $connection )
		{
			reset( self::$connections );
			$connection = key( self::$connections );
		}
		/*
			If the given connection has already been created, return it
		*/
		elseif( array_key_exists( $connection, self::$instances ) )
		{
			return self::$instances[$connection];
		}
		elseif( ! array_key_exists( $connection, self::$connections ) )
		{
			throw new \Exception( 'Connection ' . $connection . ' is not loaded' );
		}
		
		/*
			If the database connection has not already been created attempt
			to instantiate it
		*/
		$info = self::$connections[$connection];
		
		$tmp = '\RPC\Db\Adapter\\' . $info['adapter'];

		$database = new \RPC\Db\Adapter\MySQL( $info['hostname'],
		                      $info['database'],
		                      $info['socket'],
		                      $info['port'] );
/*
		$database = new $tmp( $info['hostname'],
		                      $info['database'],
		                      $info['socket'],
		                      $info['port'] );*/
		$database->connect( $info['username'], $info['password'] );
		$database->setPrefix( $info['prefix'] );
		
		self::$instances[$connection] = $database;
		
		return self::$instances[$connection];
	}
	
	/**
	 * Adds a database's configuration options
	 * 
	 * The $db_info array can have the following keys:
	 * <ul>
	 * 	<li>adapter</li>
	 * 	<li>hostname</li>
	 * 	<li>database</li>
	 * 	<li>socket</li>
	 * 	<li>port</li>
	 * 	<li>username</li>
	 * 	<li>password</li>
	 *  <li>prefix</li>
	 * </ul>
	 * 
	 * @param string $name Connection name
	 * @param array  $info DSN or array containing options
	 */
	public static function addConnection( $name, $info )
	{
		if( ! is_array( $info ) )
		{
			throw new \Exception( '$info should be an array or an array' );
		}
		
		if( empty( $name ) )
		{
			throw new \Exception( 'The configuration array should have a database name set' );
		}
		elseif( empty( $info['database'] ) )
		{
			throw new \Exception( 'The configuration array should have a database adapter set' );
		}
		
		self::$connections[$name] = $info;
	}
	
	/**
	 * Loads multiple connections from an array
	 * 
	 * @param array $connections
	 */
	public static function addConnections( $connections )
	{
		if( ! is_array( $connections ) )
		{
			throw new \Exception( 'You must pass an array of connections' );
		}
		
		foreach( $connections as $name => $info )
		{
			self::addConnection( $name, $info );
		}
	}
	
	/**
	 * Sets the default connetion name
	 * 
	 * @param string $name
	 * 
	 * @return self
	 */
	public static function setDefaultConnection( $name )
	{
		if( array_key_exists( $name, self::$connections ) )
		{
			self::$defaultconnection = $name;
		}
		else
		{
			throw new \Exception( 'Connection not loaded' );
		}
		
		return $this;
	}

	
}

?>
