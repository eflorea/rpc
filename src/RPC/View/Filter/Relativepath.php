<?php

class RPC_View_Filter_Relativepath implements RPC_View_Filter
{
	
	protected $base_uri = '';
	
	public function filter( $source )
	{
		if( ! $this->getBaseURI() )
		{
			return $source;
		}
		
		$regex = new RPC_Regex( '#(href|src)="(/[^"]+)"#i' );
		return $regex->replace( $source, '$1="' . $this->getBaseURI() . '$2"' );
	}
	
	public function setBaseURI( $base_uri )
	{
		$this->base_uri = rtrim( $base_uri, '/' );
	}
	
	public function getBaseURI()
	{
		return $this->base_uri;
	}
	
}

?>
