<?php

namespace RPC\Validator;

use RPC\Validator;

class Alpha extends Validator
{
	
	/**
	 * Validates if every character of $value is a letter
	 *
	 * @param string $value
	 * @return bool
	 */
	public function validate( $value )
	{
		return ctype_alpha( $value );
	}
	
}

?>
