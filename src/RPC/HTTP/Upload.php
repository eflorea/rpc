<?php

namespace RPC\HTTP;

/**
 * Convenience class which helps with uploading files
 * 
 * @package HTTP
 */
class Upload
{
	
	/**
	 * Name set in the form's file input
	 * 
	 * @var string
	 */
	protected $name = '';

	/**
	 * Array of extensions allowed for the file
	 * 
	 * @var array
	 */
	protected $types = array();
	
	/**
	 * Maximum allowed size (in bytes) for the file
	 * 
	 * @var int
	 */
	protected $maxsize = 0;
	
	/**
	 * If true empty files are considered valid (defaults to false)
	 * 
	 * @var bool
	 */
	protected $allowempty = false;
	
	/**
	 * Class constructor which receives the name of the file
	 * 
	 * @param string $name
	 */
	public function __construct( $name )
	{
		$this->name = $name;
	}
	
	/**
	 * Maximum allowed size for the uploaded file
	 * 
	 * @param int $bytes
	 * 
	 * @return RPC_HTTP_Upload
	 */
	public function setMaxSize( $bytes )
	{
		$this->maxsize = $bytes;
		
		return $this;
	}

	/**
	 * The extension of the uploaded file must be one of these
	 * 
	 * @param array $types
	 * 
	 * @return RPC_HTTP_Upload
	 */
	public function setValidExtensions()
	{
		$types = func_get_args();
		$this->types = array_map( 'strtolower', $types );
		
		return $this;
	}
	
	/**
	 * Whether to consider a 0 size file as valid
	 */
	public function allowEmpty( $allow = true )
	{
		$this->allowempty = $allow;
		
		return $this;
	}
	
	/**
	 * Returns the file's original name
	 * 
	 * @return string
	 */
	public function getName()
	{
		return $_FILES[$this->name]['name'];
	}
	
	/**
	 * Returns the file's size in bytes
	 * 
	 * @return int
	 */
	public function getSize()
	{
		return $_FILES[$this->name]['size'];
	}
	
	/**
	 * Returns a path to the temporary file
	 * 
	 * @return string
	 */
	public function getPath()
	{
		return $_FILES[$this->name]['tmp_name'];
	}
	
	/**
	 * Method to check the file exists and isn't over the maximum
	 * allowed size
	 * 
	 * @return bool
	 */
	public function validate()
	{
		if( ! empty( $_FILES[$this->name] ) &&
			empty( $_FILES[$this->name]['error'] ) &&
			is_uploaded_file( $_FILES[$this->name]['tmp_name'] ) &&
		    $this->getSize() <= $this->maxsize )
		{
			if( ! $this->allowempty &&
				( ! $this->getSize() > 0 ) )
			{
				return false;
			}
			
			if( empty( $this->types ) )
			{
				return true;
			}
			
			$name = $this->getName();
			if( ( $pos = strrpos( $name, '.' ) ) === false )
			{
				$extension = '';
			}
			else
			{
				$extension = substr( $name, $pos + 1 );
			}
			
			return in_array( strtolower( $extension ), $this->types );
		}
		
		return false;
	}
	
	/**
	 * Move the file to the given location
	 * 
	 * @param string $path
	 * 
	 * @return bool Whether the file has been moved
	 */
	public function moveTo( $path )
	{
		return move_uploaded_file( $_FILES[$this->name]['tmp_name'], $path );
	}
	
}

?>
