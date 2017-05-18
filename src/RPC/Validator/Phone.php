<?php

namespace RPC\Validator;

use RPC\Validator;

/**
 * Validates a phone number
 * 
 * @package Validate
 */
class Phone extends Validator
{
	
	/**
	 * Returns value if it is a valid phone number format, FALSE
	 * otherwise. The optional second argument indicates the country.
	 * 
	 * @param mixed $value
	 * 
	 * @return mixed
	 */
	public function validate( $value )
	{

		$number = preg_replace( '/[^\d]/', '', $value );
		
		if( strlen( $number ) != 10 )
		{
			return false;
		}
		
		return true;
	}
	
}

?>
