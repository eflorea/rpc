<?php

class RPC_View_Filter_Comment implements RPC_View_Filter
{

	public function filter( $source )
	{
		$regex = new RPC_Regex( '/<!-- *[^\[].*?-->/' );
		return $regex->replace( $source, '' );
	}

}

?>
