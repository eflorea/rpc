<?php

class RPC_View_Filter_Datagrid extends RPC_View_Filter_Chain
{
	
	public function __construct()
	{
		parent::addFilter( new RPC_View_Filter_Datagrid_Sort() );
		parent::addFilter( new RPC_View_Filter_Datagrid_Pagination() );
	}
	
	public function filter( $source )
	{
		$source = parent::filter( $source );

		$regex  = new RPC_Regex( '/(<.*?)datagrid="([^"]+)"(.*?(?<!\?)>)/' );
		$source = $regex->replace( $source, '$1$3<?php $_rpc_view_datagrid = $$2; ?>' );
				
		return $source;
	}
	
}

?>
