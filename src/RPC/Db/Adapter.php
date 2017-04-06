<?php

namespace RPC\Db;

use RPC\Signal;
use RPC\Db;

/**
 * Database adapter base class. This is meant to be extended by all database
 * drivers
 *
 * @package Db
 */
abstract class Adapter
{

	/**
	 * Resource holding the connections to the database
	 *
	 * @var PDO
	 */
	protected $_rpc_handle = null;

	/**
	 * Table name prefix
	 *
	 * @var string
	 */
	protected $_rpc_prefix = '';

	/**
	 * Default fetch mode
	 *
	 * @var int
	 */
	protected $_rpc_fetchmode = \RPC\Db::FETCH_ASSOC;

	/**
	 * Number of affected rows by the last statement
	 *
	 * @var int
	 */
	protected $_rpc_affectedrows = 0;

	public $queries = array();

	/**
	 * Tries to connect to the database, throwing an exception if it fails
	 *
	 * @param string $username
	 * @param string $password
	 * @param array  $options
	 */
	abstract public function connect( $username, $password, $options = null );

	/**
	 * Return the last autoincremented values
	 *
	 * @return int
	 */
	abstract public function getLastId();

	/**
	 * Returns the database handle
	 *
	 * @return PDO
	 */
	public function getHandle()
	{
		return $this->_rpc_handle;
	}

	/**
	 * Sets the database handle
	 *
	 * @param PDO $handle
	 */
	protected function setHandle( \PDO $handle )
	{
		$this->_rpc_handle = $handle;
	}

	/**
	 * Set a connection attribute
	 *
	 * @param int   $attribute
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function setAttribute( $attribute, $value )
	{
		return $this->getHandle()->setAttribute( $attribute, $value );
	}

	/**
	 * Sets the prefix of the tables in this database
	 *
	 * @param string $prefix
	 */
	public function setPrefix( $prefix )
	{
		$this->_rpc_prefix = $prefix;
	}

	/**
	 * Returns the prefix of the tables in the database
	 *
	 * @return string
	 */
	public function getPrefix()
	{
		return $this->_rpc_prefix;
	}

	/**
	 * Sets a prefered fetch mode for all results
	 *
	 * @param int $mode
	 */
	public function setFetchMode( $mode )
	{
		$this->_rpc_fetchmode = $mode;
	}

	/**
	 * Returns the default fetch mode
	 *
	 * @return int
	 */
	public function getFetchMode()
	{
		return $this->_rpc_fetchmode;
	}

	/**
	 * Executes a statement and returns the number of affected rows
	 *
	 * @param string $sql
	 *
	 * @return int
	 */
	public function execute( $sql )
	{
		if( ! \RPC\Signal::emit( array( '\RPC\Db', 'query_start' ), array( $sql, 'statement' ) ) )
		{
			return 0;
		}

		if( defined( 'DEBUG_QUERIES' ) )
		{
			$this->queries[] = $sql;
		}

		$this->_rpc_affectedrows = $this->getHandle()->exec( $sql );

		\RPC\Signal::emit( array( '\RPC\Db', 'query_end' ), array( $sql, 'statement' ) );

		return $this->_rpc_affectedrows;
	}

	/**
	 * Queries the database
	 *
	 * @param string $sql
	 *
	 * @return array
	 */
	public function query( $sql )
	{
		if( ! \RPC\Signal::emit( array( '\RPC\Db', 'query_start' ), array( $sql, 'query' ) ) )
		{
			return null;
		}

		if( defined( 'DEBUG_QUERIES' ) )
		{
			$this->queries[] = $sql;
		}

		$res = $this->getHandle()->query( $sql, $this->getFetchMode() );

		\RPC\Signal::emit( array( '\RPC\Db', 'query_end' ), array( $sql, 'query' ) );

		return $res->fetchAll();
	}

	/**
	 * Returns the number of affected rows by a previous insert, update, delete
	 * or truncate query
	 *
	 * @return int
	 */
	public function getAffectedRows()
	{
		 return $this->_rpc_affectedrows;
	}

	/**
	 * Set the default charset for the connection
	 *
	 * @param string $charset
	 */
	abstract public function setCharset( $charset = null );

	/**
	 * Prepares a query for execution. Returns a statement
	 *
	 * @return RPC_Db_Statement
	 */
	abstract public function prepare( $sql, $options = null );

	/**
	 * Starts a new transaction
	 *
	 * @return bool
	 */
	public function beginTransaction()
	{
		return $this->getHandle()->beginTransaction();
	}

	/**
	 * Commits all the queries and statements in the transaction
	 *
	 * @return bool
	 */
	public function commit()
	{
		return $this->getHandle()->commit();
	}

	/**
	 * Rolls back queries and statement in the transaction
	 *
	 * @return bool
	 */
	public function rollback()
	{
		return $this->getHandle()->rollBack();
	}

	/**
	 * Returns the code of the last error
	 *
	 * @return int
	 */
	public function getErrorCode()
	{
		return $this->getHandle()->errorCode();
	}

	/**
	 * Returns info about the error
	 *
	 * @return array
	 */
	public function getErrorInfo()
	{
		return $this->getHandle()->errorInfo();
	}

	/**
	 * Disconnects from the server, freeing up resources
	 */
	public function disconnect()
	{
		$this->_rpc_handle = null;
	}

	/**
	 * Cleaning up the object
	 */
	public function __destruct()
	{
		$this->disconnect();
	}


	public function getQueries( $all = false )
	{
		if( ! defined( 'DEBUG_QUERIES' ) )
		{
			return 'DEBUG_QUERIES constant is not defined.';
		}
		return ( $all ? $this->queries : end( $this->queries ) );
	}

}

?>
