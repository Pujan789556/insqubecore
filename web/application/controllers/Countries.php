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

class Countries extends MY_Controller
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
        $this->data['site_title'] = 'Master Setup | Countries';

        // Setup Navigation
		$this->active_nav_primary([
			'level_0' => 'master_setup',
			'level_1' => 'general',
			'level_2' => $this->router->class
		]);

		// Load Model
		$this->load->model('country_model');

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
		// this will generate cache name: mc_master_countries_all
		$records = $this->country_model->get_all();

		$this->template->partial(
							'content_header',
							'templates/_common/_content_header',
							[
								'content_header' => 'Manage Countries',
								'breadcrumbs' => ['Master Setup' => NULL, 'Countries' => NULL]
						])
						->partial('content', 'setup/countries/_index', compact('records'))
						->render($this->data);
	}

	// --------------------------------------------------------------------

	public function edit($id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->country_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		/**
		 * Form Submitted?
		 */
		if( $this->input->post() )
		{
			// Update Validation Rule on Update
			$rules = $this->country_model->validation_rules;
			$rules[1]['rules'] = 'trim|required|alpha|exact_length[2]|callback_check_duplicate_alpha2';

			$rules[2]['rules'] = 'trim|required|alpha|exact_length[3]|callback_check_duplicate_alpha3';

			$this->form_validation->set_rules($rules);

			if( $this->form_validation->run() === TRUE)
			{
				$data = $this->input->post();

				// Now Update Data & Log Activity
	        	$done = $this->country_model->update($id, $data, TRUE);

	        	if(!$done)
				{
					$status = 'error';
					$message = 'Could not Update.';
				}
				else
				{
					$status = 'success';
					$message = 'Successfully Updated.';
					$record = $this->country_model->find($id);
				}
			}
			else
			{
				$status = 'error';
				$message = 'Validation Error.';
			}

			$row = $status === 'success'
						? $this->load->view('setup/countries/_single_row', compact('record'), TRUE)
						: '';

			$this->template->json([
				'status' 		=> $status,
				'message' 		=> $message,
				'reloadForm' 	=> $status === 'error',
				'hideBootbox' 	=> $status === 'success',
				'updateSection' => $status === 'success',
				'updateSectionData'	=> $status === 'success'
										? 	[
												'box' => '#_data-row-' . $record->id,
												'html' 		=> $row,
												// Jquery Method 	html|replaceWith|append|prepend etc.
												'method' 	=> 'replaceWith'
											]
										: NULL,
				'form' 	  		=> $status === 'error'
									? 	$this->load->view('setup/countries/_form',
											[
												'form_elements' => $this->country_model->validation_rules,
												'record' 		=> $record
											], TRUE)
									: 	null

			]);
		}


		$form = $this->load->view('setup/countries/_form',
			[
				'form_elements' => $this->country_model->validation_rules,
				'record' 		=> $record
			], TRUE);

		// Return HTML
		$this->template->json(compact('form'));
	}

	// --------------------------------------------------------------------

    /**
     * Check Duplicate Callback: Alpha2
     *
     * @param string $alpha2
     * @param integer|null $id
     * @return bool
     */
    public function check_duplicate_alpha2($alpha2, $id=NULL){

    	$alpha2 = strtoupper( $alpha2 ? $alpha2 : $this->input->post('alpha2') );
    	$id   = $id ? (int)$id : (int)$this->input->post('id');

        if( $this->country_model->check_duplicate(['alpha2' => $alpha2], $id))
        {
            $this->form_validation->set_message('check_duplicate_alpha2', 'The %s already exists.');
            return FALSE;
        }
        return TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Check Duplicate Callback: Alpha3
     *
     * @param string $alpha2
     * @param integer|null $id
     * @return bool
     */
    public function check_duplicate_alpha3($alpha3, $id=NULL){

    	$alpha3 = strtoupper( $alpha3 ? $alpha3 : $this->input->post('alpha3') );
    	$id   = $id ? (int)$id : (int)$this->input->post('id');

        if( $this->country_model->check_duplicate(['alpha3' => $alpha3], $id))
        {
            $this->form_validation->set_message('check_duplicate_alpha3', 'The %s already exists.');
            return FALSE;
        }
        return TRUE;
    }
}