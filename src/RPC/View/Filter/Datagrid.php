<?php

namespace RPC\View\Filter;

use RPC\Regex;
use RPC\View\Filter;

class Datagrid extends Filter
{

	protected $filters = array();
	
	public function __construct()
	{
		$this->addFilter( new \RPC\View\Filter\Datagrid\Sort() );
		$this->addFilter( new \RPC\View\Filter\Datagrid\Pagination() );
	}

	public function addFilter( $filter )
	{
		$this->filters[] = $filter;
	}
	
	public function filter( $source )
	{
		if( $this->filters )
		{
			foreach( $this->filters as $filter )
			{
				$source = $filter->filter( $source );
			}
		}

		$regex  = new \RPC\Regex( '/(<.*?)datagrid="([^"]+)"(.*?(?<!\?)>)/' );
		$source = $regex->replace( $source, '$1$3<?php $_rpc_view_datagrid = $$2; ?>' );
				
		return $source;
	}
	
}

?>
