<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Login_attempts Controller
 *
 * This controller falls under "Application Settings" category.
 *
 * @category 	Application Settings
 */

// --------------------------------------------------------------------

class Login_attempts extends MY_Controller
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
		$this->load->library('Form_validation');

		// Set Template for this controller
        $this->template->set_template('dashboard');

        // Basic Data
        $this->data['site_title'] = 'Application Settings | Login Attempts';

        // Setup Navigation
		$this->active_nav_primary([
			'level_0' => 'application_setup',
			'level_1' => 'security',
			'level_2' => $this->router->class
		]);

		// Load Model
		$this->load->model('login_attempt_model');

		// URL Base
        $this->_url_base         = 'admin/' . $this->router->class;
        $this->_view_base 		 = 'setup/' . $this->router->class;

        $this->data['_url_base'] = $this->_url_base; // for view to access
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
		// this will generate cache name: mc_master_departments_all
		$records = $this->login_attempt_model->get_all();
		$this->template->partial(
							'content_header',
							'templates/_common/_content_header',
							[
								'content_header' => 'Manage Login Attempts',
								'breadcrumbs' => ['Application Settings' => NULL, 'Login Attempts' => NULL]
						])
						->partial('content', $this->_view_base . '/_index', compact('records'))
						->render($this->data);
	}

	// --------------------------------------------------------------------

	public function delete($ip_address)
	{
		// Admin Constraint?
		$done = $this->login_attempt_model->delete_by_ip($ip_address);
		if($done)
		{
			$data = [
				'status' 	=> 'success',
				'message' 	=> 'Successfully deleted!',
				'removeRow' => true,
				'rowId'		=> '#_data-row-'.ip2long($ip_address)
			];
		}
		else
		{
			$data = [
				'status' 	=> 'error',
				'message' 	=> 'Could not be deleted. It might have references to other module(s)/component(s).'
			];
		}
		return $this->template->json($data);
	}

	// --------------------------------------------------------------------

	/**
     * Check Duplicate Callback
     *
     * @param string $code
     * @param integer|null $id
     * @return bool
     */
    public function check_duplicate($code, $id=NULL){

    	$code = strtoupper( $code ? $code : $this->input->post('code') );
    	$id   = $id ? (int)$id : (int)$this->input->post('id');

        if( $this->login_attempt_model->check_duplicate(['code' => $code], $id))
        {
            $this->form_validation->set_message('check_duplicate', 'The %s already exists.');
            return FALSE;
        }
        return TRUE;
    }

    // --------------------------------------------------------------------


    /**
     * Flush Cache Data
     *
     * @return void
     */
    public function flush()
    {
        $this->login_attempt_model->clear_cache();
        redirect($this->_url_base);
    }

}