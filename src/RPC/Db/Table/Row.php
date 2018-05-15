<?php


namespace RPC\Db\Table;

use ArrayAccess;

use RPC\Db\Table\Adapter;


/**
 * Generic Row class that can be used with any database adapter
 *
 * @package Db
 */
class Row implements ArrayAccess
{

	/**
	 * Stores a reference to the parent table
	 *
	 * @var RPC_Db_Table_Adapter
	 */
	protected $table = null;

	/**
	 * Array with errors found when trying to save
	 *
	 * @var array
	 */
	protected $errors = array();

	/**
	 * Array containing the original field values
	 *
	 * @var array
	 */
	protected $clean = null;

	/**
	 * Array of fields whose values have been changed
	 *
	 * @var array
	 */
	protected $changedfields = array();

	/**
	 * Array containing the actual row
	 *
	 * @var array
	 */
	protected $row = array();

	/**
	 * Changes to true if any of the record's values are changed
	 *
	 * @var bool
	 */
	protected $dirty = false;

	/**
	 * Array containing other fields than the table's attributes
	 *
	 * @var array
	 */
	protected $extrafields = array();

	/**
	 * Class constructor
	 *
	 * @param RPC_Db_Table_Adapter $table
	 * @param object               $row
	 */
	public function __construct( \RPC\Db\Table\Adapter $table, $row = array() )
	{
		$this->setTable( $table );

		$this->clean = $row;
		$this->row   = $row;
	}

	/**
	 * Convenience method for returning database object
	 *
	 * @return RPC_Db_Adapter
	 */
	public function getDb()
	{
		return $this->getTable()->getDb();
	}

	/**
	 * Sets the table instance to which the row belongs
	 *
	 * @param RPC_Db_Table_Adapter $table
	 */
	protected function setTable( \RPC\Db\Table\Adapter $table )
	{
		$this->table = $table;
	}

	/**
	 * Returns the table instance where the row belongs
	 *
	 * @return RPC_Db_Table_Adapter
	 */
	public function getTable()
	{
		return $this->table;
	}

	/**
	 * Just a shortcut method for calling the table method with the same name
	 *
	 * @return array
	 */
	public function getFields()
	{
		return $this->getTable()->getFields();
	}

	/**
	 * Returns an array whose keys are the names of the fields whose
	 * values have been changed
	 *
	 * @return array
	 */
	public function getChangedFields()
	{
		return $this->changedfields;
	}

	/**
	 * Return an array of extra fields for current row
	 *
	 * @return array
	 */
	public function getExtraFields()
	{
		return $this->extrafields;
	}

	/**
	 * Sets the "dirty" status for the row, indicating if it has changed or not
	 *
	 * @param bool $dirty
	 *
	 * @return RPC_Db_Table_Row
	 */
	protected function setDirty( $dirty )
	{
		$this->dirty = $dirty;

		return $this;
	}

	/**
	 * Returns true if any change has occured on the record
	 *
	 * @return bool
	 */
	public function isDirty()
	{
		return $this->dirty;
	}

	/**
	 * Returns an array with the initial (ie when it was retrieved from the
	 * table) values in the row
	 *
	 * @return array
	 */
	public function getCleanArray()
	{
		return $this->clean;
	}

	/**
	 * Reverts the row to it's original state when it was retrieved from the
	 * database or since the last save
	 *
	 * @return RPC_Db_Table_Row
	 */
	public function revert()
	{
		$this->row = $this->getCleanArray();

		return $this;
	}

	/**
	 * In case any change occurs on the record, then it is marked as dirty
	 *
	 * @param string|int $index
	 * @param mixed      $newval
	 *
	 * @implements ArrayAccess
	 */
	public function offsetSet( $index, $newval )
	{
		if( $this->offsetExists( $index ) )
		{
			$this->changedfields[$index] = 1;
		}
		else
		{
			$this->extrafields[$index] = $newval;
		}

		/**
		 * The primary key cannot be set using $row[field] syntax, there is a
		 * special method
		 *
		 * @see self::setPk()
		 */
		if( $index == $this->getTable()->getPkField() )
		{
			throw new \Exception( 'The primary key can only be changed using the setPk method' );
		}

		/**
		 * Only if the record already exists in the database we should mark it
		 * as dirty when a change occurs on one field
		 */
		if( $this->getPk() &&
		    $this->offsetGet( $index ) != $newval )
		{
			$this->setDirty( true );
		}

		$this->row[$index] = $newval;
	}

