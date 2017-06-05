<?php

namespace RPC\Db;

use RPC\Db;
use RPC\Db\Adapter;
use RPC\Signal;

/**
 * Class representing a prepared query. It is not meant to be instantiated in
 * code, instead, an instance will be returned each time one executes
 * <code>RPC_Db_Adapter->prepare( $sql )</code>
 * 
 * @package Db
 */
class Statement
{
	
	/**
	 * Database handle
	 * 
	 * @var RPC_Db_Adapter
	 */
	protected $db;
	
	/**
	 * Query to be executed
	 * 
	 * @var string
	 */
	protected $sql;
	
	/**
	 * Statement
	 * 
	 * @var PDOStatement
	 */
	protected $stmt;
	
	/**
	 * Prepares the query
	 * 
	 * @param string         $sql
	 * @param array          $options
	 * @param RPC_Db_Adapter $db
	 */
	public function __construct( $sql, $options = array(), \RPC\Db\Adapter $db )
	{
		$this->db  = $db;
		$this->sql = $sql;
		
		$this->stmt = $this->db->getHandle()->prepare( $sql, $options );
		$this->stmt->setFetchMode( $this->db->getFetchMode() );
	}
	
	/**
	 * Sets how rows in the result should be returned
	 * 
	 * @param int $fetchmode
	 * 
	 * @return self
	 */
	public function setFetchMode( $fetchmode )
	{
		$this->stmt->setFetchMode( $fetchmode );
		return $this;
	}
	
	/**
	 * Executes a query and returns the result
	 * 
	 * @param array $params Parameters that should be replaced in the query
	 * 
	 * @return bool
	 */
	public function execute( $params = array() )
	{
		if( ! \RPC\Signal::emit( array( '\RPC\Db', 'query_start' ), array( $this->sql, 'prepared' ) ) )
		{
			return null;
		}
		
		$res = $this->stmt->execute( $params );
		
		$sql = $this->sql;

		foreach( $params as $param )
		{
			$sql = preg_replace( '/\?/', "'" . $param . "'", $sql, 1 );
		}
		
		if( getenv( 'DEBUG_QUERIES' ) === "true" )
		{
			$this->db->getHandle()->_queries[] = $sql;
		}
		
		\RPC\Signal::emit( array( '\RPC\Db', 'query_end' ), array( $this->sql, 'prepared' ) );
		
		if( $res )
		{
			if( stripos( trim( $this->sql ), 'select' ) === 0 )
			{
				$rows = $this->stmt->fetchAll();
				$this->stmt->closeCursor();
				
				return $rows;
			}
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * Binds a parameter to the specified variable name
	 * 
	 * @param string|int $column
	 * @param mixed      $value
	 * @param int        $type
	 * @param int        $length
	 * @param mixed      $options
	 * 
	 * @return self
	 */
	public function bindParam( $param, &$value, $type = -1, $length = null, $options = null )
	{
		$this->stmt->bindParam( $param, $value, $type, $length, $options );
		
		return $this;
	}
	
	/**
	 * Binds a value to a parameter
	 * 
	 * @param string|int $column
	 * @param mixed      $value
	 * @param int        $type
	 * 
	 * @return self
	 */
	public function bindValue( $param, $value, $type = -1 )
	{
		$this->stmt->bindValue( $param, $value, $type );
		
		return $this;
	}
	
	/**
	 * Bind a column to a PHP variable
	 * 
	 * @param string|int $column
	 * @param mixed      $value
	 * @param int        $type
	 * 
	 * @return self
	 */
	public function bindColumn( $column, &$param, $type = null )
	{
		$this->stmt->bindColumn( $column, $param, $type );
		
		return $this;
	}
	
}

?>
