<?php

namespace RPC\Validator;

use RPC\Validator;

class Numeric extends Validator
{
	
	/**
	 * Returns true if the given value is a numeric value
	 *
	 * @param mixed $value
	 * @return bool
	 */
	public function validate( $value )
	{
		return is_numeric( $value );
	}
	
}

?>
