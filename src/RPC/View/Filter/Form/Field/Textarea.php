<?php

namespace RPC\View\Filter\Form\Field;

use RPC\View\Filter\Form\Field;

use RPC\Regex;

/**
 * Transforms textareas like:
 * <code><textarea name="foo">something</textarea></code>
 * 
 * into
 * 
 * <code><?php $form->textarea( 'foo', 'something', '' ); ?></code>
 * 
 * @package View
 */
class Textarea extends Field
{
	
	/**
	 * Adds persistence code for all textareas inside the given form
	 * 
	 * @param string $source
	 * 
	 * @return string
	 */
	public function filter( $source )
	{
		$regex = new \RPC\Regex( '/<textarea.*?(?<!\?)>.*?(?<!\?)><\/textarea>/ms' );
		$regex->match( $source, $matches );
		
		foreach( $matches as $textarea )
		{
			$textarea = $textarea[0][0];
			
			if( ! $this->hasAttribute( $textarea, 'name' ) )
			{
				continue;
			}
			
			$name  = $this->getAttribute( $textarea, 'name' );
			$value = $this->getAttribute( $textarea, 'value' );

			$new_textarea = $this->removeAttribute( $textarea, 'value' );
			$new_textarea = $this->setContent( $new_textarea, '<?php echo $form->textarea( ' . $name . ', ' . $value . ' ); ?>' );
			
			$source = str_replace( $textarea, $new_textarea, $source );
		}
		
		return $source;
	}
	
}

?>
