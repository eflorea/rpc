<?php

namespace RPC\Db\Table\Adapter;

use RPC\Db\Table\Adapter;


/**
 * MySQL driver implementation as a base for all model classes, mapping a table
 * 
 * @package Db
 */
abstract class MySQL extends Adapter
{
	
	/**
	 * @todo Use a cache
	 */
	public function loadFields()
	{
		/**
		 * I store the fields in a global variable for performance reasons: if
		 * the table is instantiated more than once, then the query and field
		 * building is done only once
		 */
		if( ! empty( $GLOBALS['_RPC_']['models'][$this->getName()]['fields'] ) )
		{
			$this->fields      = $GLOBALS['_RPC_']['models'][$this->getName()]['fields'];
			$this->cleanfields = $GLOBALS['_RPC_']['models'][$this->getName()]['cleanfields'];
		}
		else
		{
			$res = $this->getDb()->query( 'describe `' . $this->getName() . '`' );
			
			$table_prefix = str_replace( $this->getDb()->getPrefix(), '', $this->getName() ) . '_';
			foreach( $res as $row )
			{
				$this->fields[] = $row['field'];

				$clean = str_replace( $table_prefix, '', $row['field'] );
				$this->cleanfields[$clean] = $row['field'];
			}
			
			$res = null;
			
			$GLOBALS['_RPC_']['models'][$this->getName()]['fields']      = $this->fields;
			$GLOBALS['_RPC_']['models'][$this->getName()]['cleanfields'] = $this->cleanfields;
		}
	}

	public static function query()
	{
		$args = func_get_args();

		$sql = $args[0];
		$condition_values = array();

		if( isset( $args[1] ) )
		{
			if( is_array( $args[1] ) )
			{
				$condition_values = $args[1];
			}
			else
			{
				$condition_values[] = $args[1];
			}
		}

		$t = get_called_class();
		$t = new $t( null, true );

		if( $condition_values )
		{
			return $t->getDb()->prepare( $sql )->execute( $condition_values );
		}
	
		return $t->getDb()->query( $sql );
	}

	public function get()
	{
		$args = func_get_args();

		if( ! isset( $args[0] ) )
		{
			return false;
		}

		$condition_values = array();

		if( is_array( $args[0] ) )
		{
			$condition = array();
			foreach( $args[0] as $k => $r )
			{
				$condition[] = " " . $k . " = ? ";
				$condition_values[] = $r; 
			}
			$condition = implode( ' and ', $condition );
		}
		else
		{
			$condition = $args[0];

			//check if ? exists in condition
			if( strpos( $condition, "?" ) === false )
			{
				$condition .= " = ? "; 
			}

			if( isset( $args[1] ) )
			{
				if( is_array( $args[1] ) )
				{
					$condition_values = $args[1];
				}
				else
				{
					$condition_values[] = $args[1];
				}
			}
			elseif( is_numeric( $args[0] ) )
			{
				$condition = " " . $this->getPkField() . " = ? ";
				$condition_values[] = $args[0];
			}
		}

		$fields = $this->getFields();
		$cleanfields = $this->getCleanFields();
				
		$sql = 'select * from `' . $this->getName() . '` where '
		     . $condition . ' limit 1';
		if( count( $condition_values ) )
		{
			$res = $this->getDb()->prepare( $sql )->execute( $condition_values );
		}
		else
		{
			$res = $this->getDb()->query( $sql );
		}

		if( count( $res ) )
		{
			return $res[0];
		}
		
		return null;
	}


	public function getAll()
	{
		$fields = $this->getFields();
		$cleanfields = $this->getCleanFields();
		
		$condition = strtolower( $condition );
		
		$sql = 'select * from `' . $this->getName() . '` where '
		     . $condition . '';
		$res = $this->getDb()->prepare( $sql )->execute( $condition_values );
		
		if( count( $res ) )
		{
			return $res;
		}
		
		return null;
	}
	

