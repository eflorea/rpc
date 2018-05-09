<?php

namespace RPC;


use RPC\View\Cache;
use RPC\View\Filter\Form;

use RPC\Signal;


/**
 * Used to render HTML content
 *
 * @package View
 */
class View
{
	protected $controller;
	/**
	 * Assigned variables throughout the script
	 *
	 * @var array
	 */
	protected $_view_vars = array();

	/**
	 * Cache object
	 *
	 * @var object
	 */
	protected $_view_cache = null;

	/**
	 * Base directory for templates
	 *
	 * @var string
	 */
	protected $_view_tpldir = '';

	protected $_view_filters = array();


	protected $_view_errors = array();

	/**
	 * Default filters
	 *
	 * @var array
	 */
	protected $_view_defaultfilters = array
	(
		'\RPC\View\Filter\Form',
		'\RPC\View\Filter\Echoo',
		'\RPC\View\Filter\Render',
		'\RPC\View\Filter\Placeholder',
		'\RPC\View\Filter\Datagrid',
		'\RPC\View\Filter\Error'
	);

	/**
	 * Registered filters
	 *
	 * @var array
	 */
	protected $_view_registeredfilters = array();

	/**
	 * Template that is currently being rendered
	 *
	 * @var string
	 */
	protected $current_template = '';

	/**
	 * HTTP Request object
	 *
	 * @var RPC_HTTP_Request
	 */
	public $request;

	/**
	 * HTTP Response object
	 *
	 * @var RPC_HTTP_Response
	 */
	public $response;

	/**
	 * Class constructor which adds the default filters and some needed
	 * variables
	 */
	public function __construct( $dir, \RPC\View\Cache $cache )
	{
		if( ! is_dir( $dir ) )
		{
			throw new \Exception( 'The given path does not point to a directory' );
		}

		if( ! is_object( $cache ) )
		{
			throw new \Exception( 'You must set a cache object' );
		}

		$this->_view_tpldir = realpath( $dir );
		$this->_view_cache  = $cache;


		$this->setRequest( \RPC\HTTP\Request::getInstance() );
		$this->setResponse( \RPC\HTTP\Response::getInstance() );

		foreach( $this->_view_defaultfilters as $v )
		{
			$this->registerFilter( $v );
		}
	}

	/**
	 * Returns the set template directory
	 *
	 * @return string
	 */
	public function getTemplateDirectory()
	{
		return $this->_view_tpldir;
	}

	/**
	 * Set the template directory
	 *
	 * @param string $dir template directory path
	 */
	public function setTemplateDirectory($dir)
	{
		$this->_view_tpldir = realpath($dir);
	}

	/**
	 * Set the HTTP Response object
	 *
	 * @param RPC_HTTP_Response $response
	 */
	public function setResponse( $response )
	{
		$this->response = $response;
	}

	/**
	 * Set the HTTP Response object
	 *
	 * @param RPC_HTTP_Response $response
	 */
	public function getResponse( $response )
	{
		return $this->response;
	}

	/**
	 * Set the HTTP Request object
	 *
	 * @param RPC_HTTP_Request $request
	 */
	public function setRequest( $request )
	{
		$this->request = $request;
	}

	/**
	 * Returnt the HTTP Request object
	 *
	 * @param RPC_HTTP_Request $request
	 */
	public function getRequest()
	{
		return $this->request;
	}

	/**
	 * Escapes all HTML characters from the given string
	 *
	 * @param string $str
	 *
	 * @return string Escaped string
	 */
	public function escape( $str )
	{
		return htmlentities( $str, ENT_QUOTES, 'UTF-8', false );
	}

	/**
	 * Returns the array of defined variables
	 *
	 * @return array
	 */
	public function getVars()
	{
		return $this->_view_vars;
	}

	/**
	 * Registers a filter with the view, and in case the filter has external
	 * functionality (for example, the RPC_View_Error filter has to be accessed
	 * from outside, so that errors can be set and fetched) provides a name
	 * which will allow access to the object
	 *
	 * @param string $filter
	 * @param string $name
	 */
	public function registerFilter( $class_name )
	{
		$name = explode( '\\', $class_name );
		$name = strtolower( end( $name ) );

		$this->_view_registeredfilters[$name] = array( 'class_name' => $class_name, 'instance' => null );

		return $this;
	}

	/**
	 * Removes all filters registered in the constructor
	 */
	public function removeDefaultFilters()
	{
		foreach( $this->_view_defaultfilters as $v )
		{
			$name = explode( '\\', $v );
			$name = end( $name );

			unset( $this->_view_registeredfilters[$name] );
		}
	}

	/**
	 * Unregisters a filter from the queue
	 *
	 * @param string $filter
	 *
	 * @return RPC_View
	 */
	public function unregisterFilter( $filter )
	{
		$name = explode( '\\', $filter );
		$name = end( $name );

		unset( $this->_view_registeredfilters[$name] );

		return $this;
	}

	/**
	 * Returns the parser's cache object
	 *
	 * @return RPC_View_Cache
	 */
	public function getCache()
	{
		return $this->_view_cache;
	}

	/**
	 * Set the view cache
	 *
	 * @param RPC_View_Cache $cache the parser's cache object
	 */
	public function setCache($cache)
	{
		$this->_view_cache = $cache;
	}

	public function setVars( $vars )
	{
		$this->_view_vars = $vars;
	}

	/**
	 * Assigns a variable which will be available in the templates
	 *
	 * @param string $var
	 * @param mixed  $value
	 */
	public function __set( $var, $value )
	{
		if( strpos( $var, 'plugin_' ) === 0 )
		{
			throw new \Exception( 'You are trying to assign a value on an attribute which is reserved to a filter' );
		}

		$this->_view_vars[$var] = $value;
	}

