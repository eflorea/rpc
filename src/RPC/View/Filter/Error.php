<?php

namespace RPC\View\Filter;

use RPC\View\Filter;
use RPC\Regex;

/**
 * Transforms code like <code><error src="username"><p class="error">Invalid username</p></error></code>
 * into <code><?php if( ! empty( $this->errors->get( "username" ) ): ?><p>class="error"><?php echo $this->errors->get( "username" ); ?></p><?php endif; ?></code>
 * 
 * @package View
 */
class Error extends Filter
{
	
	/**
	 * Array of errors set throughout the script
	 * 
	 * @var array
	 */
	protected $_view_errors = array();
	
	protected $error_format = '<div class="has-error" id="error-{id}"><span class="help-block">{message}</span></div>';
	
	/**
	 * Replaces all error tag occurences with the necessary PHP code
	 * 
	 * @param string $source
	 * 
	 * @return string
	 */
	public function filter( $source )
	{
		$regex = new \RPC\Regex( '/<error id="([a-zA-Z0-9_\-]+)"><\/error>/' );
	
		if( $regex->match( $source, $matches ) )
		{

			foreach( $matches as $match )
			{
				$php  = '<?php if( $view->getError( \'' . $match[1][0] . '\' ) ): ?>';
				$php .= str_replace( array( '{id}', '{message}' ), array( $match[1][0], '<?php echo $view->getError( "' . $match[1][0] . '" ) ?>' ), $this->error_format );
				$php .= '<?php endif ?>';
				
				$source = str_replace( $match[0][0], $php, $source );
			}
		}
		
		return $source;
	}
	
	/**
	 * Sets an error for a specified field
	 * 
	 * @param string $error
	 * @param string $value
	 */
	public function set( $error, $value = null )
	{
		if( is_array( $error ) )
		{
			foreach( $error as $k => $v )
			{
				$this->_view_errors[$k] = $v;
			}
		}
		else
		{
			$this->_view_errors[$error] = $value;
		}
	}
	
	/**
	 * Retrives the specified error from the object
	 * 
	 * @param string $error
	 * 
	 * @return string
	 */
	public function get( $error )
	{
		return $this->_view_errors[$error];
	}
	
	/**
	 * Shortcut for self::set
	 *
	 * @param string $error
	 * @param mixed  $value
	 */
	public function __set( $error, $value = null )
	{
		$this->set( $error, $value );
	}
	
	/**
	 * Shortcut for self::get
	 *
	 * @return mixed
	 */
	public function __get( $error )
	{
		return $this->get( $error );
	}
	
	/**
	 * Allows for checking if an error exists using isset
	 * 
	 * @param string $error
	 * 
	 * @return bool
	 */
	public function __isset( $error )
	{
		return array_key_exists( $error, $this->_view_errors );
	}
	
	/**
	 * Checks to see if there are any errors set
	 * 
	 * @return bool
	 */
	public function exist()
	{
		return count( $this->_view_errors ) ? true : false;
	}
	
}

?>
