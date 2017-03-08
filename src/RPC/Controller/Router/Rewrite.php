<?php

namespace RPC\Controller\Router;

use RPC\Controller\Router;
use RPC\HTTP\Request;
use Exception;


/**
 * Default implementation for the router which besides the inteface specific
 * functionality adds the possibility to rewrite routes
 *  
 * @package Controller
 */
class Rewrite implements Router
{
	
	/* Protected variables {{{ */
	
	/**
	 * Requested command object
	 * 
	 * @var string
	 */
	protected $_rpc_command = '';

	/**
	 * Requested action on the command
	 * 
	 * @var string
	 */
	protected $_rpc_action = '';
	
	/**
	 * Given parameters
	 * 
	 * @var array
	 */
	protected $_rpc_params = array();
	
	/**
	 * Array of rewrite rules
	 * 
	 * @var array
	 */
	protected $_rpc_rules = array();
	
	/**
	 * Default command object when none is requested
	 * 
	 * @var string
	 */
	protected $_rpc_default_command = 'index';
	
	/**
	 * Default action to be executed on the command when none is requested
	 * 
	 * @var string
	 */
	protected $_rpc_default_action  = 'index';
	
	/**
	 * HTTP Request object
	 * 
	 * @var RPC_HTTP_Request
	 */
	protected $_rpc_request = null;
	
	/**
	 * HTTP Response object
	 *
	 * @var RPC_HTTP_Response
	 */
	protected $_rpc_response = null;
	
	/**
	 * Base URI
	 * 
	 * @var string
	 */
	protected $_rpc_base_uri = '/';
	
	/* }}} */
	
	/**
	 * Class constructor which sets up the default command and action
	 */
	public function __construct() /* {{{ */
	{
		$this->_rpc_command = $this->_rpc_default_command;
		$this->_rpc_action  = $this->_rpc_default_action;

		$this->setRequest( RPC\HTTP\Request::getInstance() );
		$this->setResponse( RPC\HTTP\Response::getInstance() );
	}
	/* }}} */
	
	/**
	 * The base URI will be stripped from the current URI
	 *
	 * @return string
	 */
	public function getBaseURI() /* {{{ */
	{
		return $this->_rpc_base_uri;
	}
	/* }}} */
	
	/**
	 * Sets the relative URI of the application, which will
	 * be stripped when routing
	 *
	 * @param $base_uri string
	 *
	 * @return RPC_Controller_Router_Rewrite
	 */
	public function setBaseURI( $base_uri ) /* {{{ */
	{
		$this->_rpc_base_uri = $base_uri;

		return $this;
	}
	/* }}} */
	
	/**
	 * Returns the params array from the request, that is what is after
	 * <code>params/</code> in the URI
	 * 
	 * @return array
	 */
	public function getParams() /* {{{ */
	{
		return $this->_rpc_params;
	}
	/* }}} */
	
	/**
	 * Return the requested command object name
	 * 
	 * @return string
	 */
	public function getCommand() /* {{{ */
	{
		return $this->_rpc_command;
	}
	/* }}} */
	
	/**
	 * Returns the requested action name
	 * 
	 * @return string
	 */
	public function getAction() /* {{{ */
	{
		return $this->_rpc_action;
	}
	/* }}} */
	
	/**
	 * Adds one or more rewrite rules to the router
	 * 
	 * <code>
	 * $router->rewrite( '/download/([0-9]+)', '/download/view/params/id/$1' );
	 * </code>
	 * 
	 * @param string|array $match
	 * @param string|null  $target
	 * 
	 * @return RPC_Controller_Router_Rewrite
	 */
	public function rewrite( $match, $target = null, $redirect = false ) /* {{{ */
	{
		if( is_string( $match ) )
		{
			$this->_rpc_rules[$match] = array( $target, (bool)$redirect );
		}
		else
		{
			$routes = $match;
			
			foreach( $routes as $route )
			{
				if( empty( $route['match'] ) )
				{
					throw new Exception( 'You must provide a regex to match against the incoming requests' );
				}
				
				if( empty( $route['target'] ) )
				{
					throw new Exception( 'You must provide a target location for redirection' );
				}
				
				if( empty( $route['redirect'] ) )
				{
					$route['redirect'] = false;
				}
				
				$this->_rpc_rules[$route['match']] = array( $route['target'], (bool)$route['redirect'] );
			}
		}
		
		return $this;
	}
	/* }}} */
	
