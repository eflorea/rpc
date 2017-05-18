<?php

namespace RPC\Validator;

use RPC\Validator;

class Hex extends Validator
{
	
	/**
	 * Returns true if every character in text is a hexadecimal 'digit', that is
	 * a decimal digit or a character from [A-Fa-f], false otherwise
	 *
	 * @param mixed $value
	 * @return bool
	 */
	public function validate( $value )
	{
		return ctype_xdigit( $value );
	}
	
}

?>
