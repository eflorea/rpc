<?php


namespace RPC\Validator;

use RPC\Validator;

class LT extends Validator
{
	
	protected $max;
	
	function __construct( $max, $errormessage = '' )
	{
		$this->max = $max;
		parent::__construct( $errormessage );
	}
	
	/**
	 * Returns true if it is less than $max, false otherwise.
	 *
	 * @param mixed $value
	 * @return bool
	 */
	public function validate( $value )
	{
		return $value < $this->max;
	}
	
}

?>
