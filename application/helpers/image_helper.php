<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube Image Helper Functions
 *
 * 	This will have helper functions related to Image Manipulation
 * 
 * 
 * @package		InsQube
 * @subpackage	Helpers
 * @category	Helpers
 * @author		IP Bastola <ip.bastola@gmail.com>
 * @link		
 */


// ------------------------------------------------------------------------

if ( ! function_exists('create_thumbnail'))
{
	/**
	 * Create Thumbnail
	 * 
	 * The Image library must be loaded by Controller or Model in order to fetch errors
	 * 
	 * @param string $image_source image full path
	 * @param int $x width
	 * @param int $y height
	 * @param string $thumb_marker thumbnail postfix
	 * @return bool
	 */
	function create_thumbnail( $image_source, $x=100, $y=100, $thumb_marker = '_thumb' )
	{
		$ci = & get_instance();

		if( !file_exists($image_source) )
		{
			// Set error message
			$ci->image_lib->set_error('imglib_invalid_path');
			return FALSE;
		}
		
        
        // Get the global thumb resolutions config
        $x = is_integer($x) && $x > 0 ? $x : 100;
        $y = is_integer($y) && $y > 0 ? $y : 100;
        
        /**
         * Thumbnail Configuration
         */
        $config = array(
            'image_library'     => 'GD2',
            'source_image'      => $image_source,
            'create_thumb' 		=> TRUE,
            'thumb_marker' 		=> $thumb_marker,
            'maintain_ratio'    => TRUE,
            'quality'           => '100',
            'master_dim' 		=> 'height',
            'width'             => $x,
            'height'            => $y
        );
        // Clear Image Library & Old Config
        $ci->image_lib->clear();
        $ci->image_lib->initialize($config);
        return $ci->image_lib->resize();        
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('delete_image'))
{
	/**
	 * Delete Image
	 * 
	 * Delete an Image and Its Thumbnails
	 * 
	 * @param string $source_image Source Image with Full Path
	 * @return void
	 */
	function delete_image( $source_image )
	{
		if( file_exists($source_image))
		{
			$path_parts = pathinfo($source_image); 
			$source_path = $path_parts['dirname'] . DIRECTORY_SEPARATOR;
			$filebase = $path_parts['filename']; // Without Extension
			$extension = $path_parts['extension'];

			// Delete all image with thumbnails
			$mask = $source_path . $filebase . '*'. $extension; // /var/www/media/myfilename*.jpg
			array_map('unlink', glob($mask));
		}     
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('thumbnail_name'))
{
	/**
	 * Get Thumbnail Name of an Image
	 * 
	 * 
	 * @param string Image Name 
	 * @return void
	 */
	function thumbnail_name( $file, $thumb_fix = '_thumb' )
	{
		$base = substr ( $file , 0, strrpos($file, '.')); 
		$extension = substr ( $file , strrpos($file, '.'), strlen($file)); // Output example: .jpg 

		return $base . $thumb_fix . $extension;
	}
}