<?php

namespace RPC;

use RPC\Datagrid\Pager;
use RPC\HTTP\Request;
use RPC\Db;


/**
 * Class used to page and sort a set of items. It does not do anything
 * else besides these two things so it can be used with anything
 * 
 * @package Datagrid
 */
class Datagrid
{
	
	/**
	 * Variable holding an array of columns to be sorted by
	 * 
	 * @var array
	 */
	protected $sortby = array();
	
	/**
	 * Variable holding an array of columns by which the datagrid can
	 * be sorted
	 * 
	 * @var array
	 */
	protected $allowsort = array();

	/**
	 * Sort by one column only flag
	 */
	protected $sort_only_one_column = true;
	
	/**
	 * Datagrid's pager
	 * 
	 * @var RPC_Datagrid_Pager
	 */
	protected $pager = null;
	
	/**
	 * Array of results returned by the datagrid
	 * 
	 * @var array
	 */
	protected $rows = null;

	/**
	 * Database object
	 * 
	 * @var RPC_Database_Adapter
	 */
	protected $db = null;


	protected $model = null;

	protected $conditions = array();
	protected $sql_conditions = array();
	protected $conditions_value = array();
	protected $sql_conditions_values = array();
	protected $group_by;

	protected $manual_sql = null;
	protected $join_sql = null;

	
	/**
	 * Class constructor
	 */
	public function __construct( $model = null )
	{
		$this->model = $model;
		$this->setPager( new Pager() );
	}
	
	/**
	 * Returns a link which, when clicked, will sort by the $column field
	 * 
	 * @param string $column
	 * @param string $name
	 * 
	 * @return string
	 */
	public function printSortBy( $column, $name )
	{
		$request = Request::getInstance();
		
		$query  = $request->get;
		$sortby = $this->getSortBy();
		$class  = 'sortable';
		
		$query['sorted'] = 1;
		
		if( $this->sort_only_one_column )
		{
			if( count( $sortby ) && empty( $sortby[$column] ) )
			{
				$sortby = array();
			}
		}

		if( ! in_array( $column, $this->allowsort ) )
		{
			return $column;
		}
		
		if( empty( $sortby[$column] ) )
		{
			$sortby[$column] = 'asc';
		}
		else
		{
			if( $sortby[$column] == 'desc' )
			{
				unset( $sortby[$column] );
				$class = 'sortdesc';
			}
			else
			{
				$sortby[$column] = 'desc';
				$class = 'sortasc';
			}
		}
		
		$query['sort'] = $sortby;
		
		return '<a href="' . $request->getPathInfo() . '?' . http_build_query( $query, '', '&amp;' ) . '" class="' . $class . '">' . $name . '<span class="th-sort">
                        ' . ( $class == 'sortable' ? '<i class="sort-thin"></i>' : ( $class == 'sortasc' ? '<i class="fa fa-sort-up"></i>' : '<i class="fa fa-sort-down"></i>' ) ) . '
                      </span></a>';
	}
	
	/**
	 * Gives the columns for which the sorting is allowed
	 * 
	 * The function receives a variabile number of parameters (column
	 * names)
	 * 
	 * @return RPC_Datagrid
	 */
	public function allowSortBy()
	{
		$this->allowsort = func_get_args();
		
		return $this;
	}
	
	/**
	 * Returns the columns the results should be sorted by, as well as
	 * the order to be sorted
	 * 
	 * @return array
	 */
	public function getSortBy()
	{
		static $called = 0;
		
		if( ! $called )
		{
			$request   = Request::getInstance();
			
			if( empty( $request->get['sorted'] ) )
			{
				$sortarray = $this->sortby;
			}
			else
			{
				$sortarray = (array)@$request->get['sort'];
			}
			
			$this->sortby = array();
			
			if( empty( $sortarray ) )
			{
				return array();
			}
			
			foreach( $this->allowsort as $allowcolumn )
			{
				$order = @$sortarray[$allowcolumn];
				if( ! empty( $order ) &&
					( strtolower( $order ) == 'asc' ||
					  strtolower( $order ) == 'desc' ) )
				{
					$this->sortby[$allowcolumn] = strtolower( $order );
				}
			}
			
			$called = 1;
		}
		
		return $this->sortby;
	}
	
	/**
	 * Sets the initial sort of the datagrid
	 * 
	 * @param string|array $sort
	 * @param string       $order
	 * 
	 * @return RPC_Datagrid
	 */
	public function initialSortBy( $sort, $order = '' )
	{
		if( $order )
		{
			$this->sortby[$sort] = $order;
		}
		else
		{
			foreach( $sort as $k => $v )
			{
				$this->sortby[$k] = $v;
			}
		}
		
		return $this;
	}
	
