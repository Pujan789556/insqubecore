<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * RI Setup - Treaty Type Controller
 *
 * This controller falls under "Application Settings" category.
 *
 * @category 	Application Settings
 */

// --------------------------------------------------------------------

class Ri_treaty_types extends MY_Controller
{
	/**
     * Controller URL
     */
    private $_url_base;

    // --------------------------------------------------------------------

	function __construct()
	{
		parent::__construct();

		// Only Admin Can access this controller
		if( !$this->dx_auth->is_admin() )
		{
			$this->dx_auth->deny_access();
		}

		// Form Validation
		// $this->load->library('Form_validation');

		// Set Template for this controller
        $this->template->set_template('dashboard');

        // Basic Data
        $this->data['site_title'] = 'Application Settings | RI Setup | Treaty Types';

        // Setup Navigation
		$this->active_nav_primary([
			'level_0' => 'application_setup',
			'level_1' => 'ri',
			'level_2' => $this->router->class
		]);

		// Load Model
		$this->load->model('ri_setup_treaty_type_model');

		// Load Activitis Library
		$this->load->library('activity');

		// URL Base
		$this->_url_base 		 = 'admin/' . $this->router->class;
		$this->_view_base 		 = 'setup/ri/' . $this->router->class;

		$this->data['_url_base'] 	= $this->_url_base; // for view to access
		$this->data['_view_base'] 	= $this->_view_base;
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
		/**
		 * Normal Form Render
		 */
		// this will generate cache name: mc_master_fiscal_yrs_all
		$records = $this->ri_setup_treaty_type_model->get_all();
		$records = $records ? $records : [];
		$this->template->partial(
							'content_header',
							'templates/_common/_content_header',
							[
								'content_header' => 'Manage Treaty Types',
								'breadcrumbs' => ['Application Settings' => NULL, 'Re-Insurance' => NULL, 'Treaty Types' => NULL]
						])
						->partial('content', $this->_view_base . '/_index', compact('records'))
						->render($this->data);
	}


}