	/**
	 * N/A
	 *
	 * @param string $index
	 *
	 * @implements ArrayAccess
	 */
	public function offsetUnset( $index )
	{
		throw new \Exception( 'You cannot remove a field from the row' );
	}

	/**
	 * Checks to see if a certain field exists
	 *
	 * @param string $index Field name
	 *
	 * @implements ArrayAccess
	 */
	public function offsetExists( $index )
	{
		return in_array( $index, $this->getFields() );
	}

	/**
	 * Returns the value for the required field
	 *
	 * @param string $index Field name
	 *
	 * @return mixed
	 *
	 * @implements ArrayAccess
	 */
	public function offsetGet( $index )
	{
		if( ! $this->offsetExists( $index ) )
		{
			if( isset( $this->extrafields[$index] ) )
			{
				return $this->extrafields[$index];
			}
			return null;
		}

		return $this->row[$index];
	}

	/**
	 * Sets a value for the row's primary key. This should not be needed in the
	 * application, but is provided as it is used internally, after an row is
	 * inserted
	 *
	 * @param mixed $pk
	 *
	 * @return RPC_Db_Table_Row
	 */
	public function setPk( $pk, $force_pk = false )
	{
		if( $force_pk )
		{
			$this->force_pk = $force_pk;
		}

		if( empty( $pk ) )
		{
			throw new \Exception( 'Primary key cannot be empty' );
		}

		$this->row[$this->getTable()->getPkField()] = $pk;

		return $this;
	}

	/**
	 * Returns the row's primary key value
	 *
	 * @return int
	 */
	public function getPk()
	{
		return $this->offsetGet( $this->getTable()->getPkField() );
	}

	/**
	 * Returns the number of errors on this row
	 *
	 * @return int
	 */
	public function hasErrors()
	{
		return count( $this->errors );
	}

	/**
	 * Sets an array of errors for multiple fields in the row
	 *
	 * @param array $errors
	 *
	 * @return RPC_Db_Table_Row
	 */
	public function setErrors( $errors )
	{
		$this->errors = $errors;

		return $this;
	}

	/**
	 * Return the error messages
	 *
	 * @return array
	 */
	public function getErrors()
	{
		return $this->errors;
	}

	/**
	 * Sets an error for a specific table field
	 *
	 * @param string $field
	 * @param string $error
	 *
	 * @return RPC_Db_Table_Row
	 */
	public function setError( $field, $error = '' )
	{
		$this->errors[$field] = $error;

		return $this;
	}

	/**
	 * Returns the error for a specific table field
	 *
	 * @return string
	 */
	public function getError( $field )
	{
		return isset( $this->errors[$field] ) ? $this->errors[$field] : null;
	}

	/**
	 * Fill the values on the row with values from an array. This is a better
	 * choice as opposed to calling <code>$row->validate( $values )</code>
	 * because one may need to add more data to the row before validating
	 *
	 * @param array $values
	 * @param boolean $field_exists
	 *
	 * @return RPC_Db_Table_Row
	 */
	public function populate( $values, $options = array() )
	{
		$fields_to_ignore 	= array();
		$fields_to_parse 	= array();

		if( $options )
		{
			if( is_array( $options ) )
			{
				if( array_key_exists( 'skip', $options ) )
				{
					if( is_array( $options['skip'] ) )
					{
						$fields_to_ignore = $options['skip'];
					}
					else
					{
						$fields_to_ignore[] = $options['skip'];
					}
				}
				else
				{
					$fields_to_parse = $options;
				}
			}
			else
			{
				$fields_to_parse[] = $options;
			}
		}


		$fields = $this->getFields();

		if( $values )
		{
			foreach( $values as $field => $value )
			{
				if( $field != $this->getTable()->getPkField() )
				{
					if( $fields_to_ignore )
					{
						if( ! in_array( $field, $fields_to_ignore ) )
						{
							if( in_array( $field, $fields ) )
							{
								$this->offsetSet( $field, $value );
							}
							else
							{
								$this->extrafields[$field] = $value;
							}
						}
					}
					elseif( $fields_to_parse )
					{
						if( in_array( $field, $fields_to_parse ) )
						{
							if( in_array( $field, $fields ) )
							{
								$this->offsetSet( $field, $value );
							}
							else
							{
								$this->extrafields[$field] = $value;
							}
						}
					}
					else
					{
						if( in_array( $field, $fields ) )
						{
							$this->offsetSet( $field, $value );
						}
						else
						{
							$this->extrafields[$field] = $value;
						}
					}
				}
			}
		}

		return $this;
	}

