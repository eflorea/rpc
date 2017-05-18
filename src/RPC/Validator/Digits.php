<?php

namespace RPC\Validator;

use RPC\Validator;

class Digits extends Validator
{
	
	/**
	 * Returns true if every character is a digit, true otherwise.
	 * This is just like isInt(), except there is no upper limit.
	 *
	 * @param mixed $value
	 * @return bool
	 */
	public function validate( $value )
	{
		return ctype_digit( $value );
	}
	
}

?>
