<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube Extra File helper Functions
 *
 * @package		InsQube
 * @subpackage	Helpers
 * @category	Helpers
 * @author		IP Bastola
 * @link		https://twitter.com/ipbastola
 */

// ------------------------------------------------------------------------

if ( ! function_exists('render_pdf'))
{
	/**
	 * Render PDF on browser
	 *
	 * @param	string $file 	Fullpath of file
	 * @return	void
	 */
	function render_pdf($file)
	{
		// Get File name
		$filename = basename($file);

		// Compute File size
		$filesize = @filesize($file);
		if(!$filesize)
		{
			return ;
		}

		// Let's Render
		header('Content-Type: application/pdf');
		header('Content-Disposition: inline; filename="'.$filename.'"');
		header('Expires: 0');
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: '.$filesize);
		header('Cache-Control: private, no-transform, no-store, must-revalidate');
		@readfile($file);
		exit;

	}
}
// ------------------------------------------------------------------------