	public function find()
	{
		$args = func_get_args();

		if( ! isset( $args[0] ) )
		{
			return false;
		}

		$condition_values = array();

		if( is_array( $args[0] ) )
		{
			$condition = array();
			foreach( $args[0] as $k => $r )
			{
				$condition[] = " " . $k . " = ? ";
				$condition_values[] = $r; 
			}
			$condition = implode( ' and ', $condition );
		}
		else
		{
			$condition = $args[0];

			//check if ? exists in condition
			if( strpos( $condition, "?" ) === false )
			{
				$condition .= " = ? "; 
			}

			if( isset( $args[1] ) )
			{
				if( is_array( $args[1] ) )
				{
					$condition_values = $args[1];
				}
				else
				{
					$condition_values[] = $args[1];
				}
			}
			elseif( is_numeric( $args[0] ) )
			{
				$condition = " " . $this->getPkField() . " = ? ";
				$condition_values[] = $args[0];
			}
		}

		$fields = $this->getFields();
		$cleanfields = $this->getCleanFields();
				
		$sql = 'select * from `' . $this->getName() . '` where '
		     . $condition . ' limit 1';
		if( count( $condition_values ) )
		{
			$res = $this->getDb()->prepare( $sql )->execute( $condition_values );
		}
		else
		{
			$res = $this->getDb()->query( $sql );
		}

		if( count( $res ) )
		{
			$row = $res[0];
			
			/**
			 * Applying any defined conversions on fields
			 */
			foreach( $cleanfields as $cf => $f )
			{
				$method = 'convert_' . $cf;
				
				if( method_exists( $this, $method ) )
				{
					$row[$f] = $this->$method( $row[$f] );
				}
			}
			
			return new $this->rowclass( $this, $row );
		}
		
		return null;
	}
	
	public function findAll()
	{
		$fields = $this->getFields();
		$cleanfields = $this->getCleanFields();
		
		$condition = strtolower( $condition );
		
		$sql = 'select * from `' . $this->getName() . '` where '
		     . $condition . '';
		$res = $this->getDb()->prepare( $sql )->execute( $condition_values );
		
		if( count( $res ) )
		{
			$output = array();
			foreach( $res as $row )
			{
				/**
				 * Applying any defined conversions on fields
				 */
				foreach( $cleanfields as $cf => $f )
				{
					$method = 'convert_' . $cf;
					
					if( method_exists( $this, $method ) )
					{
						$row[$f] = $this->$method( $row[$f] );
					}
				}
				
				$output[] = new $this->rowclass( $this, $row );
			}
			
			return $output;
		}
		
		return null;
	}

	public function findBySql()
	{

		$fields = $this->getFields();
		$cleanfields = $this->getCleanFields();
		
		$condition = strtolower( $condition );
		
		$sql = $condition;
		$res = $this->getDb()->prepare( $sql )->execute( $condition_values );
		
		if( count( $res ) )
		{
			$output = array();
			foreach( $res as $row )
			{
				/**
				 * Applying any defined conversions on fields
				 */
				foreach( $cleanfields as $cf => $f )
				{
					$method = 'convert_' . $cf;
					
					if( method_exists( $this, $method ) )
					{
						$row[$f] = $this->$method( $row[$f] );
					}
				}
				
				$output[] = new $this->rowclass( $this, $row );
			}
			
			return $output;
		}
		
		return null;
	}
	
	protected function insertRow( \RPC\Db\Table\Row $row )
	{
		$columns = array();
		$values    = array();

		if( isset( $row->force_pk ) &&
			$row->force_pk &&
			! in_array( '`' . $row->getTable()->getPkField() . '`', $columns ) )
		{
			$columns[] = '`' . $row->getTable()->getPkField() . '`';
			$values[] = $row[$row->getTable()->getPkField()];
		}

		foreach( $row->getChangedFields() as $column => $yes )
		{
			if( $column == $this->getPkField() )
			{
				continue;
			}
			
			$columns[] = '`' . $column . '`';
			
			$method = 'revert_' . array_search( $column, $this->cleanfields );
			if( method_exists( $this, $method ) )
			{
				$values[] = $this->$method( $row[$column] );
			}
			else
			{
				$values[]  = $row[$column];
			}
		}

		$sql  = 'insert into `' . $this->getName() . '` ';
		$sql .= '(' . implode( ',', $columns ) . ') values ';
		$sql .= '(' . implode( ',', array_fill( 0, count( $columns ), '?' ) ) . ')';
		
		return $this->getDb()->prepare( $sql )->execute( $values );
	}
	
