<?php

namespace RPC\Db\Table;

use Countable;
use ArrayAccess;

use RPC\Db;
use RPC\Db\Table\Row;

/**
 * Base class for all model classes, representing a table
 * 
 * @package Db
 */
abstract class Adapter implements Countable
{
	
	/**
	 * Represents a insert query
	 */
	const QUERY_INSERT = 'insert';
	
	/**
	 * Represents a update query
	 */
	const QUERY_UPDATE = 'update';
	
	/**
	 * Represents a delete query
	 */
	const QUERY_DELETE = 'delete';
	
	/**
	 * Table's database object
	 * 
	 * @var RPC_Db_Adapter
	 */
	protected $db = null;
	
	/**
	 * Table's name
	 * 
	 * @var string
	 */
	protected $name = '';

	/**
	 * Force table's name
	 */
	public $force_table_name = '';
	
	/**
	 * Row object to be returned by load
	 * 
	 * @var string
	 */
	protected $rowclass = 'RPC\Db\Table\Row';
	
	/**
	 * Table's primary key column's name
	 * 
	 * @var string
	 */
	protected $pk = '';
	
	/**
	 * Array containing the fields
	 * 
	 * @var array
	 */
	protected $fields = array();
	
	/**
	 * Array containing the "cleaned" fields. These are without any table
	 * prefix and with any _ removed
	 * 
	 * @var array
	 */
	protected $cleanfields = array();
	
	/**
	 * Identity map for loaded rows from the table
	 * 
	 * @var RPC_Db_Table_RowMap
	 */
	protected $map = null;
	
	/**
	 * Finds out what fields the table has and puts them in the $fields array.
	 * Also the fields are "cleaned" and put in the $cleanfields array, which
	 * is useful for the getBy* and deleteBy* methods.
	 */
	abstract protected function loadFields();
	
	/**
	 * Returns an array with all the table's rows
	 * 
	 * @return array
	 */
	abstract public function getAll();
	
	/**
	 * Returns all the rows which have the given $field equal to $value
	 * 
	 * @param string $field
	 * @param string $value
	 * 
	 * @return array
	 */
	abstract public function getBy( $field, $value );
	
	/**
	 * Returns one row (the first in case there are more) which has the given
	 * $field equal to $value. If no row is found, returns null
	 * 
	 * @param string $field
	 * @param string $value
	 * 
	 * @return RPC_Db_Table_Row
	 */
	abstract public function loadBy( $field, $value );
	
	/**
	 * Returns all rows which has the given
	 * $field equal to $value. If no row is found, returns null
	 * 
	 * @param string $field
	 * @param string $value
	 * 
	 * @return RPC_Db_Table_Row
	 */
	abstract public function loadAllBy( $field, $value );
	
	/**
	 * Returns one row (the first in case there are more) which is returned by the query on the model's table. If no row is found, returns null
	 * 
	 * @param string $condition_sql
	 * @param array $condition_values
	 * 
	 * @return RPC_Db_Table_Row
	 */
	abstract public function loadBySql( $condition_sql, $condition_values );

	/**
	 * Returns all rows returned by the query on the model's table. If no row is found, returns null
	 * 
	 * @param string $condition_sql
	 * @param array $condition_values
	 * 
	 * @return RPC_Db_Table_Row
	 */
	abstract public function loadAllBySql( $condition_sql, $condition_values );
	
	/**
	 * Returns all rows returned by the custom query. If no row is found, returns null
	 * 
	 * @param string $condition_sql
	 * @param array $condition_values
	 * 
	 * @return RPC_Db_Table_Row
	 */
	abstract public function loadAllByCustomSql( $condition_sql, $condition_values );
	
	/**
	 * Removes the rows which have the $field = $value
	 * 
	 * @param int $field
	 * @param mixed $value
	 * 
	 * @return int Number of affected rows
	 */
	abstract public function deleteBy( $field, $value );
	
	/**
	 * Performs an insert given the supplied data
	 * 
	 * @param array $array
	 * 
	 * @return bool
	 */
	abstract protected function insertRow( RPC_Db_Table_Row $row );
	
	/**
	 * Performs an update given the supplied data
	 * 
	 * @param array $array
	 * 
	 * @return RPC_Db_Table_Row
	 */
	abstract protected function updateRow( RPC_Db_Table_Row $row );
	
	/**
	 * Deletes all records in a table;
	 * 
	 * @return int Number of affected rows
	 */
	abstract public function deleteAll();
	
