<?php

namespace RPC;

/**
 * Skeleton for all validators
 * 
 * @package Validate
 */
abstract class Validator
{
	
	/**
	 * Error message in case the validation fails
	 * 
	 * @var string
	 */
	protected $errormessage = '';
	
	/**
	 * Class constructor, which sets the error message for the validator
	 * 
	 * @param string $errormessage
	 */
	public function __construct( $errormessage = '' )
	{
		$this->setError( $errormessage );
	}
	
	/**
	 * Validates the input according to the specific rule
	 * 
	 * @return bool
	 */
	abstract public function validate( $value );
	
	/**
	 * Returns the given error message
	 * 
	 * @return string
	 */
	public function getError()
	{
		return $this->errormessage;
	}
	
	/**
	 * Sets an error message on the validator
	 * 
	 * @param string $errormessage
	 */
	public function setError( $errormessage )
	{
		$this->errormessage = $errormessage;
	}
	
}

?>
