<?php

namespace RPC\Loop;

/**
 * Class <code>RPC_Loop_Manipulator</code> defines the interface for
 * manipulators to be used with class <code>RPC_Loop</code>.
 * 
 * Every <code>RPC_Loop_Manipulator</code> must implement these four methods:
 * <ul>
 *   <li>
 *     <code>prepare()</code>: execute code right before the first object is
 *     processed.
 *   </li>
 *   <li>
 *     <code>current( $object, $index )</code>: execute code for the current
 *     object.
 *   </li>
 *   <li>
 *     <code>between( $index )</code>: execute code between every two objects.
 *     This method is not called before the first object, nor after the last.
 *   </li>
 *   <li>
 *     <code>finish( $total )</code>: execute code right after the last object is
 *     processed.
 *   </li>
 * </ul>
 * If some loop manipulator only needs to provide a few methods, implementing it
 * as a subclass of this interface allows the unused methods to be left
 * unspecified.
 * 
 * Using class <code>RPC_Loop</code> and some loop manipulator not only makes it
 * very easy to implement a specialized iteration algorithm, it also allows
 * the behavior of the algorithm to be implemented in stages, or existing 
 * behavior to be reused. Given a <code>RPC_Loop_Manipulator</code>, it can be
 * used directly, subclassed or wrapped inside another manipulator. The
 * following simple manipulator, for example, makes it possible to print any HTML
 * output inside a specified number of columns:
 * 
 * <code>
 *   class HtmlTable extends RPC_Loop_Manipulator
 *   {
 *       protected $manipulator;
 *       protected $break;
 *
 *       public function __construct( $manipulator, $total, $columns )
 *       {
 *           $this->manipulator = $manipulator;
 *           $this->break       = ceil( $total / $columns );
 *       }
 *
 *       public function prepare()
 *       {
 *           echo "&lt;table&gt;\n  &lt;tr&gt;\n";
 *           $this->manipulator->prepare();
 *       }
 *
 *       public function current( $object, $index )
 *       {
 *           $this->manipulator->current( $object, $index );
 *       }
 *
 *       public function between( $index )
 *       {
 *           $this->manipulator->between( $index );
 *           if( $index % $this->break == 0 )
 *           {
 *               echo "  &lt;/tr&gt;\n  &lt;tr&gt;\n";
 *           }
 *       }
 *
 *       public function finish( $total )
 *       {
 *          $this->manipulator->finish($total);
 *          echo "  &lt;/tr&gt;\n&lt;/table&gt;\n";
 *       }
 *   }
 *
 *   class BookPrinter extends RPC_Loop_Manipulator
 *   {
 *       public function current( $row, $index )
 *       {
 *           echo "${row['title']}&lt;br&gt;\n";
 *       }
 *   }
 *
 *   $result = $database->query('SELECT title FROM book');
 *   Loop::run(
 *       new QueryIterator($result),
 *       new HtmlTable(new BookPrinter, $result->getRowCount(), 2);
 *   );
 * </code>
 * The (extremely simplified and untested) class <code>HtmlTable</code> above
 * can be reused over and over again whenever some output must be printed in 
 * columns. Also, class <code>BookPrinter</code> can be used in other problem
 * areas as well, as it knows nothing about the HTML table it happens to be
 * printed in (in this occassion).
 * 
 * @see RPC_Loop
 */
class Manipulator
{
	
	public function prepare()
	{
	}
	
	public function current( & $var, $index )
	{
	}
	
	public function between( $index )
	{
	}
	
	public function finish( $total )
	{
	}
	
}

?>
