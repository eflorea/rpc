<?php

namespace RPC\Validator;

use RPC\Validator;

class Alternation extends Validator
{
	
	/**
	 * Given validators
	 *
	 * @var RPC_Validator
	 */
	protected $alternates = array();
	
	/**
	 * Adds the given validators to the object
	 */
	public function __construct()
	{
		if( func_num_args() )
		{
			$this->alternates = func_get_args();
		}
	}
	
	/**
	 * Another method to add validators to the object
	 *
	 * @param RPC_Validator $validator
	 * @return RPC_Validator_Alternation
	 */
	public function add( RPC\Validator $validator )
	{
		$this->alternates[] = $validator;
	}
	
	/**
	 * Validates the given validators and returns true if one of them makes it.
	 * In case they all fail, the first error message encountered will be
	 * returned
	 *
	 * @return bool
	 */
	public function validate( $value )
	{
		foreach( $this->alternates as $validator )
		{
			if( $validator->validate( $value ) )
			{
				return true;
			}
			else
			{
				if( ! $this->getError() )
				{
					$this->setError( $validator->getError() );
				}
			}
		}
		
		return false;
	}
	
}

?>
