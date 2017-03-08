<?php

class RPC_View_Filter_Whitespace implements RPC_View_Filter
{

	public function filter( $source )
	{
		return trim( $source );

		$source = str_replace( "\r\n", "\n", $source );

		$regex = new RPC_Regex( '/(?<!<(pre|code|xmp)>[^<]*)(?:^\s*$)+(?!<\/(pre|code|xmp)>)/m' );
		$source = $regex->replace( $source, "\n" );
	}

}

?>
