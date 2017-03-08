<?php

namespace RPC\Validator;

use RPC\Validator;
use Regex;

/**
 * Matches the given string against a regex
 * 
 * @package Validate
 */
class Regex extends Validator
{
	
	/**
	 * Pattern against which the value will be matched
	 * 
	 * @var string
	 */
	protected $pattern;
	
	/**
	 * Sets the regex and error message in case the string doesn't match it
	 * 
	 * @param string $pattern
	 * @param string $errormessage
	 */
	function __construct( $pattern, $errormessage = '' )
	{
		if( empty( $pattern ) )
		{
			throw new Exception( 'You must supply a valid pattern' );
		}
		
		$this->pattern = $pattern;
		parent::__construct( $errormessage );
	}
	
	/**
	 * Matches the given string against the stored regex
	 * 
	 * @param mixed $value
	 * 
	 * @return bool
	 */
	public function validate( $value )
	{
		if( is_int( $value ) )
		{
			$value = '' . $value;
		}
		
		if( ! is_string( $value ) )
		{
			return false;
		}
		
		return preg_match( $this->pattern, $value );
	}
	
}

?>
