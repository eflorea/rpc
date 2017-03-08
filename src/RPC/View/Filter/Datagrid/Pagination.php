<?php

namespace RPC\View\Filter\Datagrid;

use RPC\Regex;
use RPC\View\Filter;

class Pagination implements Filter
{
	
	public function filter( $source )
	{
		$regex  = new RPC\Regex( '/<pagination>/' );
		$source = $regex->replace( $source, '<?php echo $_rpc_view_datagrid->getPager()->render(); ?>' );
		
		return $source;
	}
	
}

?>
