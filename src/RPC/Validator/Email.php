<?php

namespace RPC\Validator;

use RPC\Validator;
use RPC\Regex;

class Email extends Validator
{
	
	/**
	 * Returns true if it is a valid email format, false otherwise.
	 *
	 * @param mixed $value
	 * @return bool
	 */
	public function validate( $value )
	{
		return preg_match( Regex::EMAIL, $value );
	}
	
}

?>
