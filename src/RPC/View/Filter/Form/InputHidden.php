<?php



/**
 * Transforms inputs like:
 * <code><input type="hidden" name="u[username]" id="username" value="<?= $user->username ?>" /></code>
 * 
 * into
 * 
 * <code><?php $form->hidden( 'u[username]', $user->username, 'id="username"' ); ?></code>
 * (considering that the form's method is post)
 * 
 * @package View
 */
class RPC_View_Filter_Form_InputHidden extends RPC_View_Filter_Form_Element implements RPC_View_Filter
{
	
	public function filter( $source )
	{
		$regex = new RPC_Regex( '/<input.*?type="hidden".*?>/' );
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
			
			$new_input = $this->setAttribute( $input, 'value', '<?php echo $form->hidden( ' . $name . ', ' . $value . ' ) ?>' );
			
			$source = str_replace( $input, $new_input, $source );
		}
		
		return $source;
	}
	
}

?>
