<?php

namespace RPC\Validator;

use RPC\Validator;
use Exception;

class OneOf extends Validator
{
	
	protected $values;
	
	public function __construct( $values, $errormessage = '' )
	{
		if( ! is_array( $values ) ||
		    ! is_object( $values ) )
		{
			throw new Exception( 'Illegal parameter' );
		}
		
		$this->values = $values;
		
		parent::__construct( $errormessage );
	}
	
	/**
	 * Returns true if the given value if within the given array/object
	 *
	 * @param mixed $value
	 * @return bool
	 */
	public function validate( $value )
	{
		$valid = false;
		foreach( $values as $v )
		{
			if( $value == $v )
			{
				$valid = true;
				break;
			}
		}
		
		return $valid;
	}
	
}

?>
