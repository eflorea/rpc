<?php

namespace RPC\Validator;

use RPC\Validator;
use RPC\Date;

class Date extends Validator
{
	
	/**
	 * Date format
	 *
	 * @var string
	 */
	protected $format = 'Y-m-d';
	
	public function __construct( $format = 'Y-m-d', $errormessage = '' )
	{
		$this->format = $format;
		$this->setError( $errormessage );
	}
	
	/**
	 * Returns true if it is a valid date, false otherwise.
	 *
	 * @param mixed $value
	 * @return bool
	 */
	public function validate( $value )
	{
		return RPC\Date::validDate( $value, $this->format );
	}
	
}

?>