	//predefined validation functions
	public function _validate_required( $column, $value = '', $msg = 'This field is required.' )
	{
		$v = new \RPC\Validator\NotEmpty( $msg );
		if( ! $v->validate( $value ) )
		{
			return $this->setError( $column, $v->getError() );
		}
	}

	public function _validate_email( $column, $value = '', $msg = 'This field requires a valid email address' )
	{
		$v = new \RPC\Validator\Email( $msg );
		if( ! $v->validate( $value ) )
		{
			return $this->setError( $column, $v->getError() );
		}
	}

	public function _validate_phone( $column, $value = '', $msg = 'This field requires a valid phone number.' )
	{
		$v = new \RPC\Validator\Phone( $msg );
		if( ! $v->validate( $value ) )
		{
			return $this->setError( $column, $v->getError() );
		}
	}

	public function _validate_zip( $column, $value = '', $msg = 'This field requires a valid zip address.' )
	{
		$v = new \RPC\Validator\Zip( $msg );
		if( ! $v->validate( $value ) )
		{
			return $this->setError( $column, $v->getError() );
		}
	}

	public function _validate_numeric( $column, $value = '', $msg = 'This field requires a numeric value.' )
	{
		$v = new \RPC\Validator\Numeric( $msg );
		if( ! $v->validate( $value ) )
		{
			return $this->setError( $column, $v->getError() );
		}
	}

	public function _validate_max( $column, $value = '', $max = 250, $msg = 'This field cannot contain be more than {$max} characters' )
	{
		$v = new \RPC\Validator\Length( -1, $max, $msg );
		if( ! $v->validate( $value ) )
		{
			return $this->setError( $column, str_replace( '{$max}', $max, $v->getError() ) );
		}
	}

	public function _validate_min( $column, $value = '', $min = 250, $msg = 'This field needs to have at least {$min} characters' )
	{
		$v = new \RPC\Validator\Length( $min, -1, $msg );
		if( ! $v->validate( $value ) )
		{
			return $this->setError( $column, str_replace( '{$min}', $min, $v->getError() ) );
		}
	}

	public function _validate_password( $column, $value = '', $msg = 'This field needs to be at least 6 characters long' )
	{
		$v = new \RPC\Validator\Password( $msg );
		if( ! $v->validate( $value ) )
		{
			return $this->setError( $column, $v->getError() );
		}
	}

	public function _parseValidateRules( $rule = '' )
	{
		$rules = array();

		if( is_array( $rule ) )
		{
			foreach( $rule as $k => $r )
			{
				if( is_numeric( $k ) )
				{
					$rules[$r] = false;
				}
				else
				{
					$rules[$k] = $r;
				}
			}
		}
		else
		{
			$tmp = explode( '|', $rule );

			if( $tmp )
			{
				foreach( $tmp as $k => $r )
				{
					if( $r )
					{
						$rules[$r] = false;
					}
				}
			}
		}

		return $rules;
	}

	public function _validateField( $field, $rules = array() )
	{

		if( count( $rules ) )
		{
			//check if optional is in the rules then ignore all the other rules
			if( isset( $rules['optional'] ) )
			{
				if( $this->$field() == '' )
				{
					return true;
				}
			}

			foreach( $rules as $rule => $msg )
			{
				// skip over running a validation method if this rule is 'optional'
				if( $rule == 'optional' )
				{
					continue;
				}

				//check if msg exists
				if( is_numeric( $rule ) )
				{
					$rule = $msg;
					$msg = false;
				}

				if( strpos( $rule, 'max:' ) !== false ||
					strpos( $rule, 'min:' ) !== false )
				{
					$min_or_max = str_replace( array( 'max:', 'min:' ), '', $rule );
					$rule = str_replace( ':' . $min_or_max, '', $rule );

					$method = '_validate_' . $rule;

					if( $msg )
					{
						$this->$method( $field, $this->$field(), $min_or_max, $msg );
					}
					else
					{
						$this->$method( $field, $this->$field(), $min_or_max );
					}
				}
				else
				{
					//check if a default validation exists
					$method = '_validate_' . $rule;
					if( method_exists( $this, $method ) )
					{
						if( $msg )
						{
							$this->$method( $field, $this->$field(), $msg );
						}
						else
						{
							$this->$method( $field, $this->$field() );
						}
					}
					else
					{
						$this->$rule();
					}
				}


				//check if validation passed so we don't run the other rules
				if( $this->getError( $field ) )
				{
					return false;
				}
			}
		}
		else
		{
			$method = 'validate_' . $field;
			if( method_exists( $this, $method ) )
			{
				$this->$method();
			}
		}
	}

