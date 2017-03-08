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
abstract class Datagrid
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
	
	/**
	 * Class constructor
	 */
	public function __construct()
	{
		$this->setPager( new RPC\Datagrid\Pager() );
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
		$request = RPC\HTTP\Request::getInstance();
		
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
			$request   = RPC\HTTP\Request::getInstance();
			
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
					( $order == 'asc' ||
					  $order == 'desc' ) )
				{
					$this->sortby[$allowcolumn] = $order;
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
	public function setInitialSortBy( $sort, $order = '' )
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
			$this->db = RPC\Db::factory( 'default' );
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
	
	/**
	 * Returns an array of items
	 * 
	 * @return array
	 */
	abstract public function fetchRows( $from, $to );
	
}

?>
