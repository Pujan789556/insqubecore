<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Fiscal_years Controller
 *
 * This controller falls under "Application Settings" category.
 *
 * @category 	Application Settings
 */

// --------------------------------------------------------------------

class Fiscal_years extends MY_Controller
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
        $this->data['site_title'] = 'Application Settings | Fiscal year';

        // Setup Navigation
		$this->active_nav_primary([
			'level_0' => 'application_setup',
			'level_1' => 'general',
			'level_2' => $this->router->class
		]);

		// Load Activitis Library
		$this->load->library('activity');

		// URL Base
		$this->_url_base 		 = 'admin/' . $this->router->class;
		$this->data['_url_base'] = $this->_url_base; // for view to access
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
		$records = $this->fiscal_year_model->get_all();
		$records = $records ? $records : [];
		$this->template->partial(
							'content_header',
							'templates/_common/_content_header',
							[
								'content_header' => 'Manage Fiscal year',
								'breadcrumbs' => ['Application Settings' => NULL, 'Fiscal year' => NULL]
						])
						->partial('content', 'setup/fiscal_years/_index', compact('records'))
						->render($this->data);
	}

	// --------------------------------------------------------------------

    /**
     * Flush Cache Data
     *
     * @return void
     */
    public function flush()
    {
        $this->fiscal_year_model->clear_cache();
        redirect($this->_url_base);
    }


}