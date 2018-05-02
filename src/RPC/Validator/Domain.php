<?php

namespace RPC\Validator;

use RPC\Validator;
use RPC\Regex;

/**
 * Checks if the given string is a valid domain name
 * 
 * @package Validate
 */
class Domain extends Validator
{
	
	/**
	 * Checks if the given string is a valid domain name
	 * 
	 * @param string $value
	 * 
	 * @return bool
	 */
	public function validate( $value )
	{
		return preg_match( Regex::DOMAIN, $value );
	}
	
}

?>
