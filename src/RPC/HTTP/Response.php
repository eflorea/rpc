<?php

namespace RPC\HTTP;

use Exception;

/**
 * Represents the response sent back to the browser
 * 
 * @package HTTP
 */
class Response
{
	
	/**
	 * Header for a GIF image
	 */
	const HEADER_GIF = 'Content-type: image/gif';
	
	/**
	 * Header for a PNG image
	 */
	const HEADER_PNG = 'Content-type: image/png';
	
	/**
	 * Header for a JPG image
	 */
	const HEADER_JPEG = 'Content-type: image/jpeg';
	
	/**
	 * Sent when the requested resource cannot be found
	 */
	const HEADER_NOT_FOUND = 'HTTP/1.0 404 Not Found';
	
	/**
	 * Singleton
	 * 
	 * @see self::getInstance()
	 */
	protected function __construct() {}
	
	/**
	 * Singletons can't be cloned
	 */
	protected function __clone() {}
	
	/**
	 * Returns an instance of the class. Subsequent calls to this method will
	 * return the same object
	 * 
	 * @return RPC_HTTP_Response
	 */
	public static function getInstance()
	{
		if( ! isset( $GLOBALS['_RPC_']['singleton']['response'] ) )
		{
			$c = __CLASS__;
			$GLOBALS['_RPC_']['singleton']['response'] = new $c;
		}
		
		return $GLOBALS['_RPC_']['singleton']['response'];
	}
	
	/**
	 * Redirects to a given URI and stops the script execution
	 * 
	 * @param string $url
	 */
	public function redirect( $url = '/', $permanent = false )
	{
		if( ! headers_sent( $file, $line ) )
		{
			if( $permanent )
			{
				header( 'HTTP/1.0 301 Moved Permanently' );
			}
			
			$url = str_replace( array( "\n", "\r" ), '', $url );
			
			header( 'Location: ' . $url );
		}
		else
		{
			die( 'Headers sent in file: ' . $file . ' on line: ' . $line );
		}
		
		exit;
	}
	
	/**
	 * Adds the specified cookie to the response.
	 * 
	 * This method can be called multiple times to set more than one cookie or
	 * to modify an already set one. Returns true if the adding was successful,
	 * false otherwise.
	 * 
	 * @param RPC_HTTP_Cookie $cookie The cookie object to add
	 * 
	 * @return boolean True if the adding was successful, false otherwise
	 * 
	 * @todo Maybe I should not set each cookie directly, but keep them in an
	 * array, keyed after their names - every unset should be easier then, and
	 * the flush methods should make sense
	 */
	public function setCookie( RPC\HTTP\Cookie $cookie )
	{
		if( headers_sent( $file, $line ) )
		{
			throw new Exception( 'Cookie cannot be set, headers have already been sent (file ' . $file . ', line ' . $line . ')' );
		}
		
		if( ! $cookie->getName() )
		{
			throw new Exception( 'Each cookie should have a name' );
		}
		
		return setcookie( $cookie->getName(),
		                  $cookie->getValue(),
		                  $cookie->getExpire(),
		                  $cookie->getPath(),
		                  $cookie->getDomain(),
						  $cookie->isSecure(),
						  $cookie->isHTTPOnly() );
	}
	
	/**
	 * Deletes the specified cookie from the response.
	 * 
	 * @param RPC_HTTP_Cookie $cookie the cookie object to delete
	 * 
	 * @return bool If the cookie has been sent (doesn't mean the client
	 *              accepted it)
	 */
	public function unsetCookie( RPC\HTTP\Cookie $cookie )
	{
		if( headers_sent() )
		{
			throw new Exception( 'Cookie cannot be unset, headers have already been sent (file ' . $file . ', line ' . $line . ')' );
		}
		
		// set the expiration date to one hour ago
		$cookie->setExpire( time() - 3600 );
		
		return setcookie( $cookie->getName(),
		                  $cookie->getValue(),
		                  $cookie->getExpire(),
		                  $cookie->getPath(),
		                  $cookie->getDomain(),
		                  $cookie->isSecure() );
	}
	
	/**
	 * Adds a response header with the given name and value.
	 * 
	 * This method allows response headers to have multiple values. Returns true
	 * if the header could be added, false otherwise. False will be returned
	 * f.g. when the headers have already been sent.  The replace parameter
	 * indicates if an already existing header with the same name should be
	 * replaced or not.
	 * 
	 * @param string  $name    the name of the header
	 * @param string  $value   the value of the header
	 * @param boolean $replace should the header be replaced or not
	 * 
	 * @return boolean true if the header could be set, false otherwise
	 */
	public function addHeader( $name, $value, $replace = false )
	{
		if( headers_sent() )
		{
			/**
			 * @todo Maybe throw an exception?
			 */
			return false;
		}
		
		header( $name . ': ' . $value, (bool) $replace );
		
		return true;
	}
	
	/**
	 * Sets the status code for this request.
	 * 
	 * Sets the status code for this response. This method is used to set the
	 * return status code when there is no error (for example, for the status
	 * codes SC_OK or SC_MOVED_TEMPORARILY). If there is an error, and the
	 * caller wishes to provide a message for the response, the sendError()
	 * method should be used instead.
	 * 
	 * @param string $code
	 */
	public function setStatus( $code )
	{
	    header( 'HTTP/1.0 ' . $code );
	}
	
	/**
	 * Prevents the browser from caching the response
	 */
	public function noCache()
	{
		header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT', true );
		header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT', true );
		header( 'Cache-Control: no-store, no-cache, must-revalidate', true );
		header( 'Cache-Control: post-check=0, pre-check=0', true );
		header( 'Pragma: no-cache', true );
	}
	
	/**
	 * Fills the buffer for the response body with the specified content
	 * 
	 * @param string $content
	 * 
	 * @return RPC_HTTP_Response
	 */
	public function setBuffer( $content )
	{
		$this->buffer = $content;
		
		return $this;
	}
	
	/**
	 * Adds the given text at the begining of the response
	 * 
	 * @param string $content
	 * 
	 * @return RPC_HTTP_Response
	 */
	public function prepend( $content )
	{
		$this->buffer = $content . $this->buffer;
		
		return $this;
	}
	
	/**
	 * Adds the given text at the end of the response
	 * 
	 * @param string $content
	 * 
	 * @return RPC_HTTP_Response
	 */
	public function append( $content )
	{
		$this->buffer .= $content;
		
		return $this;
	}
	
	/**
	 * Return the length of the current output buffer
	 * 
	 * @return int
	 */
	public function getContentLength()
	{
		return strlen( $this->buffer );
	}
	
	/**
	 * Returns the contents of the response
	 * 
	 * @return string
	 */
	public function __toString()
	{
		return $this->buffer;
	}
	
	/**
	 * Return json output
	 */
	public function json( $output = array() )
	{
		header( 'Content-Type: application/json' );
		echo json_encode( $output );
		exit;
	}

	/**
	 * Shortcuts for json outputs
	 */
	public function jsonSuccess( $data = array() )
	{
		return $this->json( array( 'success' => 1, 'data' => $data ) );
	}

	public function jsonError( $error_message = '', $data = array() )
	{
		return $this->json( array( 'error' => 1, 'error_message' => $error_message, 'data' => $data ) );
	}

}

?>
