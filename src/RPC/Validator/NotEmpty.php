<?php

namespace RPC\Validator;

use RPC\Validator;

class NotEmpty extends Validator
{
	
	public function validate( $value )
	{
		return ! empty( $value );
	}
	
}

?>
