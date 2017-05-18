<?php

namespace RPC\Validator;

use RPC\Validator;
use RPC\Regex;

/**
 * Checks if the given string is an URI
 * 
 * @package Validate
 */
class URI extends Validator
{
	
	/**
	 * Checks if the given string is an URI
	 * 
	 * @param string $value
	 * 
	 * @return bool
	 */
	public function validate( $value )
	{
		return preg_match( RPC\Regex::URI , $value );
	}
	
}

?>
