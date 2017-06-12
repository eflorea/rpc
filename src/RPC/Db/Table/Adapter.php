<?php

namespace RPC\Db\Table;

use ArrayAccess;

use RPC\Db;
use RPC\Db\Table\Row;

/**
 * Base class for all model classes, representing a table
 *
 * @package Db
 */
abstract class Adapter
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
	 * Identity map for loaded rows from the table
	 *
	 * @var RPC_Db_Table_RowMap
	 */
	protected $map = null;

	abstract protected function loadFields();

	abstract public function get();

	abstract public function getAll();

	abstract public function getBySql();

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
		if( $ignore_fields ||
		 	( count( explode( '\\', get_called_class() ) ) == 2 && ! $table_name ) )
		{
			$this->setDb( \RPC\Db::factory() );
		}
		else
		{
			if( $table_name )
			{
				$this->force_table_name = $table_name;

				if( ! $this->model_name )
				{
					$this->model_name = $table_name;
				}
			}

			$this->setDb( \RPC\Db::factory() );

			if( ! $this->force_table_name )
			{
				$classname = get_class( $this );
				$classname = explode( '\\', trim( $classname, '\\' ) );

				if( ! $this->model_name )
				{
					$this->model_name = end( $classname );
					$classrow = $this->rowclass . '\\' . $this->model_name;
				}
				else
				{
					$classrow = $this->rowclass . '\\' . end( $classname );
				}

				$classrow = str_replace( '_', '', $classrow );

				if( class_exists( $classrow ) )
				{
					$this->rowclass = $classrow;
				}
			}
			else
			{
				if( ! $this->model_name )
				{
					$this->model_name = $this->force_table_name;
				}
				$classrow = $this->rowclass . '\\' . $this->model_name;
				$classrow = str_replace( '_', '', $classrow );
				if( class_exists( $classrow ) )
				{
					$this->rowclass = $classrow;
				}
			}

			$this->setName( $this->getDb()->getPrefix() . strtolower( $this->model_name ) );

			$this->setPkField( 'id' );

			$this->loadFields();

			$this->setIdentityMap( new \RPC\Db\Table\Row\Map() );
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
		//by default set created and modified dates
		$row->created( date( 'Y-m-d H:i:s' ) );
		$row->modified( date( 'Y-m-d H:i:s' ) );

		//set status to active by default
		if( ! $row->offsetGet( 'status' ) )
		{
			$row->status( 'active' );
		}
		elseif( $row->status() == 'deleted' &&
				! $row->offsetGet( 'deleted' ) )
		{
			$row->deleted( date( 'Y-m-d H:i:s' ) );
		}

		return $row;
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
		//set modified date by default
		$row->modified( date( 'Y-m-d H:i:s' ) );

		if( $row->offsetGet( 'status' ) &&
			$row->status() == 'deleted' &&
			! $row->offsetGet( 'deleted' ) )
		{
			$row->deleted( date( 'Y-m-d H:i:s' ) );
		}

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

	public function lastQuery( $show_all = false )
	{
		return $this->getDb()->getQueries( $show_all );
	}

}

?>
