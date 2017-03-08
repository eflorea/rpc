<?php

namespace RPC;

use Exception;

/**
 * Very simple image class which allows for resizing and converting
 * between formats
 * 
 * @package Core
 */
class Image
{
	
	/**
	 * Image types the class can work on
	 * 
	 * @var array
	 */
	protected $types = array( 'jpg', 'png', 'gif' );
	
	/**
	 * Image resource
	 * 
	 * @var resource
	 */
	protected $resource = null;
	
	/**
	 * Image's width
	 * 
	 * @var int
	 */
	protected $width = 0;
	
	/**
	 * Image's height
	 * 
	 * @var int
	 */
	protected $height = 0;
	
	/**
	 * Resizing will be done proportionally to the image size
	 */
	const RESIZE_PROPORTIONAL = 1;
	
	/**
	 * Resizing will be done with the given sizes, no matter the image size
	 */
	const RESIZE_STRICT = 2;
	
	/**
	 * Initializes a new image object
	 * 
	 * @param resource $resource
	 */
	public function __construct( $path )
	{
		if( ! is_readable( $path ) )
		{
			throw new Exception( 'File "' . $path . '" is not readable' );
		}
		
		$res = imagecreatefromstring( file_get_contents( $path ) );
		if( ! $res )
		{
			throw new Exception( 'The given file is not a valid image' );
		}
		
		list( $width, $height ) = getimagesize( $path );
		
		$this->resource = $res;
		$this->width  = $width;
		$this->height = $height;
	}
	
	/**
	 * Writes the resource to a given path
	 * 
	 * @param string $path
	 * 
	 * @return RPC_Image
	 */
	public function save( $path )
	{
		$ext = $this->getExtension( $path );
		
		switch( $ext )
		{
			case 'jpeg':
				imagejpeg( $this->getResource(), $path, 100 );
				break;
			case 'png':
				imagepng( $this->getResource(), $path, 0 );
				break;
			case 'gif':
				imagegif( $this->getResource(), $path );
				break;
			default:
				throw new Exception( 'Unknown image format: ' . $ext );
		}
		
		return $this;
	}
	
	/**
	 * Outputs the image
	 * 
	 * @param string $type
	 * 
	 * @return RPC_Image
	 */
	public function output( $type = 'png' )
	{
		$ext = $this->getExtension( $type );
		
		switch( $ext )
		{
			case 'jpeg':
				imagejpeg( $this->getResource(), null, 100 );
				break;
			case 'png':
				imagepng( $this->getResource(), null, 0, PNG_NO_FILTER );
				break;
			case 'gif':
				imagegif( $this->getResource(), null );
				break;
			default:
				throw new Exception( 'Unknown image format:' . $ext );
		}
		
		return $this;
	}
	
	/**
	 * Resizes the image to a given width and height and returns a new
	 * image object
	 * 
	 * @param int $width
	 * @param int $height
	 * @param int $resize
	 * 
	 * @return RPC_Image
	 */
	public function resize( $width, $height, $resize = self::RESIZE_PROPORTIONAL )
	{
		/*
			These will make ensure that the resize will not be larger
			than the original image, resulting in a pixelized version
		*/
		if( $width > $this->getWidth() )
		{
			$width = $this->getWidth();
		}
		
		if( $height > $this->getHeight() )
		{
			$height = $this->getHeight();
		}
		
		if( $resize == self::RESIZE_PROPORTIONAL )
		{
			$ratio = $this->getWidth() / $this->getHeight();
			
			if( ( $width / $height ) > $ratio )
			{
				$width = floor( $height * $ratio );
				if( $width > $this->getWidth() )
				{
					$width = $this->getWidth();
					$height = floor( $width / $ratio );
				}
			}
			else
			{
				$height = floor( $width / $ratio );
				if( $height > $this->getHeight() )
				{
					$height = $this->getHeight();
					$width = floor( $height * $ratio );
				}
			}
		}
				
		$img = imagecreatetruecolor( $width, $height );
		imagecopyresampled( $img, $this->getResource(), 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight() );
		
		$this->resource = $img;
		$this->setWidth( $width );
		$this->setHeight( $height );
		
		return $this;
	}
	
	/**
	 * Crops the image to the given coordinates
	 * 
	 * @param int $x0
	 * @param int $x1
	 * @param int $y0
	 * @param int $y1
	 * 
	 * @return RPC_Image
	 */
	public function crop( $x0, $x1, $y0, $y1 )
	{
		$img = imagecreatetruecolor( $x1 - $x0, $y1 - $y0 );
		
		imagecopy( $img, $this->getResource(), $x0, $y0, $x1, $y1 );
		
		$this->resource = $img;
		$this->setWidth( $x1 - $x0 );
		$this->setHeight( $y1 - $y0 );
		
		return $this;
	}
	
	/**
	 * Adds the given image as a watermark on the current image
	 * 
	 * @param string $img
	 * @param int    $x
	 * @param int    $y
	 * 
	 * @return RPC_Image
	 */
	public function addWatermark( $img, $x, $y )
	{
		list( $mwidth, $mheight ) = getimagesize( $img );
		
		if( ! $mwidth ||
		    ! $mheight )
		{
			throw new Exception( 'Invalid image passed as watermark' );
		}
		
		$ext = $this->getExtension( $img );
		$f   = 'imagecreatefrom' . $ext;
		
		$mark = $f( $img );
		
		$iwidth  = $this->getWidth();
		$iheight = $this->getHeight();
		
		imagecopy( $this->resource, $mark, $iwidth - $mwidth + $x, $iheight - $mheight + $y, 0, 0, $mwidth, $mheight );
		
		return $this;
	}
	
	/**
	 * Returns the image's width
	 * 
	 * @return int
	 */
	public function getWidth()
	{
		return $this->width;
	}
	
	/**
	 * Sets the image's width
	 * 
	 * @param int $width
	 * 
	 * @return RPC_Image
	 */
	protected function setWidth( $width )
	{
		$this->width = $width;
		return $this;
	}
	
	/**
	 * Returns the image's height
	 * 
	 * @return int
	 */
	public function getHeight()
	{
		return $this->height;
	}
	
	/**
	 * Sets the image's height
	 * 
	 * @param int $height
	 * 
	 * @return RPC_Image
	 */
	public function setHeight( $height )
	{
		$this->height = $height;
	}
	
	/**
	 * Returns the resource the object is working on
	 * 
	 * @return resource
	 */
	public function getResource()
	{
		return $this->resource;
	}
	
	/**
	 * Returns the extension from a given path
	 * 
	 * @param string $path
	 * 
	 * @return string
	 */
	protected function getExtension( $path )
	{
		$ext = strtolower( substr( $path, strrpos( $path, '.' ) + 1 ) );
		if( $ext == 'jpg' )
		{
			$ext = 'jpeg';
		}
		
		return $ext;
	}
	
}

?>
