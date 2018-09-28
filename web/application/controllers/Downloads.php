<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Policy Downloads Controller
 *
 * This controller falls under "Master Setup" category.
 *
 * @category 	Master Setup
 */

// --------------------------------------------------------------------

class Downloads extends MY_Controller
{
	// --------------------------------------------------------------------

	function __construct()
	{
		parent::__construct();
	}

	// --------------------------------------------------------------------

	/**
	 * Default Method
	 *
	 * Render the settings
	 *
	 * @return type
	 */
	function index()
	{
		$this->template->render_404();
	}

    // --------------------------------------------------------------------

    /**
     * Download a file related to specific module
     *
     * @param string $module
     * @param string $filename
     * @return void
     */
	public function get($module, $filename)
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized($module, 'download.file') )
		{
			$this->dx_auth->deny_access();
		}

		// Let's Download
		$this->load->helper('download');
        $download_file = rtrim(INSQUBE_DATA_ROOT, '/') . '/' . $module . '/' . $filename;
        if( file_exists($download_file) )
        {
            force_download($download_file, NULL, true);
        }
        else
        {
        	$this->template->render_404('', "Sorry! File Not Found.");
        }
	}
}