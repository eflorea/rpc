<?php

namespace RPC;



/**
 * Session class which provides a few convenience methods. It is
 * designed to allow users to work with $_SESSION.
 * 
 * @package Core
 */
class Session
{

	/**
	 * Timestamp when the session would expire
	 * 
	 * @var int
	 */
	protected $_rpc_expire = 0;
	
	/**
	 * The path for which the session would be available
	 * 
	 * @var string
	 */
	protected $_rpc_path = '/';
	
	/**
	 * The domain for which the session would be available
	 * 
	 * @var string
	 */
	protected $_rpc_domain = '';
	
	/**
	 * Whether the session should be sent only over a HTTPS connection
	 * 
	 * @var bool
	 */
	protected $_rpc_secure = 0;
	
	/**
	 * Whether the session will be available only over HTTP connections
	 * 
	 * @var bool
	 */
	protected $_rpc_httponly = false;
	
	/**
	 * Class instance
	 * 
	 * @var RPC_Session
	 */
	protected static $_rpc_instance = null;
	
	/**
	 * Class cannot be instantiated using the new operator
	 * 
	 * @see self::getInstance()
	 */
	public function __construct() {}
	
	/**
	 * Singleton
	 * 
	 * @return RPC_Session
	 */
	public static function getInstance()
	{
		if( ! isset( self::$instance ) )
		{
			$c = __CLASS__;
			self::$_rpc_instance = new $c;
			self::$_rpc_instance->setDefaultCookieParams();
		}
		
		return self::$_rpc_instance;
	}
	
	/**
	 * Singleton
	 */
	public function __clone()
	{
		throw new \Exception( 'Singletons can\'t be cloned' );
	}
	
	/**
	 * Sets a name for the current's application session cookie
	 * Each application should have a different session name
	 * 
	 * @param string name
	 * 
	 * @return RPC_Session
	 */
	public function setName( $name )
	{
		session_name( $name );
		
		return $this;
	}
	
	/**
	 * Returns the session's cookie name
	 * 
	 * @return string
	 */
	public function getName()
	{
		return session_name();
	}
	
	/**
	 * Specifies the folder where sessions will be stored, when a file system
	 * adapter is used
	 * 
	 * @return RPC_Session
	 */
	public function setSavePath( $path )
	{
		session_save_path( $path );
		
		return $this;
	}
	
	/**
	 * Sets a timestamp when the session will expire
	 * 
	 * @param int $expire
	 * 
	 * @return RPC_Session
	 */
	public function setExpire( $expire )
	{
		$this->_rpc_expire = $expire;
		
		return $this;
	}
	
	/**
	 * Sets the path for which the session will be available
	 * 
	 * @param string $path
	 * 
	 * @return RPC_Session
	 */
	public function setPath( $path )
	{
		$this->_rpc_path = $path;
		
		return $this;
	}
	
	/**
	 * Sets the domain for which the session will be available
	 * 
	 * @param string $domain
	 * 
	 * @return RPC_Session
	 */
	public function setDomain( $domain )
	{
		$this->_rpc_domain = $domain;
		
		return $this;
	}
	
	/**
	 * Sets whether the session will be sent only over a HTTPS connection
	 * 
	 * @param bool $secure
	 * 
	 * @return RPC_Session
	 */
	public function setSecure( $secure )
	{
		$this->_rpc_secure = $secure;
		
		return $this;
	}
	
	/**
	 * Sets whether the session will be sent only over HTTP connections
	 * 
	 * @param bool $httponly
	 * 
	 * @return RPC_Session
	 */
	public function setHTTPOnly( $httponly )
	{
		$this->_rpc_httponly = $httponly;
		
		return $this;
	}
	
	/**
	 * Set current cache expire
	 * 
	 * @param int $expire Expire time in seconds
	 * 
	 * @return RPC_Session
	 */
	public function setCacheExpire( $expire )
	{
		session_cache_expire( round( $expire / 60 ) );
		
		return $this;
	}
	
