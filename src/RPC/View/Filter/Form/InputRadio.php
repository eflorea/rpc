<?php



/**
 * Transforms inputs like:
 * <code><input type="checkbox" name="u[areaofinterest][<?= $id ?>]" id="areaofinterest<?= $id ?>" value="<?= $area ?>" default="$user->areasofinterest[$id]" /></code>
 * 
 * into
 * 
 * <code><input type="text" name="u[username]" id="username" value="<?php if( isset( $this->getRequest()->post['u']['username'] ) ): echo $this->getRequest()->post['u']['username']; else: ?><?= $user->username ?><?php endif; ?>" /></code>
 * (considering that the form's method is post)
 * 
 * @package View
 */
class RPC_View_Filter_Form_InputRadio extends RPC_View_Filter_Form_Element implements RPC_View_Filter
{
	
	/**
	 * Adds persistence code to all radio inputs inside the given form
	 * 
	 * @param string $source
	 * 
	 * @return string
	 */
	public function filter( $source )
	{
		$regex = new RPC_Regex( '/<input.*?type="radio".*?>/' );
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
			
			$new_input = $this->setAttribute( $input, 'checked', '<?php echo $form->radio( ' . $name . ', ' . $value . ', ' . $checked . ' ) ?>' );
			
			$source = str_replace( $input, $new_input, $source );
		}
		
		return $source;
	}
	
}

?>
