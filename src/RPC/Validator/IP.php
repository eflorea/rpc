<?php

namespace RPC\Validator;

use RPC\Validator;

class IP extends Validator
{
	
	/**
	 * Returns true if it is a valid IPV4 format, false otherwise.
	 *
	 * @param mixed $value
	 * @return bool
	 */
	public function validate( $value )
	{
		return (bool)ip2long( $value );
	}
	
}

?>
