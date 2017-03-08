<?php

namespace RPC;


/**
 * Class <code>RPC_Loop</code> implements an iteration that can be influenced
 * by a manipulator as defined by class <code>RPC_Loop_Manipulator</code>.
 * 
 * To start the loop, just call the static method <code>run</code> with an
 * iterator and a loop manipulator. The method returns the total number of
 * objects that was processed.
 * 
 * See class <code>RPC_Loop_Manipulator</code> for more information on how to
 * influence loops and why this simple class is extremely useful.
 * 
 * @see RPC_Loop_Manipulator
 */
class Loop
{
	
	/**
	 * Run a loop on an iterator and a manipulator and return the number of
	 * items processed
	 * 
	 * @param $iterator    The <code>Iterator</code> to run the loop on
	 * @param $manipulator The <code>RPC_Loop_Manipulator</code> to use
	 * 
	 * @return int
	 */
	public static function run( Iterator $iterator, RPC_Loop_Manipulator $manipulator )
	{
		$index = 0;
		$iterator->rewind();
		
		if( $iterator->valid() )
		{
			$manipulator->prepare();
		}
		
		foreach( $iterator as $current )
		{
			if( $index )
			{
				$manipulator->between( $index );
			}
			
			$manipulator->current( $current, $index++ );
		}
		
		if( $index )
		{
			$manipulator->finish( $index );
		}
		
		return $index;
	}
	
}

?>
