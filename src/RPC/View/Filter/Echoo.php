<?php

namespace RPC\View\Filter;

use RPC\View\Filter;
use RPC\Regex;

/**
 * Transforms the shortcut syntax for echo to using echo.
 * 
 * Thus, <code><?= $var ?></code> will become <code><?php echo $view->escape( $var ); ?></code>.
 * 
 * @package View
 */
class Echoo extends Filter
{
	
	/**
	 * Filters the template transforming shortcut echo syntax to echo
	 * 
	 * @param string $source
	 * 
	 * @return string
	 */
	public function filter( $source )
	{
		$regex = new \RPC\Regex( '/<\?=(.+?)\?>/' );
		
		if( $regex->match( $source, $matches ) )
		{
			foreach( $matches as $match )
			{
				$source = str_replace( $match[0][0], '<?php echo $view->escape( ' . trim( trim( $match[1][0] ), ';' ) . ' ); ?>', $source );
			}
		}
		
		return $source;
	}
	
}

?>
