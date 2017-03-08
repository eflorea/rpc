<?php

namespace RPC;

/**
 * Skeleton for all filters
 * 
 * @package Filter
 */
interface Filter
{
	
	/**
	 * Transforms the input string and returns the result
	 * 
	 * @param string $input
	 */
	public function filter( $input );
	
}

?>
