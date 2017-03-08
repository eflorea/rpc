<?php

namespace RPC\Controller;

use RPC\Controller\Dispatcher;
use RPC\Controller\Router;


/**
 * Front controller implementation
 * 
 * @package Controller
 */
class Front
{
	
	/**
	 * Command dispatcher
	 *
	 * @var RPC_Controller_Dispatcher
	 */
	protected $dispatcher = null;
	
	/**
	 * Request router
	 *
	 * @var RPC_Controller_Router
	 */
	protected $router = null;
	
	/**
	 * Class constructor which needs a dispatcher and a router
	 */
	public function __construct( $dispatcher, $router )
	{
		$this->setDispatcher( $dispatcher );
		$this->setRouter( $router );
	}
	
	/**
	 * Set a command dispatcher
	 *
	 * @param RPC_Controller_Dispatcher $dispatcher
	 */
	public function setDispatcher( RPC\Controller\Dispatcher $dispatcher )
	{
		$this->dispatcher = $dispatcher;
	}
	
	/**
	 * Returns the dispatcher
	 *
	 * @return RPC_Controller_Dispatcher
	 */
	public function getDispatcher()
	{
		return $this->dispatcher;
	}
	
	/**
	 * Set a request routing object
	 *
	 * @param RPC_Controller_Router $router
	 */
	public function setRouter( RPC\Controller\Router $router )
	{
		$this->router = $router;
	}
	
	/**
	 * Returns the router
	 *
	 * @return RPC_Controller_Router
	 */
	public function getRouter()
	{
		return $this->router;
	}
	
	/**
	 * Dispatches the request
	 */
	public function dispatch()
	{
		$this->getRouter()->route();
		
		$this->getDispatcher()->setRouter( $this->getRouter() );
		$this->getDispatcher()->dispatch();
	}
	
}

?>
