	/**
	 * Will replace <resource module="hr" action="edit" id="70">HTML</resource> to:
	 * <code><?php if( $this->user->isAllowed( "hr", "edit", "70" ) ): ?>HTML<?php endif; ?></code>
	 *
	 * @param string $source
	 * 
	 * @return string
	 */
