<?php

namespace RPC\Validator;

use RPC\Validator;

class GT extends Validator
{
	
	protected $min;
	
	function __construct( $min, $errormessage = '' )
	{
		$this->min = $min;
		parent::__construct( $errormessage );
	}
	
	/**
	* Returns true if it is greater than $min, false otherwise.
	*
	* @param mixed $value
	* @return bool
	*/
	public function validate( $value )
	{
		return $value > $this->min;
	}
	
}

?>
