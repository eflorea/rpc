<?php

namespace RPC\HTTP;

/**
 * This class is supposted to make dealing with cookies easier.
 * 
 * The browser returns cookies to the server by adding fields to HTTP request
 * headers. Cookies can be retrieved from a request by using the
 * HttpRequest.getCookies() method.  Several cookies might have the same name
 * but different path attributes.
 * 
 * Cookies affect the caching of the Web pages that use them. HTTP 1.0 does not
 * cache pages that use cookies created with this class.  This class does not
 * support the cache control defined with HTTP 1.1.
 * 
 * @package HTTP
 */
class Cookie
{
	
	/**
	 * The cookie's name
	 * 
	 * @var string
	 */
	protected $name = '';
	
	/**
	 * The cookie's value
	 * 
	 * @var string
	 */
	protected $value = '';
	
	/**
	 * The cookie's expire time
	 * 
	 * @var integer
	 */
	protected $expire = 0;
	
	/**
	 * The cookie's path
	 * 
	 * @var string
	 */
	protected $path = '';
	
	/**
	 * The cookie's domain
	 * 
	 * @var string
	 */
	protected $domain = '';
	
	/**
	 * The cookie's secure setting
	 * 
	 * @var bool
	 */
	protected $secure = false;
	
	/**
	 * Whether the cookie is only for HTTP connections
	 * 
	 * @var bool
	 */
	protected $httponly = false;
	
	/**
	 * Class constructor
	 * 
	 * @param string $name   the name of the cookie
	 * @param string $value  the value of the cookie
	 * @param int    $expire the expire time of the cookie
	 * @param string $path   the cookie's path
	 * @param string $domain the cookie's domain
	 * @param bool   $secure is the cookie secure or not
	 */
	public function __construct( $name, $value = '', $expire = 0, $path = '', $domain = '', $secure = false, $httponly = false )
	{
		if( ! empty( $_COOKIE[$name] ) )
		{
			$parts = self::decode( $name );
			
			$value    = $parts['value'];
			$path     = $parts['path'];
			$domain   = $parts['domain'];
			$secure   = $parts['secure'];
			$httponly = $parts['httponly'];
		}
		
	    $this->name     = $name;
	    $this->value    = $value;
	    $this->expire   = $expire;
	    $this->path     = $path;
	    $this->domain   = $domain;
	    $this->secure   = $secure;
		$this->httponly = $httponly;
	}
	
	/**
	 * Returns the cookie's name or an empty string if not set.
	 * 
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}
	
	/**
	 * Returns the cookie's value or and empty string if not set.
	 * 
	 * @return string
	 */
	public function getValue()
	{
		return $this->value;
	}
	
	/**
	 * Returns the cookie's expire time or 0 if not set.
	 * 
	 * @return int
	 */
	public function getExpire()
	{
		return $this->expire;
	}
	
	/**
	 * Returns the cookie's path or an empty string if not set
	 * 
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}
	
	/**
	 * Returns the cookie's domain or an empty string if not set
	 * 
	 * @return string
	 */
	public function getDomain()
	{
		return $this->domain;
	}
	
	/**
	 * Returns whether the cookie is secure or not
	 * 
	 * @return bool
	 */
	public function isSecure()
	{
		return $this->secure;
	}
	
	/**
	 * Returns whether the cookie is sent only over the HTTP protocol
	 * 
	 * @return bool
	 */
	public function isHTTPOnly()
	{
		return $this->httponly;
	}
	
	/**
	 * Sets the cookie's name
	 * 
	 * @param string $name
	 * 
	 * @return RPC_HTTP_Cookie
	 */
	public function setName( $name )
	{
		$this->name = $name;
		
		return $this;
	}
	
	/**
	 * Sets the cookie's value
	 * 
	 * @param string $value
	 * 
	 * @return RPC_HTTP_Cookie
	 */
	public function setValue( $value )
	{
		$this->value = $value;
		
		return $this;
	}
	
	/**
	 * Sets the cookie's expire time
	 * 
	 * @param int $expire
	 * 
	 * @return RPC_HTTP_Cookie
	 */
	public function setExpire( $expire )
	{
		$this->expire = $expire;
		
		return $this;
	}
	
	/**
	 * Sets the cookie's path
	 * 
	 * @param string $path
	 * 
	 * @return RPC_HTTP_Cookie
	 */
	public function setPath( $path )
	{
		$this->path = $path;
		
		return $this;
	}
	
	/**
	 * Sets the cookie's domain
	 * 
	 * @param string $domain
	 * 
	 * @return RPC_HTTP_Cookie
	 */
	public function setDomain( $domain )
	{
		$this->domain = $domain;
		
		return $this;
	}
	
	/**
	 * Sets whether the cookie is secure or not
	 * 
	 * @param bool $secure
	 * 
	 * @return RPC_HTTP_Cookie
	 */
	public function setSecure( $secure )
	{
		$this->secure = (bool)$secure;
		
		return $this;
	}
	
	/**
	 * Sets whether the cookie is to be sent only over HTTP connections
	 * 
	 * @param bool $httponly
	 * 
	 * @return RPC_HTTP_Cookie
	 */
	public function setSecure( $httponly )
	{
		$this->httponly = (bool)$httponly;
		
		return $this;
	}
	
	/**
	 * Stores extra information in the value so that the cookie can be
	 * removed easily
	 * 
	 * @param string $value
	 * @param string $path
	 * @param string $domain
	 * @param bool   $secure
	 * @param bool   $httponly
	 * 
	 * @return string
	 */
	protected function encode()
	{
		return $this->getValue() . '#'  . (int)$this->getExpire() . ':' . $this->getPath() . ':' . $this->getDomain() . ':' . (int)$this->isSecure() . ':' . (int)$this->isHTTPOnly();
	}
	
	/**
	 * Separates the value of the cookie and return an array with all
	 * of the parts
	 * 
	 * @param string $name
	 * 
	 * @return array
	 */
	protected function decode()
	{
		$value = $_COOKIE[$name];
		$pos   = strrpos( $value, '#' );
		
		if( $pos === false )
		{
			return array();
		}
		
		list( $expire, $path, $domain, $secure, $httponly ) = explode( ':', substr( $value, $pos + 1 ) );
		$value = substr( $value, 0, $pos );
		
		return array( 'value' => $value, 'expire' => (int)$expire, 'path' => $path, 'domain' => $domain, 'secure' => (bool)$secure, 'httponly' => (bool)$httponly );

	}
	
}

?>
