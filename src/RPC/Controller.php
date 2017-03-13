<?php

namespace RPC;

use RPC\Registry;
use RPC\View;
use RPC\View\Cache;

class Controller
{
	protected $template;

	protected $vars = array();

	public function display( $template = null )
	{	
		$this->template = $template;
		$this->getView()->setController( $this );
		$this->getView()->display( $template );
	}


	public function getView()
	{
		if( ! \RPC\Registry::registered( 'view' ) )
		{
			$view = new \RPC\View( PATH_APP  . '/View', new \RPC\View\Cache( PATH_CACHE . '/view' ) );

			\RPC\Registry::set( 'view', $view );
		}
		
		return \RPC\Registry::get( 'view' );
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
			throw new Exception( 'You are trying to assign a value on an attribute which is reserved to a template name' );
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

}

?>
