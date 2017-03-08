<?php

namespace RPC\Loop\Row\Manipulator;

use ArrayIterator;

use RPC\Loop\Row\Manipulator;

/**
 * Class <code>RPC_Loop_RowManipulator</code> implements a watcher to be used
 * with class <code>RPC_Loop_RowManipulator</code>
 * 
 * This class is meant to be used only in cooperation with class 
 * <code>RPC_Loop_RowManipulator</code>. See that class for more details
 * 
 * @see RPC_Loop_RowManipulator
 */
class Watcher
{
	/**
	 * The manipulator this watcher is for
	 * 
	 * @var RPC_Loop_RowManipulator
	 */
	protected $manipulator = null;
	
	/**
	 * The column this watcher is watching
	 * 
	 * @var string
	 */
	protected $column = '';
	
	/**
	 * The list of methods to call on the manipulator when the watched column
	 * changes
	 * 
	 * @var array
	 */
	protected $methods = array();
	
	/**
	 * The last known value of the watched column
	 * 
	 * @var string
	 */
	protected $value;
	
	/**
	 * Construct a new watcher
	 * 
	 * @param $manipulator The loop manipulator this watcher is for
	 * @param $column      The column this watcher is for
	 */
	public function __construct( RPC\Loop\Row\Manipulator $manipulator, $column )
	{
		$this->manipulator = $manipulator;
		$this->column      = $column;
		unset( $this->value );
	}
	
	/**
	 * Register a method to call on the manipulator when the watched column
	 * changes. If the specified method is already registered, nothing happens
	 * 
	 * @param $method The method to call when the watched column changes
	 */
	public function register( $method )
	{
		if( ! in_array( $method, $this->methods ) )
		{
			array_push( $this->methods, $method );
		}
	}
	
	/**
	 * Unregister a method from this watcher. If the method wasn't registered in
	 * the first place, nothing happens
	 * 
	 * @param $method The method to remove from the list of registered methods
	 */
	public function unregister( $method )
	{
		if( ( $index = array_search( $method, $this->methods ) ) !== false )
		{
			array_splice( $this->methods, $index, 1 );
		}
	}
	
	/**
	 * Check a row and call all registered methods if the watched column has
	 * changed
	 * 
	 * @param $row   The row to check
	 * @param $index The index of the current row
	 */
	public function check( & $row, $index )
	{
		if( ! isset( $row[$this->column] ) )
		{
			return;
		}
		
		if( isset( $this->value ) &&
		    ( $row[$this->column] == $this->value ) )
		{
			return;
		}
		
		$it = new ArrayIterator( $this->methods );
		foreach( $this->methods as $method )
		{
			$this->manipulator->$method( $row, $index );
		}
		$this->value = $row[$this->column];
	}
	
}

?>
