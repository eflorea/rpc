<?php

namespace RPC;



use RPC\HTTP\Request;
use RPC\HTTP\Response;
use RPC\Regex;

class Router
{

	protected $rewrite_rules = array();

	protected $controller;
	protected $action;

	protected $request;
	protected $response;

	protected $params;

	
	public function __construct()
	{
		$this->controller 	= 'Home';
		$this->action 		= 'index';

		$this->request = \RPC\Request::getInstance();
		$this->response = \RPC\HTTP\Response::getInstance();
	}

	public function setRewriteRules( $rules )
	{
		$this->rewrite_rules = array_replace( $this->rewrite_rules, $rules );
	}

	public function run()
	{
		$uri = strtolower( trim( $this->request->getURI(), '/' ) );		
		
		/**
		 * If the requested URI does not have a path info, then the default
		 * command and action will be returned
		 */
		if( $uri && $this->rewrite_rules )
		{			
			/**
			 * If the string has some GET parameters, they will be ignored during
			 * the routing process
			 */
			if( ( $pos = strpos( $uri, '?' ) ) !== false )
			{
				$uri = substr( $uri, 0, $pos );
			}

			foreach( $this->rewrite_rules as $rule => $arr )
			{
				$matches = array();
				
				$regex = new \RPC\Regex( '#' . str_replace( '#', '\#', $rule ) . '#' );

				if( $regex->match( $uri, $matches ) )
				{
					$l = count( $matches[0] );
					
					if( $l == 1 )
					{
						$uri = $arr;
					}
					else
					{
						for( $replace = array(), $search = array(), $i = 1, $l = count( $matches[0] ); $i < $l; $i++ )
						{
							$replace[] = $matches[0][$i][0];
							$search[]  = '$' . $i;
						}
						
						$uri = str_replace( $search, $replace, $arr );
					}
				
					break;
				}
			}

		}

		if( $uri )
		{
			if( strpos( $uri, '/params' ) !== false )
			{
				list( $uri, $params ) = explode( '/params', $uri );
				
				$params = explode( '/', substr( $params, 1 ) );
				for( $i = 0, $l = count( $params ); $i < $l; $i += 2 )
				{
					$this->params[$params[$i]] = @$params[$i + 1];
				}
			}

			$uri = trim( $uri, '/' );
			
			if( $uri )
			{
				$cmdparts = explode( '/', $uri );

				$cmdkey = end( $cmdparts );
				reset( $cmdparts );
				array_pop( $cmdparts );

				if( count( $cmdparts ) )
				{
					foreach( $cmdparts as $k => $v )
					{
						$cmdparts[$k] = ucfirst( $v );
					}
					$this->controller = implode( '\\' , $cmdparts );
					$this->action = $cmdkey;
				}
				else
				{
					$cmdparts = array( ucfirst( $cmdkey ) );
					$this->controller = implode( '\\' , $cmdparts );
				}
			}
		}

		$command = 'APP\\Controller\\' . $this->controller;

		$command = new $command;
		if( ! $command instanceof \RPC\Controller )
		{
			throw new \Exception( 'Class "' . ( is_object( $command ) ? get_class( $command ) : $command ) . '" has to inherit from RPC_Command' );
		}
		
		if( ! in_array( $_SERVER['REQUEST_METHOD'], array( 'GET', 'POST', 'PUT' ) ) )
		{
			throw new \Exception( 'Request Method is not valid: ' . $_SERVER['REQUEST_METHOD'] );
		}

		$request = $_SERVER['REQUEST_METHOD'];
		

		$methodname = $this->action . $request;
		
		if( ! is_callable( array( $command, $methodname ), false ) )
		{
			throw new \Exception( 'Class "' . get_class( $command ) . '" was found but method "' . $methodname . '" could not be executed' );
		}
		
		/*
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


		/*
			Validate CSRF
			- default it will validate POST method
			- if called like validateCSRT( 'get' ) it will validate the CSRF from get
		*/
		if( ! isset( $command->ignore_csrf ) )
		{
			//$request->validateCSRF();
		}

		$command->request  = $this->request;
		$command->response = $this->response;

		$command->methodCalled = $this->action;
		
		if( is_callable( array( $command, 'setup' ), false ) )
		{
			$command->setup( $this->request, $this->response );
		}
		
		if( is_callable( array( $command, $this->action . 'Setup' ), false ) )
		{
			$command->{$this->action . 'Setup'}( $this->request, $this->response );
		}
		
		$command->$methodname( $this->request, $this->response );
		
		if( is_callable( array( $command, $this->action . 'Teardown' ), false ) )
		{
			$command->{$this->action . 'Teardown'}( $this->request, $this->response );
		}

		if( ! $command->template )
		{
			$command->getView()->display();
		}

		if( is_callable( array( $command, 'teardown' ), false ) )
		{
			$command->teardown( $this->request, $this->response );
		}

	}
}

?>
