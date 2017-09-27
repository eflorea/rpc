<?php

namespace RPC;


/**
 * This class is a simple implementation of the slots/signals concept.
 * 
 * To briefly summarize, they allow you to bind a signal to one or more methods
 * or functions and/or signals. When a signal is "emitted", all slots bound to
 * it are called. When a signal is called by another signal, the called signal
 * is emitted, calling the slots bound to it and so on.
 * 
 * Any parameters passed when the signal is emitted are passed to the slots and
 * signals that that are called. This easily allows you to write handler
 * functions (slots) and bind them to events (signals) as needed - without
 * having to make explicit function calls or rewrite handler functions just to
 * accomodate minor modifications in how a function is called.
 * 
 * Some uses include message passing, logging and error handling.
 * 
 * @package Core
 */
class Signal
{
	
	/**
	 * When a registered callback returns this value, the emit function
	 * will return false and all the following registered callbacks will
	 * not be called anymore
	 * 
	 * @var int
	 */
	const STOP_SIGNAL = 1;
	
	/**
	 * When a registered callback returns this value the emit function
	 * will return true but all the following callbacks will not be
	 * called anymore
	 * 
	 * @var int
	 */
	const STOP_BROADCAST = 2;
	
	/**
	 * Connects a signal to a slot. The emitent is an object which sends the
	 * signal, while the receiver is a class method or a function which will be
	 * executed when the signal is emitted
	 * 
	 * <code>
	 * 
	 * RPC_Signal::connect( array( 'RPC_View', 'render_start' ), array( 'RPC_View_Cache', 'check' ) );
	 * RPC_Signal::connect( array( 'RPC_View', 'render_start' ), 'view_cache_check' );
	 * RPC_Signal::connect( 'some_signal', array( 'RPC_Some_Object', 'somemethod' ) );
	 * RPC_Signal::connect( 'some_signal', 'some_method' );
	 * 
	 * </code>
	 * 
	 * @param string|array $signal
	 * @param string|array $slot
	 */
	public static function connect( $signal, $slot )
	{
		if( is_array( $signal ) )
		{
			$emitent = $signal[0];
			$signal  = $signal[1];
			
			if( is_object( $emitent ) )
			{
				$emitent = get_class( $emitent );
			}
			
			$signal = $emitent . '_' . $signal;
		}
		
		$GLOBALS['_RPC_']['signals'][$signal][] = array( 'type' => 'callback', 'slot' => $slot );
	}
	
	/**
	 * Same as connect, only that instead of registering a callback,
	 * it registers another signal that will be emitted when $signal1
	 * is emitted
	 * 
	 * @param string|array $signal1
	 * @param string|array $signal2
	 */
	public static function connectSignal( $signal1, $signal2 )
	{
		if( is_array( $signal ) )
		{
			$emitent = $signal[0];
			$signal  = $signal[1];
			
			if( is_object( $emitent ) )
			{
				$emitent = get_class( $emitent );
			}
			
			$signal = $emitent . '_' . $signal;
		}
		
		$GLOBALS['_RPC_']['signals'][$signal][] = array( 'type' => 'signal', 'slot' => $slot );
	}
	
	/**
	 * Emits a certain signal and all the connected slots are executed with the
	 * passed parameters
	 * 
	 * <code>
	 * 
	 * RPC_Signal::emit( array( $this, 'some_signal' ), array( $param1, $param2 ) );
	 * RPC_Signal::emit( array( 'RPC_View', 'some_signal' ), array( $param1 ) );
	 * 
	 * </code>
	 * 
	 * @param string|array $signal
	 * @param array        $params
	 * 
	 * @return bool
	 */
	public static function emit( $signal, $params = array() )
	{
		if( is_array( $signal ) )
		{
			$emitent = $signal[0];
			$signal  = $signal[1];
			
			if( is_object( $emitent ) )
			{
				$emitent = get_class( $emitent );
			}
			
			$signal = $emitent . '_' . $signal;
		}
		
		if( ! empty( $GLOBALS['_RPC_']['signals'][$signal] ) )
		{
			foreach( $GLOBALS['_RPC_']['signals'][$signal] as $slot )
			{
				if( $slot['type'] == 'callback' )
				{
					$ret = call_user_func_array( $slot['slot'], $params );
				}
				else
				{
					$ret = \RPC\Signal::emit( $slot['slot'], $params );
				}
				
				if( $ret === \RPC\Signal::STOP_BROADCAST )
				{
					break;
				}
				elseif( $ret === \RPC\Signal::STOP_SIGNAL )
				{
					return false;
				}
			}
		}
		
		return true;
	}
	
}

?>
