<?php



/**
 * Transforms inputs like:
 * <code><input type="password" name="u[password]" id="password" value="<?= $user->password ?>" /></code>
 * 
 * into
 * 
 * <code><?php $form->text( 'u[password]', $user->pasword, 'id="password"' ); ?></code>
 * (considering that the form's method is post)
 * 
 * @package View
 */
class RPC_View_Filter_Form_InputPassword extends RPC_View_Filter_Form_Element implements RPC_View_Filter
{
	
	public function filter( $source )
	{
		$regex = new RPC_Regex( '/<input.*?type="password".*?>/' );
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
			
			$new_input = $this->setAttribute( $input, 'value', '<?php echo $form->text( ' . $name . ', ' . $value . ' ) ?>' );
			
			$source = str_replace( $input, $new_input, $source );
		}
		
		return $source;
	}
	
}

?>
