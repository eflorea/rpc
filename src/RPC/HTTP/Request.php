<?php

namespace RPC\HTTP;



use RPC\HTTP\Cookie;

/**
 * Represents the request sent by the client
 *
 * @package HTTP
 */
class Request
{

	/**
	 * HEAD request method
	 */
	const METHOD_HEAD = 'head';

	/**
	 * GET request method
	 */
	const METHOD_GET  = 'get';

	/**
	 * POST request method
	 */
	const METHOD_POST = 'post';

	/**
	 * PUT request method
	 */
	const METHOD_PUT  = 'put';

	protected $uri;

	/**
	 * All the headers
	 *
	 * @var array
	 */
	protected $headers = array();

	/**
	 * The context path parameters in an indexed array
	 *
	 * @var array
	 */
	protected $pathparams = array();

	/**
	 * The context path parameters in an associative array
	 *
	 * @var array
	 */
	protected $pathparamsassoc = null;

	/**
	 * The default locale (eg. en-us) the application uses
	 *
	 * @var string
	 */
	protected $defaultlocale = 'en-us';

	/**
	 * The locales (eg. en-us, fi_fi, se_se etc) the application
	 * supports
	 *
	 * @var array
	 */
	protected $supportedlocales = array();

	protected $params = null;

	protected $router = null;

	/**
	 * Hash containing all the get variables sent with the request
	 *
	 * @var array
	 */
	public $get = array();

	/**
	 * Hash containing all the post variables sent with the request
	 *
	 * @var array
	 */
	public $post = array();

	/**
	 * Hash containing all the file variables sent with the request
	 *
	 * @var array
	 */
	public $files = array();

	/**
	 * Class constructor. The request is a singleton so this method is protected
	 * and the objects can't be initialized using the "new" operator
	 *
	 * @see self::create()
	 */
	protected function __construct()
	{
		$this->post  = $_POST;
		$this->get   = $_GET;
		$this->files = $_FILES;
	}

	/**
	 * Singletons can't be cloned
	 */
	protected function __clone() {}

	/**
	 * Returns an instance of RPC_HTTP_Response. Subsequent calls to this method
	 * will return the same object
	 *
	 * @return RPC_HTTP_Request
	 */
	public static function getInstance()
	{
		if( ! isset( $GLOBALS['_RPC_']['singleton']['request'] ) )
		{
			$c = __CLASS__;
			$GLOBALS['_RPC_']['singleton']['request'] = new $c;
		}

		return $GLOBALS['_RPC_']['singleton']['request'];
	}

	/**
	 * Returns a cookie object
	 *
	 * @param string $name
	 *
	 * @return RPC_HTTP_Cookie
	 */
	public function getCookie( $name )
	{
		return new \RPC\HTTP\Cookie( $name );
	}

	/**
	 * Returns the context path.
	 *
	 * Returns the portion of the request URI that indicates the
	 * context of the request.
	 *
	 * Example
	 * <code>
	 *  // if the request is done to http://www.example.com/user/list/params/client/3
	 *  $contextPath = $this->getContextPath();
	 *  // $contextPath will be "/user/list"
	 * </code>
	 *
	 * @return string The context path or null if there is none
	 */
	public function getContextPath()
	{
		$pathinfo = $this->getPathInfo();

		if( $pos = strpos( $pathinfo, '/params' ) !== false )
		{
			return '/';
		}

		return substr( $pathinfo, 0, $pos );
	}

	/**
	 * Gets the value of header. Returns the value of the specified request
	 * header or null if not found
	 *
	 * @param  string $name The name of the header
	 *
	 * @return string The value of the specified header
	 */
	public function getHeader( $name )
	{
		if( empty( $this->headers ) )
		{
			$this->headers = $this->getAllHeaders();
		}

		if( isset( $this->headers[$name] ) )
		{
			return $this->headers[$name];
		}

		return null;
	}

	/**
	 * Returns an associative array of all the header names and values of this
	 * request
	 *
	 * @return array
	 */
	public function getHeaders()
	{
		if( is_null( $this->headers == null ) )
		{
			if( function_exists( 'getallheaders' ) )
			{
				$this->headers = getallheaders();
			}
			else
			{
				/*
					Map so that the variables gotten from the environment when
					running as CGI have the same names as when PHP is an apache
					module
				*/
				$map = array
				(
					'HTTP_ACCEPT'           =>  'Accept',
					'HTTP_ACCEPT_CHARSET'   =>  'Accept-Charset',
					'HTTP_ACCEPT_ENCODING'  =>  'Accept-Encoding',
					'HTTP_ACCEPT_LANGUAGE'  =>  'Accept-Language',
					'HTTP_CONNECTION'       =>  'Connection',
					'HTTP_HOST'             =>  'Host',
					'HTTP_KEEP_ALIVE'       =>  'Keep-Alive',
					'HTTP_USER_AGENT'       =>  'User-Agent'
				);

				foreach( $_SERVER as $k => $v )
				{
					if( substr( $k, 0, 5 ) === 'HTTP_' )
					{
						$this->headers[$map[$k]] = $v;
					}
				}
			}
		}

		return $this->headers;
	}

	/**
	 * Sets the router on the request, which will allow the request
	 * to have a callable getParam method
	 *
	 * @param object $router
	 *
	 * @return RPC_HTTP_Request
	 */
	public function setRouter( \RPC\Router $router )
	{
		$this->router = $router;
		return $this;
	}

