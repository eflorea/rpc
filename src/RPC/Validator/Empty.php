<?php

namespace RPC\Validator;

use RPC\Validator;

class Empty extends Validator
{
	
	/**
	 * Checks if the given value is empty
	 *
	 * @param mixed $value
	 * @return bool
	 */
	public function validate( $value )
	{
		return empty( $value );
	}
	
}

?>
