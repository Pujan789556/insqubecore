<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * District Controller
 *
 * This controller falls under "Application Settings" category.
 *
 * @category 	Application Settings
 */

// --------------------------------------------------------------------

class Districts extends MY_Controller
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
        $this->data['site_title'] = 'Application Settings | Districts';

        // Setup Navigation
		$this->active_nav_primary([
			'level_0' => 'application_setup',
			'level_1' => 'general',
			'level_2' => $this->router->class
		]);

		// Load Model
		$this->load->model('district_model');

		// URL Base
		$this->_url_base 		 = 'admin/' . $this->router->class;
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
		// this will generate cache name: mc_master_districts_all
		$records = $this->district_model->get_all();
		// echo $this->db->last_query();
		// echo '<pre>'; print_r($records);exit;

		$this->template->partial(
							'content_header',
							'templates/_common/_content_header',
							[
								'content_header' => 'Manage Districts',
								'breadcrumbs' => ['Application Settings' => NULL, 'Districts' => NULL]
						])
						->partial('content', $this->_view_base . '/_index', compact('records'))
						->render($this->data);
	}

	// --------------------------------------------------------------------

	public function edit($id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->district_model->get($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		/**
		 * Form Submitted?
		 */
		if( $this->input->post() )
		{
			// Now Update Data
        	$view = '';

        	$data = $this->input->post();
        	if( $this->district_model->update($id, $data) )
        	{
        		$status = 'success';
				$message = 'Successfully Updated.';
				$record = $this->district_model->get($id);
        	}
			else
			{
				$status = 'error';
				$message = 'Validation Error.';
			}


			$row = $status === 'success'
						? $this->load->view($this->_view_base . '/_single_row', compact('record'), TRUE)
						: '';

			$this->template->json([
				'status' 		=> $status,
				'message' 		=> $message,
				'reloadForm' 	=> $status === 'error',
				'hideBootbox' 	=> $status === 'success',
				'updateSection' => $status === 'success',
				'updateSectionData'	=> $status === 'success'
										? 	[
												'box' => '#_dst-row-' . $record->id,
												'html' 		=> $row,
												// Jquery Method 	html|replaceWith|append|prepend etc.
												'method' 	=> 'replaceWith'
											]
										: NULL,
				'form' 	  		=> $status === 'error'
									? 	$this->load->view($this->_view_base . '/_form',
											[
												'form_elements' => $this->district_model->validation_rules,
												'record' 		=> $record
											], TRUE)
									: 	null

			]);
		}


		$form = $this->load->view($this->_view_base . '/_form',
			[
				'form_elements' => $this->district_model->validation_rules,
				'record' 		=> $record
			], TRUE);

		// Return HTML
		$this->template->json(compact('form'));
	}

	// --------------------------------------------------------------------

    /**
     * Flush Cache Data
     *
     * @return void
     */
    public function flush()
    {
        $this->district_model->clear_cache();
        redirect($this->_url_base);
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

        if( $this->district_model->check_duplicate(['code' => $code], $id))
        {
            $this->form_validation->set_message('check_duplicate', 'The %s already exists.');
            return FALSE;
        }
        return TRUE;
    }
}