	public function updateRow( \RPC\Db\Table\Row $row )
	{
		$columns = array();
		$values  = array();
		$parts   = array();
		$sql = 'update `' . $this->getName() . '` set ';
		foreach( $row->getChangedFields() as $column => $yes )
		{
			if( $column != $this->getPkField() )
			{
				$parts[]  = '`' . $column . '`=?';
				
				$method = 'revert_' . array_search( $column, $this->cleanfields );
				if( method_exists( $this, $method ) )
				{
					$values[] = $this->$method( $row[$column] );
				}
				else
				{
					$values[]  = $row[$column];
				}
			}
		}
		
		$sql .= implode( ',', $parts );
		$sql .= ' where `' . $this->getPkField() . '`=?';
		$values[] = $row->getPk();
		
		return $this->getDb()->prepare( $sql )->execute( $values );
	}
	
	public function deleteBy( $field, $value )
	{
		$fields = $this->getFields();
		$cleanfields = $this->getCleanFields();
		
		$field = strtolower( $field );
		
		$field = in_array( $field, $fields ) ? $field : $cleanfields[$field];
		$sql   = 'delete from `' . $this->getName() . '` where `' . $field . '`=?';
		
		return $this->getDb()->prepare( $sql )->execute( array( $value ) );
	}
	
	public function deleteAll()
	{
		return $this->getDb()->execute( 'delete from `' . $this->getName() . '`' );
	}
	
	public function truncate()
	{
		return $this->getDb()->execute( 'truncate table `' . $this->getName() . '`' );
	}
	
	public function countRows()
	{
		$res = $this->getDb()->query( 'select count(*) as nr from `' . $this->getName() . '`' );
		
		return (int) $res[0]['nr'];
	}
	
	public function exists( $value, $column )
	{
		if( ! in_array( $column, $this->getFields() ) )
		{
			throw new \Exception( 'Column "' . $column . '" does not exist in table ' . $this->getName() );
		}
		
		$res = $this->getDb()->prepare( 'select count(*) as nr from `' . $this->getName() . '` where `' . $column . '`=? limit 1' )->execute( array( $value ) );
		
		return (int) $res[0]['nr'];
	}
	
	public function unique( $column, \RPC\Db\Table\Row $row )
	{
		$values[0] = $row[$column];
		
		$sql = 'select count(*) as nr from `' . $this->getName() . '` where '
		     . '`' . $column . '`=?';
		if( $row->getPk() )
		{
			$sql .= ' and ' . $this->getPkField() . '!=?';
			$values[] = $row->getPk();
		}
		
		$res = $this->getDb()->prepare( $sql )->execute( $values );
		$row = $res[0];
		
		return ! (int) $row['nr'];
	}
	
	/**
	 * @todo Implement
	 */
	public function lock()
	{
		throw new \Exception( 'Not implemented' );
	}
	
	/**
	 * @todo Implement
	 */
	public function unlock()
	{
		throw new \Exception( 'Not implemented' );
	}


	public function cacheQuery( $sql, $seconds )
	{
		$filename = PATH_CACHE . '/sql_' . md5( $sql );
		if( is_readable( $filename ) &&
		    ( time() - filemtime( $filename ) ) < $seconds )
		{
			return unserialize( file_get_contents( $filename ) );
		}
		
		$res = $this->getDb()->query( $sql );
		
		file_put_contents( $filename, serialize( $res ) );
		
		return $res;
	}


	public static function __callStatic( $name, $arguments )
    {
    	$class_called = get_called_class();
    	return new $class_called( $name );
    }
	
}

?>
