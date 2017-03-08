<?php

namespace RPC\Controller;

/**
 * Interprets the request and fetches the parameters, the command object and
 * specific action
 * 
 * @package Controller
 */
interface Router
{
	
	/**
	 * Returns the params array from the request
	 * 
	 * @return array
	 */
	public function getParams();
	
	/**
	 * Execute the routing process
	 */
	public function route();
	
}

?>
