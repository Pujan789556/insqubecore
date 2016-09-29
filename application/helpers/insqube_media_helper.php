<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube Media Helper Functions
 *
 * 	This will have helper functions related to Media Upload, Thumbnail Creation, Deletion
 * 
 * 
 * @package		InsQube
 * @subpackage	Helpers
 * @category	Helpers
 * @author		IP Bastola <ip.bastola@gmail.com>
 * @link		
 */

// ------------------------------------------------------------------------

if ( ! function_exists('upload_insqube_media'))
{
    /**
     * Upload Insqube Media [file(s)]
     * 
     * Upload Single/Multiple Files
     * 
     * Options:
     *      [
     *          //
     *          // Upload Configuration
     *          //    
     *          'config' => [
     *              'encrypt_name' => TRUE,
     *              'upload_path' => $this->_upload_path, // Module Upload Path
     *              'allowed_types' => 'gif|jpg|png',
     *              'max_size' => '2048'
     *          ],
     * 
     *          //
     *          // Form field's name
     *          //
     *          'form_field' => '' // profile_image | logo | ...
     * 
     *          //
     *          // Thumbnail Options
     *          //
     *          'create_thumb' => true | false
     *          'thumb_xy'  => ['x' => 100, 'y' => 100],
     *          'thumb_marker' => '_thumb'
     * 
     *          //
     *          // Old files to delete if any
     *          //
     *          'old_files'     => ['file1.jpg', 'file4.pdf', ...]
     *          'delete_old'    => true | false
     *      ]
     * 
     * @param array  $options
     * @return mixed
     */
    function upload_insqube_media( $options = [])
    {
    	$ci =& get_instance();	

        /**
         * Extract options into useful goodies
         */
        $config         = $options['config'] ?? NULL;
        $form_field     = $options['form_field'] ?? '';
        $old_files      = $options['old_files'] ?? [];
        $delete_old     = $old_files ? ($options['delete_old'] ?? TRUE) : FALSE; // If old files supplied, TRUE
        $create_thumb   = $options['create_thumb'] ?? TRUE;
        $thumb_xy       = $create_thumb ? ($options['thumb_xy'] ?? ['x' =>100, 'y' => 100]) : FALSE;
        $thumb_marker   = $create_thumb ? ($options['thumb_marker'] ?? '_thumb') : '';
        
        $new_files  = [];
        $status     = 'error';
        $message    = 'Invalid upload options.';

        /**
         * Basic Upload Config
         */
        $default_config = [
            'encrypt_name'  => TRUE,
            'allowed_types' => 'gif|jpg|png|pdf|rtf|doc|docx|odt|pdf|txt|xls|xlsx|ods|csv',
            'max_size'      => '4096'
        ];

        // Override default
        $config = array_merge($default_config, $config);

        // We must have "upload_path" Set
        if( empty($form_field) OR !isset($config['upload_path']) OR !is_dir($config['upload_path']))
        {
            return ['status' => $status, 'message' => $message, 'files' => []];
        }

        if( isset($_FILES[$form_field]['name']) && !empty($_FILES[$form_field]['name']) )
        {
            $ci->load->library('upload', $config);

            if( $ci->upload->do_upload($form_field))
            {
                $uploaded = $ci->upload->data(); 

                /**
                 * Single Upload Response:
                 * 
                 *      $uploaded = ['key' => 'val', ...]
                 * 
                 * Multiple Uploads Response:
                 * 
                 *      $uploaded = [
                 *          0 => ['key' => 'val', ...],
                 *          1 => ['key' => 'val', ...],
                 *          ...
                 *      ]
                 */
                if( is_assoc($uploaded) )
                {
                    $uploaded = array($uploaded);
                }

                /**
                 * Post upload Tasks
                 * 
                 *  1. Create thumbnails if file is image and option says so
                 *  2. Delete old file if option says so
                 */
                if($create_thumb)
                {
                    $ci->load->library('image_lib');   
                }
                foreach($uploaded as $single)
                {
                    $new_files[] = $single['file_name']; 

                    // Task 1: Create Thumbnails
                    if($create_thumb && $single['is_image'])
                    {
                        create_thumbnail( $single['full_path'], $thumb_xy, $thumb_marker ); 
                    }
                }

                // Task 2: Delete old file
                if($delete_old)
                {
                    // Get upload path
                    $upload_path = rtrim($config['upload_path'], '/');
                    foreach($old_files as $file)
                    {
                        delete_insqube_document($upload_path . '/' . $file);
                    }
                } 

                $status = 'success';
                $message = count($uploaded) . ' files uploaded successfully.'; 
            }
            else
            {
                $message = $ci->upload->display_errors();                 
            }               
        }
        else 
        {
            $status = 'no_file_selected';
            $message = 'No file(s) selected to upload.';
        }
        return [
            'status'    => $status,
            'message'   => $message,
            'files'     => $new_files
        ];
    }
}

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
	function create_thumbnail( $image_source, $thumb_xy = ['x' => 100, 'y' => 100], $thumb_marker = '_thumb' )
	{
		$ci = & get_instance();

		if( !file_exists($image_source) )
		{
			// Set error message
			$ci->image_lib->set_error('imglib_invalid_path');
			return FALSE;
		}
		
        
        // Get the global thumb resolutions config
        $x = $thumb_xy['x'] ?? 100;
        $y = $thumb_xy['y'] ?? 100;
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

if ( ! function_exists('delete_insqube_document'))
{
	/**
	 * Delete an Insqube Media
	 * 
	 * If it's an image, it delete its thumbnails too.
	 * 
	 * @param string $file Source File with Full Path
	 * @return void
	 */
	function delete_insqube_document( $file )
	{
		if( file_exists($file) && is_file($file) )
		{
			$path_parts = pathinfo($file); 
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