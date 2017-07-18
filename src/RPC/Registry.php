<?php

namespace RPC;



/**
 * Allows registering objects which can be accessed from anywhere in the
 * application
 * 
 * <code>
 * 
 * // boostrap file
 * 
 * RPC_Registry::set( 'mno', new MuchNeededObject() );
 * 
 * ...
 * 
 * // some controller file
 * 
 * $mno = RPC_Registry::get( 'mno' );
 * 
 * </code>
 * 
 * @package Core
 */
class Registry
{
	
	/**
	 * Array containing registered objects
	 * 
	 * @var array
	 */
	protected static $registry = array();
	
	/**
	 * Registers an object into a global namespace and returns the registry so
	 * you can add another object
	 * 
	 * @param string $name
	 * @param object $obj
	 * 
	 * @return RPC_Registry
	 */
	public static function set( $name, $obj )
	{		
		self::$registry[$name] = $obj;
		
		return $obj;
	}
	
	/**
	 * Fetches an object from the global namespace
	 * 
	 * @param string $name
	 * 
	 * @return object
	 */
	public static function get( $name )
	{
		if( ! self::registered( $name ) )
		{
			return null;
		}
		
		return self::$registry[$name];
	}
	
	/**
	 * Determines if a given key has been registered or if an object has been
	 * registered
	 * 
	 * @param mixed $name
	 * 
	 * @return bool
	 */
	public static function registered( $name )
	{
		return isset( self::$registry[$name] );
	}
	
}

?>
