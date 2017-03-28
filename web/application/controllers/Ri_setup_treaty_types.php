<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * RI Setup - Treaty Type Controller
 *
 * This controller falls under "Master Setup" category.
 *
 * @category 	Master Setup
 */

// --------------------------------------------------------------------

class Ri_setup_treaty_types extends MY_Controller
{
	/**
	 * Validation Rules
	 *
	 * @var array
	 */
	private $form_elements = [];

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
        $this->data['site_title'] = 'Master Setup | RI Setup | Treaty Types';

        // Setup Navigation
		$this->active_nav_primary([
			'level_0' => 'master_setup',
			'level_1' => 'ri',
			'level_2' => $this->router->class
		]);

		// Load Model
		$this->load->model('ri_setup_treaty_type_model');

		// Load Activitis Library
		$this->load->library('activity');
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
								'breadcrumbs' => ['Master Setup' => NULL, 'Treaty Types' => NULL]
						])
						->partial('content', 'setup/ri/treaty_types/_index', compact('records'))
						->render($this->data);
	}


}