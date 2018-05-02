<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Region Controller
 *
 * NOTE: The regions are area provisioned by the Beema Samiti.
 *
 * This controller falls under "Master Setup" category.
 *
 * @category 	Master Setup
 */

// --------------------------------------------------------------------

class Regions extends MY_Controller
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
        $this->data['site_title'] = 'Master Setup | Regions';

        // Setup Navigation
		$this->active_nav_primary([
			'level_0' => 'master_setup',
			'level_1' => 'general',
			'level_2' => $this->router->class
		]);

		// Load Model
		$this->load->model('region_model');

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
		$records = $this->region_model->get_all();
		$this->template->partial(
							'content_header',
							'templates/_common/_content_header',
							[
								'content_header' => 'Manage Regions',
								'breadcrumbs' => ['Master Setup' => NULL, 'Regions' => NULL]
						])
						->partial('content', 'setup/regions/_index', compact('records'))
						->render($this->data);
	}

	// --------------------------------------------------------------------

	public function edit($id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->region_model->find($id);
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
        	if( $this->region_model->update($id, $data) )
        	{
        		$status = 'success';
				$message = 'Successfully Updated.';
				$record = $this->region_model->find($id);
        	}
			else
			{
				$status = 'error';
				$message = 'Validation Error.';
			}


			$row = $status === 'success'
						? $this->load->view('setup/regions/_single_row', compact('record'), TRUE)
						: '';

			$this->template->json([
				'status' 		=> $status,
				'message' 		=> $message,
				'reloadForm' 	=> $status === 'error',
				'hideBootbox' 	=> $status === 'success',
				'updateSection' => $status === 'success',
				'updateSectionData'	=> $status === 'success'
										? 	[
												'box' => '#_region-row-' . $record->id,
												'html' 		=> $row,
												// Jquery Method 	html|replaceWith|append|prepend etc.
												'method' 	=> 'replaceWith'
											]
										: NULL,
				'form' 	  		=> $status === 'error'
									? 	$this->load->view('setup/regions/_form',
											[
												'form_elements' => $this->region_model->validation_rules,
												'record' 		=> $record
											], TRUE)
									: 	null

			]);
		}


		$form = $this->load->view('setup/regions/_form',
			[
				'form_elements' => $this->region_model->validation_rules,
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
        $this->region_model->clear_cache();
        redirect($this->router->class);
    }
}