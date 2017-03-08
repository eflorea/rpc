<?php

namespace RPC\Loop\Row;

use RPC\Loop\Manipulator;

/**
 * Class <code>RowLoopManipulator</code> implements a loop manipulator 
 * specialized for iterators where each element of the iteration is represented
 * by an array (<code>QueryIterator</code>, <code>DataFileIterator</code>).
 * <p>
 *   In many cases, each element in an iteration is an array. This is the case,
 *   for example, in query results and data files. Often, some column in that
 *   array is special, in that it stays the same for many items in the loop. As
 *   an example, consider the query <code>SELECT author.name AS author,
 *   book.title AS title FROM author, book WHERE book.author_id = author.id
 *   GROUP BY author</code>. This query selects all books by all authors, and
 *   groups them on author. If all rows are printed in HTML, it is likely that
 *   the name of each author should be shown only once.
 * </p>
 * <p>
 *   This class is a specialized loop manipulator that simplifies the handling
 *   of special columns by using so called <i>watchers</i>. For every column a
 *   watcher can be created, after which any number of methods can be registered
 *   on the watcher. When the watched column changes, all registered methods are
 *   called on the manipulator one by one.
 * </p>
 * <p>
 *   The watchers have a number of nice properties:
 * </p>
 * <ul>
 *   <li>
 *     For any column, any number of methods can be registered in a watcher. The
 *     methods are called in the order they are registered in.
 *   </li>
 *   <li>
 *     Watchers can be registered and/or unregistered at any time during the
 *     loop. It is possible, for example, to let some watcher trigger the
 *     creation of another watcher, which will then be active for the remainder
 *     of the iteration (unless it is unregistered again some time later).
 *   </li>
 *   <li>
 *     The code that is executed for a watcher is just a method of some
 *     manipulator. This makes it possible for new manipulators to be
 *     implemented as a subclass of that manipulator, overriding just the
 *     methods that need to express different behavior.
 *   </li>
 * </ul>
 * <p>
 *   The following code implements a manipulator for the example given earlier:
 * </p>
 * <pre>
 *   class BookAuthorsPrinter extends RowLoopManipulator
 *   {
 *       function BookAuthorsPrinter()
 *       {
 *           $this->RowLoopManipulator();
 *           $watcher =& $this->addWatcher('author');
 *           $watcher->register('author');
 *       }
 *
 *       function author(&$row, $index)
 *       {
 *           echo "&lt;b&gt;${row['author']}&lt;/b&gt;:&lt;br&gt;\n";
 *       }
 *
 *       function current(&$row, $index)
 *       {
 *           parent::current($row, $index);
 *           echo " - ${row['title']}&lt;br&gt;\n";
 *       }
 *   }
 *
 *   $result = $database->query(
 *       'SELECT author.name AS author, book.title AS title
 *        FROM author, book
 *        WHERE book.author_id = author.id
 *        GROUP BY author'
 *   );
 *   Loop::run(new QueryIterator($result), new BookAuthorsPrinter);
 * </pre>
 * <p>
 *   Subclasses that override the method <code>current(&$row, $index)</code>
 *   <i>must</i> call the parent method to make sure the watchers are processed.
 * </p>
 * @see RPC_Loop
 * @see RPC_Loop_Manipulator
 * @see RPC_Loop_RowManipulator_Watcher
 */
class Manipulator extends Manipulator
{

	/**
	 * The list of watchers; every column has at most one
	 * @var array
	 */
	protected $watchers = array();
	
	/**
	 * Construct a new <code>RPC_Loop_RowManipulator</code>
	 */
	public function __construct()
	{
	}
	
	/**
	 * Add a watcher for some column and return it
	 * 
	 * @param $column Name of the column to watch; either an index or a key
	 * 
	 * @return RPC_Row_LoopManipulator
	 */
	function addWatcher( $column )
	{
		if( $this->getWatcher( $column ) === false )
		{
			$this->watchers[$column] =	new RPC\Loop\Row\Manipulator\Watcher( $this, $column );
		}
		
		return $this->getWatcher( $column );
	}
	
	/**
	 * @return void
	 */
	function current( & $row, $index )
	{
		foreach( $this->watchers as $column => $watcher )
		{
			$watcher->check( $row, $index );
		}
	}
	
	/**
	 * Get the watcher for a specific column; if the watcher doesn't exist, this
	 * method returns <code>false</code>
	 * 
	 * @param $column the name of the column to get the watcher for
	 * @return RPC_Loop_RowManipulator_Watcher
	 */
	function getWatcher( $column )
	{
		if( isset( $this->watchers[$column] ) )
		{
			return $this->watchers[$column];
		}
		
		return false;
	}
	
}

?>
