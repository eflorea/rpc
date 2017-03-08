<?php

namespace RPC\Validator;

use RPC\Validator;

class Image extends Validator
{
	
	public function validate( $filename )
	{
		return getimagesize( $filename );
	}
	
}

?>