	/**
	 * Checks to see if the values on a row are valid
	 *
	 * @return bool
	 */
	public function validate( $options = array() )
	{
		$pk = $this->getPk();

		$validate_rules = array();

		if( method_exists( $this, 'preValidate' ) )
		{
			$this->preValidate();
		}

		$fields = $this->getTable()->getFields();

		$extra_fields = $this->getExtraFields();

	    if( $extra_fields )
	    {
	    	foreach( $extra_fields as $field => $value )
	    	{
	    		$fields[] = $field;
	    	}
	    }

		if( count( $options ) )
		{
			//check if we have ignore
			if( isset( $options['skip'] ) )
			{
				if( is_array( $options['skip'] ) )
				{
					foreach( $options['skip'] as $field )
					{
						if( ( $key = array_search( $field, $fields ) ) !== false )
						{
    						unset( $fields[$key] );
						}
					}
				}
				else
				{
					if( ( $key = array_search( $field, $fields ) ) !== false )
					{
						unset( $fields[$key] );
					}
				}
				unset( $options['skip'] );
			}

			if( count( $options ) )
			{
				foreach( $options as $field => $option )
				{
					//this is for when the rule doesn't exist and the user wants the default validation
					if( is_numeric( $field ) )
					{
						$validate_rules[$option] = $this->_parseValidateRules( '' );
						
					}
					else
					{
						$validate_rules[$field] = $this->_parseValidateRules( $option );
					}

				}
			}
		}
		else
		{
			foreach( $fields as $column )
			{
				$validate_rules[$column] = $this->_parseValidateRules( '' );
			}
		}


		foreach( $validate_rules as $field => $rules )
		{
			$this->_validateField( $field, $rules );
		}

		if( method_exists( $this, 'postValidate' ) )
		{
			$this->postValidate();
		}

		return ! (bool) $this->hasErrors();
	}

	/**
	 * Inserts or updates an array into a table, based on the primary key: if
	 * the primary key is empty it will insert, otherwise update
	 */
	public function save()
	{
		$pk = $this->getPk();
		if( empty( $pk )|| ( isset( $this->force_pk ) && $this->force_pk ) )
		{
			$saved = $this->getTable()->insert( $this );
		}
		else
		{
			$saved = $this->getTable()->update( $this );
		}

		$this->clean = $this->row;
		$this->changedfields = array();
		$this->setDirty( false );

		return $saved;
	}

	/**
	 * Deletes the record from the table
	 *
	 * @return bool
	 */
	public function delete()
	{
		$pk = $this->getPk();
		if( ! empty( $pk ) )
		{
			if( $this->getTable()->delete( $this ) )
			{
				$this->__destruct();

				return true;
			}
		}

		return false;
	}

	/**
	 * In case one wants a new row containing the same properties the row's
	 * primary key will be nulled and the errors removed
	 */
	public function __clone()
	{
        $this->populate( $this->getData() ); // force all fields to be marked as changed, otherwise they won't be saved
		$this->row[$this->getTable()->getPkField()] = null;
        $this->row['created'] = null;
        $this->row['modified'] = null;

		$this->errors = array();
		$this->clean  = $this->row;
		$this->dirty  = false;
	}

	/**
	 * Class destructor
	 */
	public function __destruct()
	{
		$this->table  = null;
		$this->row    = null;
		$this->clean  = null;
		$this->dirty  = null;
		$this->errors = null;
	}


	/**
	* Overloading call
	*/
	public function __call( $name, $arguments = false )
    {
    	if( ! method_exists( $this, $name ) )
		{
			if( $arguments )
			{
				if( isset( $arguments[0] ) ||
					$arguments[0] === NULL )
				{
					$this[$name] = $arguments[0];
				}
			}

			//check if property exists
			if( isset( $this[$name] ) ||
				isset( $this->extrafields[$name] ) )
			{
				return $this[$name];
			}
			else
			{
				throw new \Exception( "Field $name doesn't exist on the row object" );
			}
		}
    }


    public function getData()
    {
    	$data = array();

    	$fields = $this->getFields();

    	if( $fields )
    	{
	    	foreach( $this->getFields() as $field )
	    	{
	    		$data[$field] = $this[$field];
	    	}
	    }

	    $extra_fields = $this->getExtraFields();

	    if( $extra_fields )
	    {
	    	foreach( $extra_fields as $field => $value )
	    	{
	    		$data[$field] = $this[$field];
	    	}
	    }

    	return $data;
    }

}

?>
