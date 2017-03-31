<?php

namespace RPC\View\Filter\Form\Field;

use RPC\View\Filter\Form\Field;

use RPC\Regex;


/**
 * Transforms inputs like:
 * <code><input type="checkbox" name="u[areaofinterest][<?= $id ?>]" id="areaofinterest<?= $id ?>" value="<?= $area ?>" default="$user->areasofinterest[$id]" /></code>
 * 
 * into
 * 
 * <code><input type="text" name="u[username]" id="username" value="<?php if( isset( $this->getRequest()->post['u']['username'] ) ): echo $this->getRequest()->post['u']['username']; else: ?><?= $user->username ?><?php endif; ?>" /></code>
 * 
 * @package View
 */
class Checkbox extends Field
{
	
	/**
	 * Adds persistence code to all checkbox inputs inside the given form
	 * 
	 * @param string $source
	 * 
	 * @return string
	 */
	public function filter( $source )
	{
		$regex = new \RPC\Regex( '/<input.*?type="checkbox".*?(?<!\?)>>/' );
		$regex->match( $source, $inputs );
		
		foreach( $inputs as $input )
		{
			$input = $input[0][0];
			
			$name    = $this->getAttribute( $input, 'name' );
			$value   = $this->getAttribute( $input, 'value' );
			$checked = $this->getAttribute( $input, 'checked' );
			
			$persist = $this->getAttribute( $input, 'persist' );

			if( $persist == "'no'" )
			{
				$new_input = $this->removeAttribute( $input, 'persist' );
				$source    = str_replace( $input, $new_input, $source );
				continue;
			}
			
			$new_input = $this->setAttribute( $input, 'checked', '<?php echo $form->checkbox( ' . $name . ', ' . $value . ', ' . $checked . ' ); ?>' );
			
			$source = str_replace( $input, $new_input, $source );
		}
		
		return $source;
	}
	
}

?>
