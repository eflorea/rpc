<?php

namespace RPC\Validator;

use RPC\Validator;

class Equal extends Validator
{
	
	/**
	 * Comparison value
	 *
	 * @var mixed
	 */
	protected $value;
	
	/**
	 * Comparison type
	 *
	 * @var bool
	 */
	protected $strict;
	
	/**
	 * Class constructor
	 *
	 * @param mixed  $value        Value to be compared
	 * @param bool   $strict       Whether the comparison will be strict or not
	 * @param string $errormessage Error message if the values are not equal
	 */
	function __construct( $value, $strict, $errormessage = '' )
	{
		$this->value = $value;
		$this->strict = (bool) $strict;
		
		parent::__construct( $errormessage );
	}
	
	/**
	 * Returns true if it is equal to <var>$this->value</var>, false otherwise
	 *
	 * @param mixed $value
	 * 
	 * @return bool
	 */
	public function validate( $value )
	{
		return $this->strict ? $value === $this->value : $value == $this->value;
	}
	
}

?>
