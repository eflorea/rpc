<?php

class RPC_View_Filter_Translate implements RPC_View_Filter
{

	public function filter( $source )
	{
		$regex = new RPC_Regex( '/<t>(.*)?<\/t>/' );
		return $regex->replace( $source, '<?php ob_start(); ?>$1<?php RPC_Util::translate( trim( ob_get_clean() ) ); ?>' );
	}

}

?>