	/**
	 * The cache limiter defines which cache control HTTP headers are sent
	 * to the client. These headers determine the rules by which the page
	 * content may be cached by the client and intermediate proxies.
	 * Setting the cache limiter to nocache disallows any client/proxy
	 * caching. A value of public permits caching by proxies and the client,
	 * whereas private disallows caching by proxies and permits the client
	 * to cache the contents.
	 * 
	 * In private mode, the Expire header sent to the client may cause
	 * confusion for some browsers, including Mozilla. You can avoid this
	 * problem by using private_no_expire mode. The expire header is never
	 * sent to the client in this mode.
	 * 
	 * @param string $limiter
	 * 
	 * @return RPC_Session
	 */
	public function setCacheLimiter( $limiter )
	{
		session_cache_limiter( $limiter );
		
		return $this;
	}
	
	/**
	 * Gives a path to an external resource (file) which will be used as an
	 * additional entropy source in the session id creation process
	 * 
	 * @return RPC_Session
	 */
	public function setEntropyFile( $path )
	{
		ini_set( 'session.entropy_file', $path );
		
		return $this;
	}
	
	/**
	 * Specifies the number of bytes which will be read from the file specified by
	 * the entropy file
	 * 
	 * @return RPC_Session
	 */
	public function setEntropyLength( $length )
	{
		ini_set( 'session.entropy_length', $length );
		
		return $this;
	}
	
	/**
	 * Allows you to specify the hash algorithm used to generate the session IDs. '0' means MD5 (128 bits) and '1' means SHA-1 (160 bits)
	 * 
	 * @return RPC_Session
	 */
	public function setHashFunction( $function )
	{
		ini_set( 'session.hash_function', $function );
		
		return $this;
	}
	
	/**
	 * Session will not be available if cookies are not allowed
	 * 
	 * @return RPC_Session
	 */
	public function useOnlyCookies( $value )
	{
		ini_set( 'session.use_only_cookies', $value );
		
		return $this;
	}
	
	/**
	 * Sets a save adapter for the session. The object will provide a
	 * medium to keep the session data.
	 * 
	 * @param RPC_Session_Adapter $adapter
	 * 
	 * @return RPC_Session
	 */
	public function setAdapter( RPC\Session\Adapter $adapter )
	{
		session_set_save_handler( array( $adapter, 'open' ),
		                          array( $adapter, 'close' ),
		                          array( $adapter, 'read' ),
		                          array( $adapter, 'write' ),
		                          array( $adapter, 'destroy' ),
		                          array( $adapter, 'gc' ) );
		
		return $this;
	}
	
	/**
	 * Generates a new session id and removes the old session file
	 * 
	 * @retun RPC_Session
	 */
	public function regenerateId()
	{
		session_regenerate_id( true );
		
		return $this;
	}
	
	/**
	 * Initializes the session
	 * 
	 * @return RPC_Session
	 */
	public function start()
	{
		session_set_cookie_params( $this->_rpc_expire, $this->_rpc_path, $this->_rpc_domain, $this->_rpc_secure, $this->_rpc_httponly );
		
		session_start();

		//fixation attacks
		if( ! isset( $_SESSION['initiated'] ) )
		{
		    session_regenerate_id( true );
		    $_SESSION['initiated'] = true;
		}

		//session hijacking
		if( isset( $_SESSION['HTTP_USER_AGENT'] ) )
		{
		    if( $_SESSION['HTTP_USER_AGENT'] != md5( $_SERVER['HTTP_USER_AGENT'] . 'three29framework' ) )
		    {
		        /* Prompt for password */
		        $this->destroy();
		        return $this->start();
		    }
		}
		else
		{
		    $_SESSION['HTTP_USER_AGENT'] = md5( @$_SERVER['HTTP_USER_AGENT'] . 'three29framework' );
		}
		
		return $this;
	}
	
	/**
	 * Writes and closes the session
	 */
	public function write()
	{
		session_write_close();
	}
	
	/**
	 * Removes the session and regenerates a new session id
	 */
	public function destroy()
	{
		unset( $_SESSION );
		session_destroy();
	}
	
	/**
	 * When the session is initialized, the default parameters are set
	 */
	public function setDefaultCookieParams()
	{
		$params = session_get_cookie_params();
		
		$this->_rpc_expire   = $params['lifetime'];
		$this->_rpc_path     = $params['path'];
		$this->_rpc_domain   = $params['domain'];
		$this->_rpc_secure   = $params['secure'];
		$this->_rpc_httponly = $params['httponly'];
	}
	
}

?>
