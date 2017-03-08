<?php

namespace RPC\Validator;

use RPC\Validator;

class Alnum extends Validator
{
	
	/**
	 * Validates if every characther is either a letter or a number
	 *
	 * @param string $value
	 * @return bool
	 */
	public function validate( $value )
	{
		return ctype_alnum( $value );
	}
	
}

?>