	/**
	 * Returns the value of the $param parameter or $default if
	 * it's not set
	 *
	 * @param string $param
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	public function getParam( $param, $default = null )
	{
		if( is_null( $this->params ) )
		{
			$this->params = $this->router->getParams();
		}

		return isset( $this->params[$param] ) ? $this->params[$param] : $default;
	}

	/**
	 * Gets the ip address.
	 *
	 * Returns the Internet Protocol (IP) address of the client that
	 * sent the request. Different from getRemoteAddress, also checks
	 * for HTTP_CLIENT_IP and HTTP_X_FORWARD_FOR
	 *
	 * @return string The ip address
	 */
	public function getIP()
	{
		$ip = null;

		if( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) )
		{
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) )
		{
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		elseif( ! empty( $_SERVER['REMOTE_ADDR'] ) )
		{
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		else
		{
			$ip = null;
		}

		return $ip;
	}

	/**
	 * Returns the name of the HTTP method with which this request
	 * was made: HEAD, POST, GET, PUT - this also maps to the METHOD_* constants
	 * defined on the class
	 *
	 * @return string
	 */
	public function getMethod()
	{
		return strtolower( $_SERVER['REQUEST_METHOD'] );
	}

	/**
	 * Returns the requested URI
	 *
	 * @return string
	 */
	public function getURI()
	{
		return $_SERVER['REQUEST_URI'];
	}

	/**
	 * Checks to see if the request has been made in a SSL protected medium
	 *
	 * @return bool
	 */
	public function isSecure()
	{
		if( isset( $_SERVER['HTTPS'] ) )
		{
			return strtolower( $_SERVER['HTTPS'] ) == 'on' ? true : false;
		}

		return false;
	}

	/**
	 * Checks if the request was made asynchronously (i.e. AJAX)
	 *
	 * @return bool
	 */
	public function isXHR()
	{
		return ! empty( $_SERVER['HTTP_X_REQUESTED_WITH'] );
	}


	/**
	 * Checks if this is ajax call (alias for isXHR)
	 */
	public function isAjax()
	{
		return $this->isXHR();
	}


	/**
	 * Gets the query string
	 *
	 * Returns the query string this is contained in the request
	 * URL after the path
	 *
	 * @return string The query string
	 */
	public function getQueryString()
	{
		return $_SERVER['QUERY_STRING'];
	}

	/**
	 * Returns the path info
	 *
	 * @return string
	 */
	public function getPathInfo()
	{
		return isset( $_SERVER['PATH_INFO'] ) ? $_SERVER['PATH_INFO'] : null;
	}

	/**
	 * Returns the real path to the requested resource
	 *
	 * @return string
	 */
	public function getPathTranslated()
	{
		return isset( $_SERVER['PATH_TRANSLATED'] ) ? $_SERVER['PATH_TRANSLATED'] : null;
	}

	/**
	 * Gets the protocol
	 *
	 * Returns the name and version of the protocol the request uses
	 * in the form protocol/majorVersion/minorVersion, for example
	 * HTTP/1.1
	 *
	 * @return string
	 */
	public function getProtocol()
	{
		return $_SERVER['SERVER_PROTOCOL'];
	}

	/**
	 * Gets the remove address.
	 *
	 * Returns the Internet Protocol (IP) address of the client that
	 * sent the request.
	 *
	 * @return   string  the ip address
	 */
	public function getRemoteAddress()
	{
		return isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : null;
	}

	/**
	 * Returns the fully qualified name of the client that sent the
	 * request.
	 *
	 * This is figured out by doing a dns server lookup of the ip address.
	 * If no host is associated with the address NULL is returned.
	 *
	 * @return string
	 */
	public function getRemoteHost()
	{
		if( $ra = $this->getRemoteAddress() )
		{
			$hostname = gethostbyaddr( $ra );

			if( $hostname == $ra )
			{
				return null;
			}
		}

		return $hostname;
	}

	/**
	 * Returns the server's IP address
	 *
	 * @return string
	 */
	public function getServerAddr()
	{
		return $_SERVER['SERVER_ADDR'];
	}

	/**
	 * Returns the host name of the server that received the request
	 *
	 * @return string
	 */
	public function getServerName()
	{
		return $_SERVER['SERVER_NAME'];
	}

	/**
	 * Returns the port number on which this request was received
	 *
	 * @return string
	 */
	public function getServerPort()
	{
		return $_SERVER['SERVER_PORT'];
	}

	/**
	 * Returns the server's hostname
	 *
	 * @return string
	 */
	public function getHostName()
	{
		return $_SERVER['HTTP_HOST'];
	}


	/**
	 * Validate CSRF_TOKEN from GET or POST
	 *
	 * @return boolean
	 */
	public function validateCSRF( $method = 'post' )
	{
		if( $this->getMethod() == $method && @$this->{$method}['csrf_token'] !== \RPC\Util::csrf() )
		{
            throw new \Exception( 'Token was not found. Please go back and refresh your page. Token: ' . @$this->{$method}['csrf_token'] );
		}

		return true;
	}


	/**
	 * Parse json input
	 */
	public function json()
	{
		try
		{
			return json_decode( file_get_contents( 'php://input' ), true );
		}
		catch( Exception $e )
		{
			return array();
		}
	}


}

?>
