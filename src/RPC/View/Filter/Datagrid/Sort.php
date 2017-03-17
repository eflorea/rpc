<?php

namespace RPC\View\Filter\Datagrid;

use RPC\View\Filter;
use RPC\Regex;

class Sort implements Filter
{
	
	public function filter( $source )
	{
		$regex  = new \RPC\Regex( '/<sort +field="([^"]+)">(.+?)<\/sort>/' );
		$source = $regex->replace( $source, '<?php echo $_rpc_view_datagrid->printSortBy( \'$1\', \'$2\' ); ?>' );
		
		return $source;
	}
	
}

?>
