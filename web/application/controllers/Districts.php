<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * District Controller
 *
 * This controller falls under "Master Setup" category.
 *
 * @category 	Master Setup
 */

// --------------------------------------------------------------------

class Districts extends MY_Controller
{
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
        $this->data['site_title'] = 'Master Setup | Districts';

        // Setup Navigation
		$this->active_nav_primary([
			'level_0' => 'master_setup',
			'level_1' => 'general',
			'level_2' => $this->router->class
		]);

		// Load Model
		$this->load->model('district_model');

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
								'breadcrumbs' => ['Master Setup' => NULL, 'Districts' => NULL]
						])
						->partial('content', 'setup/districts/_index', compact('records'))
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
						? $this->load->view('setup/districts/_single_row', compact('record'), TRUE)
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
									? 	$this->load->view('setup/districts/_form',
											[
												'form_elements' => $this->district_model->validation_rules,
												'record' 		=> $record
											], TRUE)
									: 	null

			]);
		}


		$form = $this->load->view('setup/districts/_form',
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
        redirect($this->router->class);
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