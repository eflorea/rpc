<?php

namespace RPC\Validator;

use RPC\Validator;
use RPC\Regex;

/**
 * Validates a ZIP code
 * 
 * @package Validate
 */
class Zip extends Validator
{
	
	/**
	 * Returns value if it is a valid US ZIP, FALSE otherwise.
	 * 
	 * @param mixed $value
	 * 
	 * @return bool
	 */
	public function validate( $value )
	{
		return (bool) preg_match( Regex::US_ZIP, $value );
	}
	
}

?>
