<?php

namespace RPC\View\Filter\Form\Field;

use RPC\View\Filter\Form\Field;

use RPC\Regex;

/**
 * Transforms inputs like:
 * <code><input type="password" name="u[password]" id="password" value="<?= $user->password ?>" /></code>
 * 
 * into
 * 
 * <code><?php $form->text( 'u[password]', $user->pasword, 'id="password"' ); ?></code>
 * 
 * @package View
 */
class Pass extends Field
{
	
	public function filter( $source )
	{
		$regex = new \RPC\Regex( '/<input.*?type="password".*?(?<!\?)>/' );
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
