<?php



/**
 * Makes selects persistent
 * 
 * @package View
 */
class RPC_View_Filter_Form_Select extends RPC_View_Filter_Form_Element implements RPC_View_Filter
{
	
	/**
	 * Adds persistence code for all selects inside the given form
	 * 
	 * @param string $source
	 * 
	 * @return string
	 */
	public function filter( $source )
	{
		$regex = new RPC_Regex( '/<select.*?><\/select>/' );
		$regex->match( $source, $matches );

		foreach( $matches as $select )
		{
			$select = $select[0][0];
			
			if( ! $this->hasAttribute( $select, 'name' ) )
			{
				continue;
			}
			
			$name     = $this->getAttribute( $select, 'name' );
			$src      = $this->getAttribute( $select, 'source' );
			$selected = $this->getAttribute( $select, 'selected' );

			$new_select = $this->removeAttribute( $select, 'source' );
			$new_select = $this->removeAttribute( $new_select, 'selected' );
			
			$new_select = $this->setContent( $new_select, '<?php echo $form->select( ' . $name . ', ' . $src . ', ' . $selected . ' ) ?>' );
			
			$source = str_replace( $select, $new_select, $source );
		}
		
		return $source;
	}
	
}

?>