	/**
	 * Returns an assigned variable or null if the variable does not exist
	 *
	 * @param string $var
	 *
	 * @return mixed
	 */
	public function __get( $var )
	{
		if( strpos( $var, 'plugin_' ) === 0 )
		{
			$var = substr( $var, 7 );

			if( empty( $this->_view_registeredfilters[$var]['instance'] ) )
			{
				$class = $this->_view_registeredfilters[$var]['class_name'];
				$this->_view_registeredfilters[$var]['instance'] = new $class;
			}

			return $this->_view_registeredfilters[$var]['instance'];
		}

		return isset( $this->_view_vars[$var] ) ? $this->_view_vars[$var] : null;
	}

	/**
	 * Checks to see if a certain variable has been assigned
	 *
	 * @param string $var
	 *
	 * @return bool
	 */
	public function __isset( $var )
	{
		return isset( $this->_view_vars[$var] );
	}

	/**
	 * Returns the output of the template
	 *
	 * @return string
	 *
	 * @see self::display
	 */
	public function render( $template )
	{
		ob_start();
		$this->display( $template );
		$output = ob_get_clean();

		//reset current template
		$this->setCurrentTemplate( null );

		return $output;
	}

	/**
	 * Parses the template, if it's not already cached and executes it
	 *
	 * @param string $template Path to template
	 */
	public function display( $template = null )
	{
		if( $this->current_template )
		{
			return false;
		}

		if( ! $template )
		{
			//get template automatic from controller
			$class = str_replace( array( 'APP\\Controller\\', '' ), array( '', '/' ), get_class( $this->controller ) );

			$class = str_replace( '\\', '/', $class );

			//check if folder exits
			if( ! is_dir( $this->_view_tpldir . '/' . $class ) )
			{
				throw new \Exception( "Template Folder doesn't exists: " . $this->_view_tpldir . '/' . $class );
			}

			$template = $class . '/' . $this->controller->current_method . '.php';

			//check if template exists based on the method called;
			if( ! is_file( $this->_view_tpldir . '/' . $class . '/' . $this->controller->current_method . '.php' ) )
			{
				throw new \Exception( "Template doesn't exists: " . $this->_view_tpldir . '/' . $class . '/' . $this->controller->current_method . '.php' );
			}
		}

		if( ! \RPC\Signal::emit( array( '\RPC\View', 'onBeforeRender' ), array( $this, $template ) ) )
		{
			return '';
		}

		$this->setCurrentTemplate( $template );

		$view = $this;

		extract( $this->_view_vars );

		/*
			"require"-ing the php file so that the PHP code is ran within the
			local context, which will make the variables (previously extracted)
			available without using $this->
		*/
		require $this->getFilteredFile( $template );

		\RPC\Signal::emit( array( '\RPC\View', 'onAfterRender' ), array( $this, $template ) );
	}

	/**
	 * Returns the path to the filtered template
	 *
	 * @return string
	 */
	public function getFilteredFile( $template )
	{
		$file = $this->getTemplateDirectory() . DIRECTORY_SEPARATOR . $template;

		if( ! file_exists( $file ) )
		{
			throw new \Exception( 'File "' . $file . '" does not exist' );
		}

		if( ! $this->getCache()->get( $file, $template ) )
		{
			$this->_view_filters = array();

			foreach( $this->_view_registeredfilters as & $v )
			{
				if( ! $v['instance'] )
				{
					$class = $v['class_name'];
					$v['instance'] = new $class();
				}

				$this->addFilter( $v['instance'] );
			}

			$this->getCache()->set( $file, $this->filter( file_get_contents( $file ) ), $template );
		}

		return $this->getCache()->get( $file, $template );
	}

	public function getCurrentTemplate( )
	{
		return $this->current_template;
	}

	public function setCurrentTemplate( $tpl )
	{
		$this->current_template = $tpl;
		return $this;
	}

	public function setController( $obj )
	{
		$this->controller = $obj;
	}

	/**
	 * Adds a new filter to the queue
	 *
	 * @param RPC_View_Filter $filter
	 *
	 * @return self
	 */
	public function addFilter( \RPC\View\Filter $filter )
	{
		$this->_view_filters[] = $filter;

		return $this;
	}

	/**
	 * Removes a filter from the queue
	 *
	 * @param RPC_View_Filter $filter
	 *
	 * @return RPC_View
	 */
	public function removeFilter( \RPC\View\Filter $filter )
	{
		$key = array_search( $filter, $this->_rpc_filters );
		if( $key !== false )
		{
			unset( $this->_rpc_filters[$key] );
		}
		return $this;
	}

	/**
	 * Returns an array of previously loaded filters
	 *
	 * @return array
	 */
	public function getFilters()
	{
		return $this->_view_filters;
	}

	/**
	 * Filters the source code through all registered filters
	 *
	 * @param string $source
	 *
	 * @return string
	 */
	public function filter( $source )
	{
		foreach( $this->_view_filters as $filter )
		{
			$source = $filter->filter( $source );
		}

		return $source;
	}


	public function newForm()
	{
		return new \RPC\View\Filter\Form();
	}


	public function getError( $id = '' )
	{
		if( isset( $this->_view_errors[$id] ) )
		{
			return $this->_view_errors[$id];
		}

		return '';
	}

	public function setErrors( $errors = array() )
	{
		if( count( $errors ) )
		{
			$this->_view_errors = array_replace( $this->_view_errors, $errors );
		}
	}

}

?>
