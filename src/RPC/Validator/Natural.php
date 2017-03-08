<?php

namespace RPC\Validator;

use RPC\Validator;

class Natural extends Validator
{
	
	/**
	 * Returns true if the given value is an integer
	 *
	 * @param mixed $value
	 * @return bool
	 */
	public function validate( $value )
	{
		if( ( ! is_numeric( $value ) ) ||
		    ( (int) $value != $value ) ||
		    ( $value < 0 ) )
		{
			return false;
		}
		
		return true;
	}
	
}

?>
