<?php

namespace RPC\View\Filter;

use RPC\View\Filter;

use RPC\HTTP\Request;

use RPC\Regex;
use RPC\View\Filter\Form\Field\Pass;
use RPC\View\Filter\Form\Field\Text;
use RPC\View\Filter\Form\Field\Hidden;
use RPC\View\Filter\Form\Field\Radio;
use RPC\View\Filter\Form\Field\Checkbox;
use RPC\View\Filter\Form\Field\Textarea;
use RPC\View\Filter\Form\Field\Select;


/**
 * Finds forms in the source and adds a line of code which will set the method
 * for the current form
 *
 * @package View
 */
class Form extends Filter
{

	protected $filters = array();
	protected $request;
	protected $method;

	/**
	 * Class Constructor which adds some filters which are part of the form
	 */
	public function __construct()
	{

		$this->request = \RPC\HTTP\Request::getInstance();
		$this->addFilter( new \RPC\View\Filter\Form\Field\Pass() );
		$this->addFilter( new \RPC\View\Filter\Form\Field\Text() );
		$this->addFilter( new \RPC\View\Filter\Form\Field\Hidden() );
		$this->addFilter( new \RPC\View\Filter\Form\Field\Radio() );
		$this->addFilter( new \RPC\View\Filter\Form\Field\Checkbox() );
		$this->addFilter( new \RPC\View\Filter\Form\Field\Textarea() );
		$this->addFilter( new \RPC\View\Filter\Form\Field\Select() );


	}

	public function addFilter( $filter )
	{
		$this->filters[] = $filter;
	}

	/**
	 * Finds forms in the source and adds a line of code which will set the method
	 * for the current form
	 *
	 * <code><form method="post" action="/path/to/action" class="example"></code>
	 * will become
	 * <code><form method="post" action="/path/to/action" class="example"><?php $form->setMethod( 'post' ); ?></code>
	 *
	 * @param string $source
	 *
	 * @return string
	 */
	public function filter( $source )
	{
		if( $this->filters )
		{
			foreach( $this->filters as $filter )
			{
				$source = $filter->filter( $source );
			}
		}

		$regex  = new \RPC\Regex( '/<form.*?method="([^"]+)".*?(?<!\?)>/' );
		$csrf_token = \RPC\Registry::get('csrf_token');
		$source = $regex->replace( $source, '${0}<?php $form = new \RPC\View\Filter\Form; ?><?php $form->setMethod( \'${1}\' ); ?><input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">' );

		//$source = parent::filter( $source );

		return $source;
	}




	public function setMethod( $method )
	{
		$method = strtolower( $method );
		if( $method != 'get' &&
		    $method != 'post' )
		{
			throw new \Exception( 'Method can only be GET or POST' );
		}

		$this->method = strtolower( $method );
	}

	public function text( $name, $value = '' )
	{
		return $this->escape( $this->isSubmitted() ? $this->getValue( $name ) : $value );
	}


	public function hidden( $name, $value = '' )
	{
		if( $name == 'csrf_token' )
		{
			return \RPC\Registry::get( 'csrf_token' );
		}
		return $this->escape( $this->isSubmitted() ? $this->getValue( $name ) : $value );
	}

	public function checkbox( $name, $value = 1, $checked = false )
	{
		if( $this->isSubmitted() )
		{
			if( substr( $name, -2 ) == '[]' )
			{
				if( in_array( $value, $this->getValue( $name ) ) )
				{
					return ' checked="checked" ';
				}
			}
			else
			{
				if( $value == $this->getValue( $name ) )
				{
					return ' checked="checked" ';
				}
			}
		}
		else
		{
			if( $checked )
			{
				return ' checked="checked" ';
			}
		}
		return '';
	}

	public function radio( $name, $value, $checked = false )
	{
		if( $this->isSubmitted() )
		{
			if( $value == $this->getValue( $name ) )
			{
				return ' checked="checked" ';
			}
		}
		elseif( $checked )
		{
			return ' checked="checked" ';
		}

		return '';
	}

	public function textarea( $name, $value = '' )
	{
		return $this->escape( $this->isSubmitted() ? $this->getValue( $name ) : $value );
	}

	public function select( $name, $source, $selected = '' )
	{

		$selected = ( $this->isSubmitted() && strpos( $name, '$view-&gt;escape' ) !== false ) ? $this->getValue( $name ) : $selected;
		$options = '';

		if( is_array( $source ) )
		{
			foreach( $source as $k => $v )
			{
				if( is_array( $v ) )
				{
					$options .= '<optgroup label="' . $k . '">';
					foreach( $v as $k1 => $v1 )
					{
						$options .= '<option value="' . $this->escape( $k1 ) . '"';

						if( substr( $name, -2 ) == '[]' )
						{
							if( in_array( $k1, $selected ) )
							{
								$options .= ' selected="selected"';
							}
						}
						else
						{
							if( $k1 == $selected )
							{
								$options .= ' selected="selected"';
							}
						}

						$options .= '>' . $this->escape( $v1 ) . '</option>';
					}
					$options .= '</optgroup>';
				}
				else
				{
					$options .= '<option value="' . $this->escape( $k ) . '"';

					if( substr( $name, -2 ) == '[]' )
					{
						if( in_array( $k, $selected ) )
						{
							$options .= ' selected="selected"';
						}
					}
					else
					{
						// problem with == because '' (the default value for selected) is equal to 0 (usual value as key in source arrays)

						if( $k == $selected &&
						    @strlen( $k ) == @strlen( $selected ) ||
						    ( is_array( $selected ) && in_array( $k, $selected ) ) )
						{
							$options .= ' selected="selected"';
						}
					}

					$options .= '>' . $this->escape( $v ) . '</option>';
				}
			}
		}

		return $options;
	}

	public function getValue( $name )
	{
		$m = $this->method;

		if( strpos( $name, '[' ) === false )
		{
			return @$this->request->{$m}[$name];
		}

		if( substr( $name, -2 ) == '[]' )
		{
			$name = substr( $name, 0, -2 );
			$defaultreturn = array();
		}
		else
		{
			$defaultreturn = '';
		}

		$name = "['" . implode( "']['", explode( '[', str_replace( ']', '', $name ) ) ) . "']";
		$val = eval( 'return @$this->request->' . $m . $name . ';' );
		return empty( $val ) ? $defaultreturn : $val;
	}

	public function isSubmitted()
	{
		//check if we have token
		if( $this->method == 'post' )
		{
			return $this->request->getMethod() == 'post';
		}

		$arr = $this->request->getQueryString();
		return ! empty( $arr );
	}

	public function escape( $str )
	{
		return htmlentities( $str, ENT_QUOTES, 'UTF-8', false );
	}

}

?>
