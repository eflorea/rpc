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

	public $model_name = '';
	
	/**
	 * Row object to be returned by load
	 * 
	 * @var string
	 */
	protected $rowclass = '\RPC\Db\Table\Row';
	
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
	 * Returns one row (the first in case there are more) which is returned by the query on the model's table. If no row is found, returns null
	 * 
	 * @param string $condition_sql
	 * @param array $condition_values
	 * 
	 * @return RPC_Db_Table_Row
	 */
	abstract public function find();

	/**
	 * Returns all rows returned by the query on the model's table. If no row is found, returns null
	 * 
	 * @param string $condition_sql
	 * @param array $condition_values
	 * 
	 * @return RPC_Db_Table_Row
	 */
	abstract public function findAll();
	
	/**
	 * Returns all rows returned by the custom query. If no row is found, returns null
	 * 
	 * @param string $condition_sql
	 * @param array $condition_values
	 * 
	 * @return RPC_Db_Table_Row
	 */
	abstract public function findBySql();
	
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
	abstract protected function insertRow( \RPC\Db\Table\Row $row );
	
	/**
	 * Performs an update given the supplied data
	 * 
	 * @param array $array
	 * 
	 * @return RPC_Db_Table_Row
	 */
	abstract protected function updateRow( \RPC\Db\Table\Row $row );
	

	
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
	abstract public function unique( $column, \RPC\Db\Table\Row $row );
	
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
	public function __construct( $table_name = null, $ignore_fields = false )
	{
		if( ! $ignore_fields )
		{
			if( $table_name )
			{
				$this->force_table_name = $table_name;
				$this->model_name = $table_name;
			}

			$this->setDb( \RPC\Db::factory() );
			
			
			if( ! $this->force_table_name )
			{
				$classname = get_class( $this );
				$classname = explode( '\\', trim( $classname, '\\' ) );

				$this->model_name = end( $classname );
			
				$classrow = $this->rowclass . '\\' . $this->model_name;
			
				if( class_exists( $classrow ) )
				{
					$this->rowclass = $classrow; 
				}
			}
			else
			{
				$classrow = $this->rowclass . '\\' . $this->model_name;
			
				if( class_exists( $classrow ) )
				{
					$this->rowclass = $classrow; 
				}
			}

			$this->setName( $this->getDb()->getPrefix() . strtolower( $this->model_name ) );
			
			$this->setPkField( strtolower( $this->model_name ) . '_id' );
			
			$this->loadFields();
			
			$this->setIdentityMap( new \RPC\Db\Table\Row\Map() );
		}
		else
		{
			$this->setDb( \RPC\Db::factory() );
		}
	}
	
	/**
	 * Sets the parent database connection
	 * 
	 * @param RPC_Db_Adapter $database
	 */
	protected function setDb( \RPC\Db\Adapter $db )
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
	public function setIdentityMap( \RPC\Db\Table\Row\Map $map )
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
	public function validate( \RPC\Db\Table\Row $row )
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
	public function insert( \RPC\Db\Table\Row $row )
	{
		$this->getDb()->beginTransaction();
		
		if( ! $this->onBeforeSave( $row, \RPC\Db::QUERY_INSERT ) )
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
		
		if( ! $this->onAfterSave( $row, \RPC\Db::QUERY_INSERT ) )
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
		
		if( ! $this->onBeforeSave( $row, \RPC\Db::QUERY_UPDATE ) )
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
		
		if( ! $this->onAfterSave( $row, \RPC\Db::QUERY_UPDATE ) )
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
