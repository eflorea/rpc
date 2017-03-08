<?php

namespace RPC\Controller;

use RPC\Object;
use Exception;

/**
 * Base class for all controllers
 * 
 * @package Controller
 * 
 * @todo Maybe this should extend from RPC_HTTP_Response? or maybe the view?
 * 
 * - if the command were to extend from the response, it could only be used for
 * serving html documents, while it should be agnostic
 */
abstract class Command
{
	
	/**
	 * Container for all controller variables
	 * 
	 * @var RPC_Controller_Context
	 */
	protected $context = null;
	
	/**
	 * Parameters for this command
	 * 
	 * @var array
	 */
	protected $params = array();
	
	/**
	 * Router object which handled the request
	 * 
	 * @var RPC_Controller_Router
	 */
	protected $router = null;
	
	public function __construct() /* {{{ */
	{
		$this->context = new RPC\Object();
	}
	/* }}} */
	
	/**
	 * Returns the current context
	 * 
	 * @return RPC_Controller_Context
	 */
	public function getContext() /* {{{ */
	{
		return $this->context;
	}
	/* }}} */
	
	/**
	 * Set a context for the current command
	 * 
	 * @param RPC_Controller_Context $context
	 */
	public function setContext( RPC\Controller\Context $context ) /* {{{ */
	{
		$this->context = $context;
	}
	/* }}} */
	
	/**
	 * Sets the router object which handled the request
	 * 
	 * @param RPC_Controller_Router $router
	 */
	public function setRouter( RPC\Controller\Router $router ) /* {{{ */
	{
		$this->router = $router;
	}
	/* }}} */
	
	/**
	 * Returns the router object which handled the request
	 * 
	 * @return RPC_Controller_Router
	 */
	public function getRouter() /* {{{ */
	{
		return $this->router;
	}
	/* }}} */
	
	/**
	 * Sets a variabile on the computer
	 * 
	 * @param string $key
	 * @param mixed  $value
	 */
	public function __set( $key, $value ) /* {{{ */
	{
		$this->context->$key = $value;
	}
	/* }}} */
	
	/**
	 * Returns a value set on the controller
	 * 
	 * @param string $key
	 * 
	 * @return mixed
	 */
	public function __get( $key ) /* {{{ */
	{
		return isset( $this->context->$key ) ? $this->context->$key : null;
	}
	/* }}} */
	
	/**
	 * Set parameters on this command
	 * 
	 * @param array $params
	 */
	public function setParams( $params ) /* {{{ */
	{
		$this->params = $params;
	}
	/* }}} */
	
	/**
	 * Return parameters set on this command
	 * 
	 * @return array
	 */
	public function getParams() /* {{{ */
	{
		return $this->params;
	}
	/* }}} */
	
	/**
	 * Returns a parameter set on this command
	 * 
	 * @param string $key
	 * @param mixed  $default
	 * @param array  $accepted
	 * 
	 * @return mixed
	 */
	public function getParam( $key, $default = null, $accepted = null ) /* {{{ */
	{
		if( array_key_exists( $key, $this->params ) )
		{
			if( $accepted )
			{
				return in_array( $this->params[$key], $accepted ) ? $this->params[$key] : $default;
			}
			
			return $this->params[$key];
		}
		
		return $default;
	}
	/* }}} */

	/** short chat for getParam **/
	public function param( $key, $default = null, $accepted = null )
	{
		return $this->getParam( $key, $default, $accepted );
	}
	
	/**
	 * Sets a variable for a one time read
	 * 
	 * @param string $name
	 * @param mixed  $value
	 */
	public function setFlash( $name, $value ) /* {{{ */
	{
		$_SESSION['__RPC__']['flash'][$name] = $value;
	}
	/* }}} */
	
	/**
	 * Returns and removes a previously set variable
	 * 
	 * @param string $name
	 * @param mixed  $default
	 * 
	 * @return mixed
	 */
	public function getFlash( $name, $default = null ) /* {{{ */
	{
		if( ! isset( $_SESSION['__RPC__']['flash'][$name] ) )
		{
			return $default;
		}
		
		$var = $_SESSION['__RPC__']['flash'][$name];
		unset( $_SESSION['__RPC__']['flash'][$name] );
		
		return $var;
	}
	/* }}} */
	
	/**
	 * Executes a new request
	 * 
	 * @param string $uri
	 * 
	 * @todo Maybe should also allow a controller/action/params array instead
	 * of an uri?
	 */
	public function forward( $uri ) /* {{{ */
	{
		throw new Exception( 'Not implemented' );
	}
	/* }}} */
	
}

?>
