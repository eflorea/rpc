<?php

namespace RPC;

use RPC\Registry;
use RPC\View;
use RPC\View\Cache;

class Controller
{
	protected $template;
	public $current_method;
	public $current_controller;

	protected $vars = array();

	public $request;
	public $response;

	public function display( $template = null )
	{
		$this->template = $template;
		$this->getView( true )->display( $template );
	}


	public function getView( $refresh_vars = false )
	{
		if( ! Registry::registered( 'view' ) )
		{
			$view = new \RPC\View( APP_PATH  . '/View', new Cache( CACHE_PATH . '/view' ) );
			$view->setController( $this );

			$this->vars['current_method'] 		= $this->current_method;
			$this->vars['current_controller'] 	= $this->current_controller;
			$this->vars['csrf_token']			= \RPC\Util::csrf();

			$view->setVars( $this->vars );


			Registry::set( 'view', $view );
		}

		if( $refresh_vars )
		{
			$view = Registry::get( 'view' );
			$view->setVars( $this->vars );

			Registry::set( 'view', $view );
		}

		return Registry::get( 'view' );
	}


	public function setErrors( $errors = array() )
	{
		$this->getView()->setErrors( $errors );
	}


	public function param( $name = null, $default = null )
	{
		return $this->request->getParam( $name, $default );
	}

	public function redirect( $url )
	{
		return $this->response->redirect( $url );
	}


	public function json( $data = array() )
	{
		return $this->response->json( $data );
	}

	public function jsonSuccess( $data = array() )
	{
		return $this->response->jsonSuccess( $data );
	}

	public function jsonError( $error_message = '', $data = array() )
	{
		return $this->response->jsonError( $error_message, $data );
	}

/**
	 * Assigns a variable which will be available in the templates
	 *
	 * @param string $var
	 * @param mixed  $value
	 */
	public function __set( $var, $value )
	{
		if( strpos( $var, 'template' ) === 0 )
		{
			throw new \Exception( 'You are trying to assign a value on an attribute which is reserved to a template name' );
		}

		$this->vars[$var] = $value;
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
		return isset( $this->vars[$var] ) ? $this->vars[$var] : null;
	}

	public function flash()
    {
    	@list( $message, $message_type, $persistent ) = func_get_args();

    	if( $message )
    	{
    		$_SESSION['_FLASH_'][] = array( 'message' => $message, 'message_type' => $message_type, 'persistent' => ( $persistent ? 1 : 0 ) );
    	}
    	else
    	{
    		$messages = array();

	        if( isset( $_SESSION['_FLASH_'] ) )
	        {
	            foreach( $_SESSION['_FLASH_'] as $index => $message )
	            {
	                if ( ! $message['persistent'] )
	                {
	                    unset( $_SESSION['_FLASH_'][$index] );
	                }
	                $messages[] = $message;
	            }
	        }

	        return $messages;
    	}
    }


}

?>
