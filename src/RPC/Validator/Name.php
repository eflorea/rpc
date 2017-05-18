<?php

namespace RPC\Validator;

use RPC\Validator;
use RPC\Regex;

class Name extends RPC_Validator
{
	
	/**
	 * Returns value if it is a valid format for a person's name,
	 * false otherwise.
	 *
	 * @param mixed $value
	 * @return bool
	 */
	public function validate( $value )
	{
		return preg_match( RPC\Regex::NAME, $value  );
	}
	
}

?>
