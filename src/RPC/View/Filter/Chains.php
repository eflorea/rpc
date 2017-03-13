<?php

namespace RPC\View\Filter;

use RPC\View\Filter;

/**
 * Chain of filters which allows for multiple filters to be added so they can be
 * ran sequentially
 * 
 * @package View
 */
class Chains implements \RPC\View\Filter
{
	
	/**
	 * Array containing filters
	 * 
	 * @var array
	 */
	protected $_view_filters = array();
	
	/**
	 * Class constructor which allows a variable number of filters to be added
	 */
	public function __construct()
	{
		if( func_num_args() )
		{
			$this->_view_filters = func_get_args();
		}
	}
	
	/**
	 * Adds a new filter to the queue
	 * 
	 * @param RPC_View_Filter $filter
	 * 
	 * @return self
	 */
	public function addFilter( RPC\View\Filter $filter )
	{
		$this->_view_filters[] = $filter;
		
		return $this;
	}
	
	/**
	 * Removes a filter from the queue
	 * 
	 * @param RPC_View_Filter $filter
	 * 
	 * @return RPC_View
	 */
	public function removeFilter( RPC\View\Filter $filter )
	{
		$key = array_search( $filter, $this->_rpc_filters );
		if( $key !== false )
		{
			unset( $this->_rpc_filters[$key] );
		}
		return $this;
	}
	
	/**
	 * Returns an array of previously loaded filters
	 * 
	 * @return array
	 */
	public function getFilters()
	{
		return $this->_view_filters;
	}
	
	/**
	 * Filters the source code through all registered filters
	 * 
	 * @param string $source
	 * 
	 * @return string
	 */
	public function filter( $source )
	{
		foreach( $this->_view_filters as $filter )
		{
			$source = $filter->filter( $source );
		}
		
		return $source;
	}
	
}

?>