	/**
	 * Deletes the table and recreates it
	 * 
	 * @return bool
	 */
	abstract public function truncate();
	
	/**
	 * Should return the number of rows in the table
	 * 
	 * @return int
	 */
	abstract public function countRows();
	
	/**
	 * Checks to see if the given value exists in the current table on the given
	 * column
	 * 
	 * @param string $column
	 * @param string $value
	 * 
	 * @return bool
	 */
	abstract public function exists( $value, $column );
	
	/**
	 * Checks to see if a value in a row is unique throught the table
	 * 
	 * @param string           $column Column on which to search
	 * @param RPC_Db_Table_Row $row    Row which holds the value
	 * 
	 * @return bool
	 */
	abstract public function unique( $column, RPC\Db\Table\Row $row );
	
	/**
	 * Locks a table
	 * 
	 * @return bool
	 */
	abstract public function lock();
	
	/**
	 * Unlocks the table
	 * 
	 * @return bool
	 */
	abstract public function unlock();
	
	/**
	 * Initializes the table based on two conventions:
	 * - object name will be: <table_name>Model
	 * - table primary key will be: <table_name>_id
	 */
	public function __construct()
	{
		$this->setDb( RPC\Db::factory() );
		
		if( $this->getName() )
		{
			$name = $this->getName();
		}
		else
		{
			$classname = get_class( $this );
			$name = $this->force_table_name ? $this->force_table_name : str_replace( 'model', '', strtolower( $classname ) );
			$this->setName( $this->getDb()->getPrefix() . $name );
		
			$classrow = str_replace( 'Model', '', $classname ) . 'Row';
		
			if( class_exists( $classrow, false ) )
			{
				$this->rowclass = $classrow; 
			}
		}
		
		
		
		$this->setPkField( substr( $this->getName(), strlen( $this->getDb()->getPrefix() ) ) . '_id' );
		
		$this->loadFields();
		
		$this->setIdentityMap( new RPC\Db\Table\Row\Map() );
	}
	
	/**
	 * Sets the parent database connection
	 * 
	 * @param RPC_Db_Adapter $database
	 */
	protected function setDb( RPC\Db\Adapter $db )
	{
		$this->db = $db;
	}
	
	/**
	 * Get the table's database connection
	 * 
	 * @return RPC_Db_Adapter
	 */
	public function getDb()
	{
		return $this->db;
	}
	
	/**
	 * Sets the table name
	 * 
	 * @param string $name
	 */
	public function setName( $name )
	{
		$this->name = $name;
	}
	
	/**
	 * Get the table's name
	 * 
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}
	
	/**
	 * Sets an identity map for this table
	 * 
	 * @param RPC_Db_Table_RowMap $map
	 */
	public function setIdentityMap( RPC\Db\Table\Row\Map $map )
	{
		$this->map = $map;
	}
	
	/**
	 * Returns the table's identity map
	 * 
	 * @return RPC_Db_Table_RowMap
	 */
	public function getIdentityMap()
	{
		return $this->map;
	}
	
	/**
	 * Implements Countable interface
	 * 
	 * @return int
	 */
	public function count()
	{
		return $this->countRows();
	}
	
	/**
	 * Magic method to allow for cleaner access to the (get|delete|load)By*
	 * methods
	 * 
	 * @param string $method
	 * @param array  $args
	 * 
	 * @return mixed
	 */
	public function __call( $method, $args )
	{
		if( strpos( $method, 'getBy' ) === 0 )
		{
			$field = substr( $method, 5 );
			return $this->getBy( $field, $args[0] );
		}
		elseif( strpos( $method, 'deleteBy' ) === 0 )
		{
			$field = substr( $method, 8 );
			return $this->deleteBy( $field, $args[0] );
		}
		elseif( strpos( $method, 'loadBy' ) === 0 )
		{
			$field = substr( $method, 6 );
			return $this->loadBy( $field, $args[0] );
		}
		else
		{
			throw new \Exception( 'Method "' . $method . '" is not implemented' );
		}
	}
	
	/**
	 * Set the table's primary key
	 * 
	 * @param string $pk
	 * 
	 * @return RPC_Db_Table_Adapter
	 */
	public function setPkField( $pk )
	{
		$this->pk = $pk;
		return $this;
	}
	
	/**
	 * Get the table's primary key
	 * 
	 * @return string
	 */
	public function getPkField()
	{
		return $this->pk;
	}
	
