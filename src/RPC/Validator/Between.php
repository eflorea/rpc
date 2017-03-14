<?php

namespace RPC\Validator;

use RPC\Validator;


class Between extends Validator
{
	
	protected $min;
	
	protected $max;
	
	function __construct( $min = null, $max = null, $errormessage = '' )
	{
		if( is_null( $min ) ||
		    is_null( $max ) )
		{
			throw new \Exception( 'Invalid arguments' );
		}
		
		$this->min = $min;
		$this->max = $max;
		
		parent::__construct( $errormessage );
	}
	
	/**
	 * Returns true if it is greater than or equal to $min and less
	 * than or equal to $max, false otherwise.
	 *
	 * @param int $value
	 * @return bool
	 */
	public function validate( $value )
	{
		if( ! is_numeric( $value ) )
		{
			return false;
		}
		
		return ( $this->min <= $value ) &&
		       ( $this->max >= $value );
	}
	
}

?>
