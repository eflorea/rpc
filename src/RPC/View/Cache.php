<?php

namespace RPC\View;

/**
 * Implements a basic caching mechanism for views
 * 
 * @package View
 */
class Cache
{
	
	/**
	 * Directory path where cached templates are stored
	 * 
	 * @var string
	 */
	protected $directory = '';
	
	/**
	 * Instantiates an object with a given directory to store templates
	 * 
	 * @param string $path
	 */
	public function __construct( $path )
	{
		$this->setDirectory( $path );
	}
	
	/**
	 * Sets the path where templates will be cached
	 * 
	 * @param string $path
	 * 
	 * @return RPC_View_Cache
	 */
	public function setDirectory( $path )
	{
		$path = realpath( $path );
		
		if( ! is_dir( $path ) )
		{
			throw new Exception( $path . ' is not a directory' );
		}
		
		if( ! is_writable( $path ) )
		{
			throw new Exception( $path . ' must be writeable' );
		}
		
		$this->directory = $path;
		
		return $this;
	}
	
	/**
	 * Returns the directory where cached templates are stored
	 * 
	 * @return string
	 */
	public function getDirectory()
	{
		return $this->directory;
	}
	
	/**
	 * Returns a path to the cached version of the given template
	 * 
	 * @param string $file
	 * 
	 * @return string
	 */
	public function get( $file, $template_name )
	{
		$template_name = preg_replace( '/[^a-zA-Z]/', '_', $template_name );
		$path = $this->getPathForFile( $file, $template_name );

		if( ! file_exists( $path ) )
		{
			return false;
		}
		
		$current_time = time();
		$filemtime = filemtime( $file );
		$pathmtime = filemtime( $path );
		
		if( $filemtime > $pathmtime )
		{
			if( $filemtime > $current_time )
			{
				if(  ( $filemtime - ( $filemtime - $current_time ) ) > $pathmtime )
				{
					@unlink( $path );
					return false;
				}
			}
			else
			{
				@unlink( $path );
				return false;
			}
		}
		
		return $path;
	}
	
	/**
	 * Generates the path where a certain file will be written
	 * 
	 * @param string $file
	 * 
	 * @return string
	 */
	protected function getPathForFile( $file, $nice_name )
	{
		return $this->getDirectory() . DIRECTORY_SEPARATOR . 'view_' . $nice_name .  '_' . md5( $file ) . '.php';
	}
	
	/**
	 * Caches the content of a template
	 * 
	 * @param string $file
	 * @param string $content
	 * 
	 * @return RPC_View_Cache
	 */
	public function set( $file, $content, $template_name )
	{
		$template_name = preg_replace( '/[^a-zA-Z]/', '_', $template_name );

		if( ! file_put_contents( $this->getPathForFile( $file, $template_name ), $content ) )
		{
			throw new Exception( 'Cannot write cached version of template "' . $file  . '" to "' . $this->getPathForFile( $file, $template_name ) . '"' );
		}

		return $this;
	}

	/**
	 * Removes all cache files from the set directory
	 */
	public function removeCacheFiles()
	{
		foreach( new DirectoryIterator( $this->getDirectory() ) as $entry )
		{
			if( $entry->isFile() &&
			    strpos( $entry->getPathName(), 'rpc_view_cache_' ) !== false )
			{
				@unlink( $entry->getPathName() );
			}
		}
	}
	
}

?>