	/**
	 * Create a new, empty row object
	 * 
	 * @param array $data
	 * 
	 * @return RPC_Db_Table_Row
	 */
	public function create( $data = array() )
	{
		if( is_array( $data ) ||
		        ( is_object( $data ) &&
		          $data instanceof ArrayAccess ) )
		{
			/*
				We build an array of fields, filling the fields found in the
				array with the corresponding values and nulling the rest
			*/
			$tmp = array();
			foreach( $this->getFields() as $k => $field )
			{
				if( isset( $data[$field] ) &&
				    ! is_array( $data[$field] ) &&
				    ! is_object( $data[$field] ) )
				{
					$tmp[$field] = $hash->$field;
				}
				else
				{
					$tmp[$field] = null;
				}
				
				$tmp[$this->getPkField()] = null;
			}
		}
		else
		{
			throw new \Exception( 'If given, data must be an array' );
		}
		
		return new $this->rowclass( $this, $tmp );
	}
	
	/**
	 * Loads a row from the table or from the identity map if it has already
	 * been loaded. If no row is found, returns null
	 * 
	 * @link http://www.martinfowler.com/eaaCatalog/identityMap.html
	 * 
	 * @param int $pk
	 * 
	 * @return RPC_Db_Table_Row
	 */
	public function load( $pk )
	{
		if( ! $pk )
		{
			return null;
		}
		
		/*
			First I check to see if the row hasn't already been loaded in the
			identity map so that I won't query the database again
		*/
		if( $row = $this->getIdentityMap()->get( $pk ) )
		{
			return $row;
		}
		
		/*
			If it hasn't been loaded yet, I will query the database, and if a
			row is found it is added to the map
		*/
		$row = $this->loadBy( $this->getPkField(), $pk );
		if( is_object( $row ) )
		{
			$this->getIdentityMap()->add( $row );
		}
		
		return $row;
	}
	
	/**
	 * Returns an Row object from an array (the array must have all row
	 * data, including the pk). This is useful, for example, when selecting
	 * multiple rows with a query and then creating new objects from each row
	 * 
	 * @param array $array
	 * 
	 * @return RPC_Db_Table_Row
	 */
	public function loadFromArray( $array ) /* {{{ */
	{
		$pk = $array[$this->getPkField()];
		
		if( empty( $pk ) )
		{
			throw new \Exception( 'The array must contain the primary key' );
		}
		
		if( $row = $this->getIdentityMap()->get( $pk ) )
		{
			return $row;
		}
		
		$row = new $this->rowclass( $this, $array );
		$this->getIdentityMap()->add( $row );
		
		return $row;
	}
	/* }}} */
	
	/**
	 * Get the table's fields
	 * 
	 * @return array
	 * 
	 * @todo Create a standard value object / structure for fields
	 */
	public function getFields()
	{
		return $this->fields;
	}
	
	/**
	 * Returns the table's cleaned fields
	 * 
	 * @return array
	 */
	public function getCleanFields()
	{
		return $this->cleanfields;
	}
	
	/**
	 * Checks to see if a certain row validates
	 * 
	 * @param RPC_Db_Table_Row $row
	 * 
	 * @return bool
	 */
	public function validate( RPC\Db\Table\Row $row )
	{
		$pk = $row->getPk();
		$operation = empty( $pk ) ? RPC_Db::QUERY_INSERT : RPC_Db::QUERY_UPDATE;
		
		if( method_exists( $this, 'preValidate' ) )
		{
			$this->preValidate( $row, $operation );
		}
		
		/**
		 * For each table field, if a method named validate_fieldname exists it
		 * is run with the $row and operation type (insert/update)
		 */
		foreach( $this->getCleanFields() as $column => $field )
		{
			$method = 'validate_' . $column;
			
			if( method_exists( $this, $method ) )
			{
				$this->$method( $row, $operation );
			}
		}
		
		if( method_exists( $this, 'postValidate' ) )
		{
			$this->postValidate( $row, $operation );
		}
		
		return ! (bool) $row->hasErrors();
	}
	