	/**
	 * Sets the datagrid's pager
	 * 
	 * @param RPC_Datagrid_Pager $pager
	 * 
	 * @return RPC_Datagrid
	 */
	public function setPager( $pager )
	{
		$this->pager = $pager;
	}
	
	/**
	 * Returns the datagrid's pager
	 * 
	 * @return RPC_Datagrid_Pager
	 */
	public function getPager()
	{
		return $this->pager;
	}
	
	public function setDb( $db )
	{
		$this->db = $db;
		
		return $this;
	}
	
	public function getDb()
	{
		if( ! $this->db )
		{
			$this->db = Db::factory( 'default' );
		}
		
		return $this->db;
	}
	
	/**
	 * Returns the array of rows fetched by the datagrid
	 * 
	 * If called multiple times, it will only execute the fetching
	 * instructions once and then cache the results
	 * 
	 * @return array
	 */
	public function getRows()
	{
		list( $from, $to ) = $this->getPager()->getLimits();
		if( ! is_null( $this->rows ) )
		{
			return $this->rows;
		}
		
		return $this->fetchRows( $from, $to );
	}
	
	
	public function setRows( $rows )
	{
		$this->rows = $rows;
	}

	public function getPrefix() {

		return $this->getDb()->getPrefix();
	
	}
	
	/**
	 * Returns an array of items
	 * 
	 * @return array
	 */
	public function fetchRows( $from, $to )
	{
		$db = $this->getDb();

		if( $this->manual_sql )
		{
			$sql = trim( $this->manual_sql );
			$sql = preg_replace( '/select /', 'select SQL_CALC_FOUND_ROWS ', $this->manual_sql, 1 );
		}
		else
		{
			$sql = '
				SELECT SQL_CALC_FOUND_ROWS
					*
				FROM
					' . $this->model->getName();
		}

		if( $this->join_sql )
		{
			$sql .= ' ' . $this->join_sql . ' ';
		}

		$sort    = array();
		$columns = $this->getSortBy();
		//$columns = $this->sortby;
		foreach( $columns as $column => $order )
		{
			$sort[] = $column . ' ' . $order;
		}
		$sort = empty( $sort ) ? '' : ' ORDER BY ' . implode( ',', $sort );
		
		$where = ( count( $this->conditions ) || $this->sql_conditions ) ? ' WHERE ' : '';
		
		if( count( $this->conditions ) )
		{
			
			$where .= implode( ' AND ', $this->conditions );
		}
		
		if( count( $this->sql_conditions ) )
		{
			if( count( $this->conditions ) )
			{
				$where .= ' AND ';
			}
			
			$where .= ' ' . implode( ' AND ', $this->sql_conditions  ). ' ';
		}

		$sql .= $where . '
				' . $this->group_by . '
				' . $sort . '
				LIMIT
					' . $from . ', ' . ( $to - $from );

		if( count( $this->sql_conditions_values ) )
		{
			$this->conditions_value = array_merge( $this->conditions_value, $this->sql_conditions_values );
		}

		if( count( $this->conditions_value ) )
		{
			$this->rows = $db->prepare( $sql )->execute( $this->conditions_value );
		}
		else
		{
			$this->rows = $db->prepare( $sql )->execute();
		}		
		
		$this->getPager()->setTotal( $db->getFoundRows() );
		
		if( $this->model )
		{
			if( $this->rows )
			{
				foreach( $this->rows as $k => $r )
				{
					$this->rows[$k] = $this->model->newObject( $r );
				}
			}
		}

		return $this->rows;
	}
	
	public function setCondition( $condition, $value )
	{
		if( strpos( $condition, '?' ) === false )
		{
			$condition .= ' = ?'; 
		}
		$this->conditions[] = $condition;
		if( is_array( $value ) )
		{
			foreach( $value as $v )
			{
				$this->conditions_value[] = $v;
			}
		}
		else
		{
			$this->conditions_value[] = $value;
		}
	}

	
	public function setPerPage( $limit )
	{
		$this->getPager()->setPerPage( $limit );
	}
	
	public function setSortBy( $sort, $order = '' )
	{
		$this->sortby[$sort] = $order;
	}


	public function groupBy( $group_by = null )
	{
		$this->group_by = ' ' . $group_by . ' ';
	}

	public function query( $sql, $conditions = null ) {
		$this->manual_sql = $sql;

		if( $conditions )
		{
			if( is_array( $conditions ) )
			{
				$this->conditions_value = array_merge( $this->conditions_value, $conditions );
			}
			else
			{
				$this->conditions_value[] = $conditions;
		
			}
		}
	}


	public function sqlJoin( $join_sql )
	{
		$this->join_sql = $join_sql;
	}


	public function nextPageExists()
	{
		if( ( $this->getPager()->getCurrentPage() + 1 ) < $this->getPager()->getTotalPages() )
		{
			return 1;
		}
		return 0;
	}
	
}

?>
