<?php

namespace RPC\Controller;


use RPC\Signal;
use Exception;

/**
 * Instantiates a command and executes the action based on the routed request
 * 
 * @package Controller
 */
class Dispatcher
{
	
	/**
	 * Directory to search for commands
	 *
	 * @var string
	 */
	protected $commanddir = '';
	
	/**
	 * Router object
	 *
	 * @var RPC_Controller_Router
	 */
	protected $router = null;
	
	/**
	 * Response object
	 * 
	 * @var RPC_HTTP_Response
	 */
	protected $response = null;

	/**
	 * Request object
	protected $request = null;

	/**
	 * Class constructor which sets the path where to look for commands
	 */
	public function __construct( $dir )
	{
		$this->setCommandDirectory( $dir );
	}
	
	/**
	 * Sets a directory to search for commands
	 *
	 * @param string $dir
	 * 
	 * @todo Maybe allow to search in multiple directories?
	 */
	public function setCommandDirectory( $dir )
	{
		$this->commanddir = $dir;
	}
	
	/**
	 * Returns the directory where commands will be searched
	 *
	 * @return string
	 */
	public function getCommandDirectory()
	{
		return $this->commanddir;
	}
	
	/**
	 * Set a router object
	 *
	 * @param RPC_Controller_Router $router
	 */
	public function setRouter( $router )
	{
		$this->router = $router;
	}
	
	/**
	 * Returns the current router object
	 *
	 * @return RPC_Controller_Router
	 */
	public function getRouter()
	{
		return $this->router;
	}
	
	/**
	 * Sets response object
	 * 
	 * @param RPC_HTTP_Response $response
	 */
	public function setResponse( $response )
	{
		$this->response = $response;
	}
	
	/**
	 * Returns the response object
	 * 
	 * @return RPC_HTTP_Response
	 */
	public function getResponse()
	{
		return $this->response ? $this->response : \RPC\HTTP\Response::getInstance();
	}
	
	/**
	 * Instantiates the command and executes the action
	 */
	public function dispatch()
	{
		/*
			Builds the path to the command file by the following rules:
			everything is lower case except the command file name should have
			the first letter upper cased
		*/
		$cmdparts = explode( '/', strtolower( $this->getRouter()->getCommand() ) );

		end( $cmdparts );
		$cmdkey = key( $cmdparts );
		
		/*
			Getting the command class name
		*/
		$command = $cmdparts[$cmdkey] = ucfirst( $cmdparts[$cmdkey] );

		/*
			Checking if the corresponding file exists in the previously
			specified directory
		*/
		$path = implode( '/', $cmdparts ) . '.php';

		if( ! is_file( $this->getCommandDirectory() . '/' . $path ) )
		{
			$tmpcommand = $path;
			$tmpaction = strtolower( $this->getRouter()->getAction() );
			$tmpcommandfile = $tmpcommand . '/' . ucfirst( $tmpaction ) . '.php';
			if( is_file( $this->getCommandDirectory() . '/' . $tmpcommandfile ) )
			{
				$path = $tmpcommandfile;
				$action = 'index';
				$command = ucfirst( $tmpaction );
			}
			else
			{
				RPC\Signal::emit( array( 'RPC\Controller\Command', '404' ) );

				throw new Exception( 'File "' . $this->getCommandDirectory() . '/' . $path . '" does not exist. Request: ' . @$_SERVER['PATH_INFO'] );
			}
		}

		require $this->getCommandDirectory() . '/' . $path;

		/*
			A class having the requested command name plus the "Command" string
			appended should be defined in that file
		*/
		if( ! class_exists( $command ) )
		{
			throw new Exception( 'File "' . $path . '" has been loaded but class "' . $command . 'Command" was not found' );
		}
		
		$command = new $command;
		if( ! $command instanceof \RPC\Controller\Command )
		{
			throw new Exception( 'Class "' . ( is_object( $command ) ? get_class( $command ) : $command ) . '" has to inherit from RPC_Controller_Command' );
		}
		
		$command->setRouter( $this->getRouter() );
		
		if( ! isset( $action ) )
		{
			$action = $this->getRouter()->getAction();
		}

		if( ! in_array( $_SERVER['REQUEST_METHOD'], array( 'GET', 'POST' ) ) )
		{
			$request = 'GET';
		}
		else
		{
			$request = $_SERVER['REQUEST_METHOD'];
		}
		

		$methodname = $action . $request;
		
		if( ! is_callable( array( $command, $methodname ), false ) )
		{
			throw new Exception( 'Class "' . $command . '" was found but method "' . $methodname . '" could not be executed' );
		}
		
		$command->setParams( $this->getRouter()->getParams() );
		
		/*
			Methods are called depending on the request type - the type
			name is appended to the method name:
				editGET
				editPOST
			
			Also, if a method having "setup" appended to its name exists and/or
			one having "teardown" appended, it will be executed before,
			respectively after, either GET or POST methods:
				editSetup
				editTeardown
		*/
		
		$request  = $this->getRouter()->getRequest();
		$response = $this->getResponse();

		/*
			Validate CSRF
			- default it will validate POST method
			- if called like validateCSRT( 'get' ) it will validate the CSRF from get
		*/
		if( ! isset( $command->ignore_csrf ) )
		{
			$request->validateCSRF();
		}
		
		if( is_callable( array( $command, 'setup' ), false ) )
		{
			$command->setup( $request, $response );
		}
		
		if( is_callable( array( $command, $action . 'Setup' ), false ) )
		{
			$command->{$action . 'Setup'}( $request, $response );
		}
		
		$command->$methodname( $request, $response );
		
		if( is_callable( array( $command, $action . 'Teardown' ), false ) )
		{
			$command->{$action . 'Teardown'}( $request, $response );
		}
		
		if( is_callable( array( $command, 'teardown' ), false ) )
		{
			$command->teardown( $request, $response );
		}
	}
	
}

?>
