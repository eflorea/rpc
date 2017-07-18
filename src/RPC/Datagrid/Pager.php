<?php

namespace RPC\Datagrid;

use RPC\HTTP\Request;

/**
 * Generates pagination HTML
 *
 * @package Datagrid
 */
class Pager
{

	/**
	 * Total number of items to be paginated
	 *
	 * @var int
	 */
	protected $total = 0;

	/**
	 * Current page
	 *
	 * @var int
	 */
	protected $current = 0;

	/**
	 * Number of items displayed per page
	 *
	 * @var int
	 */
	protected $perpage = 50;

	/**
	 * Number of pages to be shown to the left and right of the current page
	 *
	 * @var int
	 */
	protected $delta = 5;

	/**
	 * Class constructor
	 */
	public function __construct()
	{
		$request = \RPC\HTTP\Request::getInstance();

		$page = @$request->get['page'];
		$this->setCurrent( (int)$page );
	}

	/**
	 * Sets the total number of items to be paginated
	 *
	 * @param int $number
	 *
	 * @return RPC_Datagrid_Pager
	 */
	public function setTotal( $number )
	{
		$this->total = $number;

		return $this;
	}

	/**
	 * Sets the current page
	 *
	 * @param int $page
	 *
	 * @return RPC_Datagrid_Pager
	 */
	public function setCurrent( $page )
	{
		$this->current = $page;
		return $this;
	}

	/**
	 * Sets the number of items to be displayed per page
	 *
	 * @param int $perpage
	 *
	 * @return RPC_Datagrid_Pager
	 */
	public function setPerPage( $perpage )
	{
		if( $perpage < 1 )
		{
			$perpage = $this->perpage;
		}

		$this->perpage = $perpage;

		return $this;
	}

	/**
	 * Returns the number of items that should be displayed on each page
	 *
	 * @return int
	 */
	public function getPerPage()
	{
		return $this->perpage;
	}

	/**
	 * Number of pages to be shown besides the current one
	 *
	 * @param int $delta
	 *
	 * @return RPC_Datagrid_Pager
	 */
	public function setDelta( $delta )
	{
		$this->delta = 3;

		return $this;
	}

	/**
	 * Return an array containing the lower and upper boundaries of
	 * items to be retrieved for the current page
	 *
	 * @return array
	 */
	public function getLimits()
	{
		$lower = $this->perpage * $this->current;
		$upper = $lower + $this->perpage;

		return array( $lower, $upper );
	}

	/**
	 * Returns the HTML used for pagination
	 *
	 * @return string
	 */
	public function render()
	{
		if( ! $this->getTotalPages() )
		{
			return '';
		}

		$html  = '<ul class="pagination">';

		$request = \RPC\HTTP\Request::getInstance();
		$uri     = $request->getPathInfo();
		$query   = $request->get;

		$from = $this->current - $this->delta;
		$to   = $this->current + $this->delta;

		if( $this->current > 0 )
		{
			$query['page'] = 0;
			$html .= '<li class="start"><a href="' . $uri . '?' . http_build_query( $query ) . '">&laquo;</a></li>';
			$query['page'] = $this->current - 1;
			$html .= '<li class="prev"><a href="' . $uri . '?' . http_build_query( $query ) . '">&lt;</a></li>';
		}

		for( $first = 1, $i = $from; ( $i < $to + 1 ) && ( $i < $this->getTotalPages() ); $i++ )
		{
			if( $i < 0 )
			{
				continue;
			}

			$class = '';

			if( $i != $this->current )
			{
				if( $first )
				{
					$class = ' class="first" ';
					$first = 0;
				}
				elseif( ! ( $i + 1 < $to ) &&
					      ( $i + 1 < $this->getTotalPages() ) )
				{
					$class = ' class="last" ';
				}

				$query['page'] = $i;

				$html .= '<li' . $class . '><a href="' . $uri . '?' . http_build_query( $query ) . '">' . ( $i + 1 ) . '</a></li>';
			}
			else
			{
				$html .= '<li class="current active"><a class="disabled">' . ( $i + 1 ) . '</a></li>';
			}
		}

		if( $this->current < ( $this->getTotalPages() - 1 ) )
		{
			$query['page'] = $this->current + 1;
			$html .= '<li class="next"><a href="' . $uri . '?' . http_build_query( $query ) . '">&gt;</a></li>';
			$query['page'] = $this->getTotalPages() - 1;
			$html .= '<li class="end"><a href="' . $uri . '?' . http_build_query( $query ) . '">&raquo;</a></li>';
		}

		$html .= '</ul>';

		return $html;
	}

	/**
	 * Returns the total number of pages available
	 *
	 * @return int
	 */
	public function getTotalPages()
	{
		return ceil( $this->total / $this->perpage );
	}

	/**
	 * Returns the number of rows found
	 *
	 * @return int
	 */
	public function getTotalRows()
	{
		return $this->total;
	}

	/**
	 * Return current page
	 */
	public function getCurrentPage()
    {
        return $this->current;
    }

}

?>