	/**
	 * Creates a prepared statement for insertion in the table of a given
	 * RPC_Db_Table_Row
	 * 
	 * @param RPC_Db_Table_Row $row
	 * 
	 * @return bool
	 */
	public function insert( RPC_Db_Table_Row $row )
	{
		$this->getDb()->beginTransaction();
		
		if( ! $this->onBeforeSave( $row, RPC_Db::QUERY_INSERT ) )
		{
			$this->getDb()->rollback();
			return false;
		}
		
		if( ! $this->onBeforeInsert( $row ) )
		{
			$this->getDb()->rollback();
			return false;
		}
		
		$this->insertRow( $row );
		
		$pk = $this->getPkField();
		if( isset( $row->force_pk ) && $row->force_pk )
		{
			$row->force_pk = null;
		}
		else
		{
			$row->setPk( $this->getDb()->getLastId() );
		}

		if( ! $this->onAfterInsert( $row ) )
		{
			$this->getDb()->rollback();
			return false;
		}
		
		if( ! $this->onAfterSave( $row, RPC_Db::QUERY_INSERT ) )
		{
			$this->getDb()->rollback();
			return false;
		}
		
		$this->getDb()->commit();
		$this->getIdentityMap()->add( $row );
		
		return true;
	}
	
	/**
	 * Hook called before executing an insert query. If it returns false the
	 * transaction will be rolled back
	 * 
	 * @param RPC_Db_Table_Row $row
	 * 
	 * @return bool
	 */
	public function onBeforeInsert( $row )
	{
		return true;
	}
	
	/**
	 * Hook called after executing an insert query. If it returns false the
	 * transaction will be rolled back
	 * 
	 * @param RPC_Db_Table_Row $row
	 * 
	 * @return bool
	 */
	public function onAfterInsert( $row )
	{
		return true;
	}
	
	/**
	 * Creates a prepared statement for update in the table the given row
	 * identified by it's primary key
	 * 
	 * @param RPC_Db_Table_Row $row
	 * 
	 * @return int Number of affected rows
	 */
	public function update( $row )
	{
		$this->getDb()->beginTransaction();
		
		if( ! $this->onBeforeSave( $row, RPC\Db::QUERY_UPDATE ) )
		{
			$this->getDb()->rollback();
			return false;
		}
		
		if( ! $this->onBeforeUpdate( $row ) )
		{
			$this->getDb()->rollback();
			return false;
		}
		
		try
		{
			$this->updateRow( $row );
		}
		catch( Exception $e )
		{
			$this->getDb()->rollback();
			return false;
		}
		
		if( ! $this->onAfterUpdate( $row ) )
		{
			$this->getDb()->rollback();
			return false;
		}
		
		if( ! $this->onAfterSave( $row, RPC\Db::QUERY_UPDATE ) )
		{
			$this->getDb()->rollback();
			return false;
		}
		
		$this->getDb()->commit();
		
		return true;
	}
	
	/**
	 * Hook called before executing an update query. If it returns false the
	 * transaction will be rolled back
	 * 
	 * @param RPC_Db_Table_Row $row
	 * 
	 * @return bool
	 */
	public function onBeforeUpdate( $row )
	{
		return true;
	}
	
	/**
	 * Hook called after executing an update query. If it returns false the
	 * transaction will be rolled back
	 * 
	 * @param RPC_Db_Table_Row $row
	 * 
	 * @return bool
	 */
	public function onAfterUpdate( $row )
	{
		return true;
	}
	
	public function onBeforeSave( $row, $op )
	{
		return true;
	}
	
	public function onAfterSave( $row, $op )
	{
		return true;
	}
	
	/**
	 * Delets the given row
	 * 
	 * @param RPC_Db_Table_Row $row
	 * 
	 * @return bool
	 */
	public function delete( $row )
	{
		$this->getDb()->beginTransaction();
		
		if( ! $this->onBeforeDelete( $row ) )
		{
			$this->getDb()->rollback();
			return false;
		}
		
		if( ! (bool) $this->deleteBy( $this->getPkField(), $row->getPk() ) )
		{
			$this->getDb()->rollback();
			return false;
		}
		
		if( ! $this->onAfterDelete( $row ) )
		{
			$this->getDb()->rollback();
			return false;
		}
		
		$this->getDb()->commit();
		$this->getIdentityMap()->remove( $row );
		
		return true;
	}
	
	/**
	 * Hook called before executing a delete query. If it returns false the
	 * transaction will be rolled back
	 * 
	 * @param RPC_Db_Table_Row $row
	 * 
	 * @return bool
	 */
	public function onBeforeDelete( $row )
	{
		return true;
	}
	
	/**
	 * Hook called after executing a delete query. If it returns false the
	 * transaction will be rolled back
	 * 
	 * @param RPC_Db_Table_Row $row
	 * 
	 * @return bool
	 */
	public function onAfterDelete( $row )
	{
		return true;
	}
	
}

?>
