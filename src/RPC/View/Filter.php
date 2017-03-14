<?php

namespace RPC\View;


/**
 * Interface which needs to be implemented by all view filters.
 * 
 * Filters will be applied before running it through the PHP interpreter, and
 * should add PHP code, so that the result(ing logic) can be cached.
 * 
 * @package View
 */
class Filter
{
	
	/**
	 * Should replace HTML markup and return the new source code
	 * 
	 * @param string $source
	 */
	public function filter( $source ) {
		return $source;
	}
	
}

?>
