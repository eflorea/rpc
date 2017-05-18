<?php

namespace RPC\Validator;

use RPC\Validator;

class Float extends Validator
{
	
	/**
	* Returns value if it is a valid float value, FALSE otherwise.
	*
	* @param mixed $value
	* @return bool
	*/
	public function validate( $value )
	{
		return is_float( $value );
	}
	
}

?>