	/**
	 * Set the request object
	 * 
	 * @param RPC_HTTP_Request $request
	 */
	public function setRequest( \RPC\HTTP\Request $request ) /* {{{ */
	{
		$this->_rpc_request = $request;
	}
	/* }}} */
	
	/**
	 * Returns the request object
	 * 
	 * @return RPC_HTTP_Request
	 */
	public function getRequest() /* {{{ */
	{
		return $this->_rpc_request === null ? \RPC\HTTP\Request::getInstance() : $this->_rpc_request;
	}
	/* }}} */

	public function setResponse( \RPC\HTTP\Response $response ) /* {{{ */
	{
		$this->_rpc_response = $response;
	}
	/* }}} */

	public function getResponse() /* {{{ */
	{
		return $this->_rpc_response;
	}
	/* }}} */
	
	/**
	 * Execute the routing process
	 */
	public function route() /* {{{ */
	{
		$uri = rtrim( $this->getRequest()->getURI(), '/' );
		$base_uri = rtrim( $this->getBaseUri(), '/' );

		if( $uri && $base_uri && strpos( $uri, $base_uri ) === 0 )
		{
		    $uri = substr( $uri, strlen( $base_uri ) );
		}
		
		/**
		 * If the requested URI does not have a path info, then the default
		 * command and action will be returned
		 */
		if( $uri )
		{
			$uri = ltrim( $uri, '/' );
			
			/**
			 * If the string has some GET parameters, they will be ignored during
			 * the routing process
			 */
			if( ( $pos = strpos( $uri, '?' ) ) !== false )
			{
				$uri = substr( $uri, 0, $pos );
			}

			foreach( $this->_rpc_rules as $rule => $arr )
			{
				$rewrite  = $arr[0];
				$redirect = $arr[1];
				
				$matches = array();
				
				$regex = new RPC_Regex( '#' . str_replace( '#', '\#', $rule ) . '#' );

				if( $regex->match( $uri, $matches ) )
				{
					$l = count( $matches[0] );
					
					if( $l == 1 )
					{
						$uri = $rewrite;
					}
					else
					{
						for( $replace = array(), $search = array(), $i = 1, $l = count( $matches[0] ); $i < $l; $i++ )
						{
							$replace[] = $matches[0][$i][0];
							$search[]  = '$' . $i;
						}
						
						$uri = str_replace( $search, $replace, $rewrite );
					}
					
					if( $redirect )
					{
						$this->getResponse()->redirect( $uri );
					}
				
					break;
				}
			}
			
			$this->routeURI( $uri );
		}
	}
	/* }}} */
	
	/**
	 * Helper function which segregates the command, action and params from
	 * the given URI
	 * 
	 * @param string $uri
	 */
	protected function routeURI( $uri ) /* {{{ */
	{
		/**
		 * Params should be passed as a key/value string, after the /params/
		 * marker
		 */
		if( strpos( $uri, '/params' ) !== false )
		{
			list( $uri, $params ) = explode( '/params', $uri );
			
			$params = explode( '/', substr( $params, 1 ) );
			for( $i = 0, $l = count( $params ); $i < $l; $i += 2 )
			{
				$this->_rpc_params[$params[$i]] = @$params[$i + 1];
			}
		}
		$uri = trim( $uri, '/' );
		if( ( $pos = strrpos( $uri, '/' ) ) !== false &&
			$pos !== 0 )
		{
			$this->_rpc_command = substr( $uri, 0, $pos );
			$this->_rpc_action  = substr( $uri, $pos + 1 );
		}
		else
		{
			if( $uri )
			{
				$this->_rpc_command = $uri;
			}
			else
			{
				$this->_rpc_command = 'index';
			}
		}
	}
	/* }}} */
	
}

?>
