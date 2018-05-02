<?php

namespace RPC\Validator;

use RPC\Validator;
use RPC\Regex;

class Password extends Validator
{
	
	/**
	 * Returns true if it is a valid password format, false otherwise.
	 *
	 * @param mixed $value
	 * @return bool
	 */
	public function validate( $value )
	{
		return (bool)preg_match( Regex::PASSWORD, $value );
	}
	
}

?>
