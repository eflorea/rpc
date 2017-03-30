<?php

namespace RPC\View\Filter\Form\Field;

use RPC\View\Filter\Form\Field;

use RPC\Regex;

/**
 * Transforms inputs like:
 * <code><input type="text" name="u[username]" id="username" value="<?= $user->username ?>" /></code>
 * 
 * into
 * 
 * <code><?php $form->text( 'u[username]', $user->username, 'id="username"' ); ?></code>
 * 
 * @package View
 */
class Text extends Field
{
	
	public function filter( $source )
	{		
		$regex = new \RPC\Regex( '/<input.*?type="text".*?(?<!\?)>/' );
		$regex->match( $source, $inputs );
		
		foreach( $inputs as $input )
		{
			$input = $input[0][0];
		
			if( ! $this->hasAttribute( $input, 'name' ) )
			{
				continue;
			}
			
			$name  = $this->getAttribute( $input, 'name' );
			$value = $this->getAttribute( $input, 'value' );

			$persist = $this->getAttribute( $input, 'persist' );
			
			if( $persist == "'no'" )
			{
				$new_input = $this->removeAttribute( $input, 'persist' );
				$source    = str_replace( $input, $new_input, $source );
				continue;
			}
			
			$new_input = $this->setAttribute( $input, 'value', '<?php echo $form->text( ' . $name . ', ' . $value . ' ); ?>' );
			
			$source = str_replace( $input, $new_input, $source );	
		}
		
		return $source;
	}
	
}

?>
