<?php

namespace RPC\Db\Table\Adapter;

use RPC\Db\Table\Adapter;


/**
 * MSSQL driver implementation as a base for all model classes, mapping a table
 *
 * @package Db
 */
class MSSQL extends Adapter
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
		}
		else
		{
			$res = $this->getDb()->query( 'exec sp_columns "' . $this->getName() . '"' );

			foreach( $res as $row )
			{
				$this->fields[] = $row['column_name'];
			}

			$res = null;

			$GLOBALS['_RPC_']['models'][$this->getName()]['fields']      = $this->fields;
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

	public static function execute()
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
	
		return $t->getDb()->execute( $sql );
	}

	public function get()
	{
		$args = func_get_args();

		if( ! isset( $args[0] ) )
		{
			return [];
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

		$sql = 'select top 1 * from "' . $this->getName() . '" where '
		     . $condition;
		if( count( $condition_values ) )
		{
			$res = $this->getDb()->prepare( $sql )->execute( $condition_values );
		}
		else
		{
			$res = $this->getDb()->query( $sql );
		}

		return $res ? $res[0] : [];
	}


	public function getAll()
	{
		$args = func_get_args();

		if( ! isset( $args[0] ) )
		{
			$sql = 'select top 1000 * from "' . $this->getName() . '"';
			$res = $this->getDb()->query( $sql );
			
			return $res ?: [];
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

		$sql = 'select * from "' . $this->getName() . '" where '
		     . $condition . '';
		if( count( $condition_values ) )
		{
			$res = $this->getDb()->prepare( $sql )->execute( $condition_values );
		}
		else
		{
			$res = $this->getDb()->query( $sql );
		}

		return $res ?: [];
	}


	public function getBySql()
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

		$sql = $condition;
		if( count( $condition_values ) )
		{
			$res = $this->getDb()->prepare( $sql )->execute( $condition_values );
		}
		else
		{
			$res = $this->getDb()->query( $sql );
		}

		return $res ?: [];
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

		$sql = 'select top 1 * from "' . $this->getName() . '" where '
		     . $condition;
		if( count( $condition_values ) )
		{
			$res = $this->getDb()->prepare( $sql )->execute( $condition_values );
		}
		else
		{
			$res = $this->getDb()->query( $sql );
		}

		if( $res )
		{
			$row = $res[0];

			/**
			 * Applying any defined conversions on fields
			 */
			foreach( $fields as $column )
			{
				$method = 'convert_' . $column;

				if( method_exists( $this, $method ) )
				{
					$row[$column] = $this->$method( $row[$column] );
				}
			}

			return new $this->rowclass( $this, $row );
		}

		return null;
	}

	public function findAll()
	{
		$args = func_get_args();


		if( ! isset( $args[0] ) )
		{
			$sql = 'select top 10000 * from "' . $this->getName() . '"';
			$res = $this->getDb()->query( $sql );
		}
		else
		{
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

			$sql = 'select * from "' . $this->getName() . '" where '
			     . $condition;
			if( count( $condition_values ) )
			{
				$res = $this->getDb()->prepare( $sql )->execute( $condition_values );
			}
			else
			{
				$res = $this->getDb()->query( $sql );
			}
		}



		if( $res )
		{
			$fields = $this->getFields();

			$output = array();

			foreach( $res as $row )
			{
				/**
				 * Applying any defined conversions on fields
				 */
				foreach( $fields as $column )
				{
					$method = 'convert_' . $column;

					if( method_exists( $this, $method ) )
					{
						$row[$column] = $this->$method( $row[$column] );
					}
				}

				$output[] = new $this->rowclass( $this, $row );
			}

			return $output;
		}

		return [];
	}

	public function findBySql()
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

		$sql = $condition;
		if( count( $condition_values ) )
		{
			$res = $this->getDb()->prepare( $sql )->execute( $condition_values );
		}
		else
		{
			$res = $this->getDb()->query( $sql );
		}

		if( $res )
		{
			$output = array();

			foreach( $res as $row )
			{
				/**
				 * Applying any defined conversions on fields
				 */
				foreach( $fields as $column )
				{
					$method = 'convert_' . $column;

					if( method_exists( $this, $method ) )
					{
						$row[$column] = $this->$method( $row[$column] );
					}
				}

				$output[] = new  $this->rowclass( $this, $row );
			}

			return $output;
		}

		return [];
	}


	protected function insertRow( \RPC\Db\Table\Row $row )
	{
		$columns = array();
		$values    = array();

		if( isset( $row->force_pk ) &&
			$row->force_pk &&
			! in_array( '"' . $row->getTable()->getPkField() . '"', $columns ) )
		{
			$columns[] = '"' . $row->getTable()->getPkField() . '"';
			$values[] = $row[$row->getTable()->getPkField()];
		}

		foreach( $row->getChangedFields() as $column => $yes )
		{
			if( $column == $this->getPkField() )
			{
				continue;
			}

			$columns[] = '"' . $column . '"';

			$method = 'revert_' . $column;
			if( method_exists( $this, $method ) )
			{
				$values[] = $this->$method( $row[$column] );
			}
			else
			{
				$values[]  = $row[$column];
			}
		}

		$sql  = 'insert into "' . $this->getName() . '" ';
		$sql .= '(' . implode( ',', $columns ) . ') values ';
		$sql .= '(' . implode( ',', array_fill( 0, count( $columns ), '?' ) ) . ')';

		return $this->getDb()->prepare( $sql )->execute( $values );
	}

	public function updateRow( \RPC\Db\Table\Row $row )
	{
		$columns = array();
		$values  = array();
		$parts   = array();
		$sql = 'update "' . $this->getName() . '" set ';
		foreach( $row->getChangedFields() as $column => $yes )
		{
			if( $column != $this->getPkField() )
			{
				$parts[]  = '"' . $column . '"=?';

				$method = 'revert_' . $column;
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
		$sql .= ' where "' . $this->getPkField() . '"=?';
		$values[] = $row->getPk();

		return $this->getDb()->prepare( $sql )->execute( $values );
	}

	public function deleteBy( $field, $value )
	{
		$sql   = 'delete from "' . $this->getName() . '" where "' . $field . '"=?';

		return $this->getDb()->prepare( $sql )->execute( array( $value ) );
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
		$filename = CACHE_PATH . '/sql_' . md5( $sql );
		if( is_readable( $filename ) &&
		    ( time() - filemtime( $filename ) ) < $seconds )
		{
			return unserialize( file_get_contents( $filename ) );
		}

		$res = $this->getDb()->query( $sql );

		file_put_contents( $filename, serialize( $res ) );

		return $res;
	}


	public function newObject( $row )
	{
		return new $this->rowclass( $this, $row );
	}


	public static function __callStatic( $name, $arguments )
    {
    	$class_called = get_called_class();
    	$temp_name = str_replace( '_', '', $name );

		//check if model file exists
		if( class_exists( '\\' . $class_called . '\\' . $temp_name ) )
		{
			$class = '\\' . $class_called . '\\' . $temp_name;
			return new $class;
		}
    	return new $class_called( $name );
    }

}

?>
