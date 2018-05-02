<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Fy_quarters Controller
 *
 * This controller falls under "Master Setup" category.
 *
 * @category 	Master Setup
 */

// --------------------------------------------------------------------

class Fy_quarters extends MY_Controller
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
        $this->data['site_title'] = 'Master Setup | Fiscal Year Quarters';

        // Setup Navigation
		$this->active_nav_primary([
			'level_0' => 'master_setup',
			'level_1' => 'general',
			'level_2' => $this->router->class
		]);

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
		$records = $this->fy_quarter_model->get_all();
		$records = $records ? $records : [];
		$this->template->partial(
							'content_header',
							'templates/_common/_content_header',
							[
								'content_header' => 'Manage Fiscal Year Quarters',
								'breadcrumbs' => ['Master Setup' => NULL, 'Fiscal Year Quarters' => NULL]
						])
						->partial('content', 'setup/fy_quarters/_index', compact('records'))
						->render($this->data);
	}

	// --------------------------------------------------------------------

	/**
	 * Add a new Role
	 *
	 * @return void
	 */
	public function add()
	{
		$record = NULL;

		// Form Submitted? Save the data
		$json_data = $this->_save('add');


		// No form Submitted?
		$json_data['form'] = $this->load->view('setup/fy_quarters/_form',
			[
				'form_elements' => $this->fy_quarter_model->validation_rules,
				'record' 		=> $record
			], TRUE);

		// Return HTML
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Edit Record
	 *
	 *
	 * @param integer $id
	 * @return void
	 */
	public function edit($id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->fy_quarter_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Form Submitted? Save the data
		$json_data = $this->_save('edit', $record);


		// No form Submitted?
		$json_data['form'] = $this->load->view('setup/fy_quarters/_form',
			[
				'form_elements' => $this->fy_quarter_model->validation_rules,
				'record' 		=> $record
			], TRUE);

		// Return HTML
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Save a Record
	 *
	 * @param string $action [add|edit]
	 * @param object|null $record Record Object or NULL
	 * @return array
	 */
	private function _save($action, $record = NULL)
	{
		// Valid action?
		if( !in_array($action, array('add', 'edit')))
		{
			$this->template->json([
				'status' => 'error',
				'message' => 'Invalid action!'
			]);
		}

		/**
		 * Form Submitted?
		 */
		$return_data = [];

		if( $this->input->post() )
		{
			$done = FALSE;

			$rules = $this->fy_quarter_model->validation_rules;
			$this->form_validation->set_rules($rules);
			if( $this->form_validation->run() === TRUE )
			{
				$data = $this->input->post();

				// Insert or Update?
				if($action === 'add')
				{
					// @NOTE: Activity Log will be automatically inserted
					$done = $this->fy_quarter_model->insert($data, TRUE); // No Validation on Model
				}
				else
				{
					// Now Update Data
					$done = $this->fy_quarter_model->update($record->id, $data, TRUE);
				}

				if(!$done)
				{
					$status = 'error';
					$message = 'Could not update.';
				}
				else
				{
					$status = 'success';
					$message = 'Successfully Updated.';
				}
			}
			else
			{
				$status = 'error';
				$message = 'Validation Error.';
			}

			// Success HTML
			$success_html = '';
			if($status === 'success' )
			{
				if($action === 'add')
				{
					$records = $this->fy_quarter_model->get_all();
					$success_html = $this->load->view('setup/fy_quarters/_list', ['records' => $records], TRUE);
				}
				else
				{
					// Get Updated Record
					$record = $this->fy_quarter_model->get($record->id);
					$success_html = $this->load->view('setup/fy_quarters/_single_row', ['record' => $record], TRUE);
				}
			}

			$return_data = [
				'status' 		=> $status,
				'message' 		=> $message,
				'reloadForm' 	=> $status === 'error',
				'hideBootbox' 	=> $status === 'success',
				'updateSection' => $status === 'success',
				'updateSectionData'	=> $status === 'success'
										? 	[
												'box' 	=> $action === 'add'
															? '#iqb-data-list'
															: '#_data-row-' . $record->id,
												'html' 	=> $success_html,

												//
												// How to Work with success html?
												// Jquery Method 	html|replaceWith|append|prepend etc.
												//
												'method' 	=> $action === 'add' ? 'html' : 'replaceWith'
											]
										: NULL,
				'form' 	  		=> $status === 'error'
									? 	$this->load->view('setup/fy_quarters/_form',
											[
												'form_elements' => $this->fy_quarter_model->validation_rules,
												'record' 		=> $record
											], TRUE)
									: 	null

			];
		}

		return $return_data;
	}

	// --------------------------------------------------------------------

	/**
	 * Validaton Callback - Valid Quarter?
	 */
	function _cb_valid_quarter($quarter)
	{
		$quarter 		= $quarter ? (int)$quarter : (int)$this->input->post('quarter');
    	$id   			= (int)$this->input->post('id');
    	$fiscal_yr_id 	= (int)$this->input->post('fiscal_yr_id');

    	$where = [
    		'fiscal_yr_id' 	=> $fiscal_yr_id,
    		'quarter' 		=> $quarter
    	];
        if( $this->fy_quarter_model->check_duplicate($where, $id) )
        {
            $this->form_validation->set_message('_cb_valid_quarter', 'The %s already exists for selected fiscal year.');
            return FALSE;
        }
        return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Validaton Callback - Valid Dates?
	 */
	function _cb_valid_dates($ends_at)
	{
		$ends_at 		= $ends_at ? $ends_at : $this->input->post('ends_at');
    	$id   			= (int)$this->input->post('id');
    	$starts_at 		= $this->input->post('starts_at');
    	$fiscal_yr_id 	= (int)$this->input->post('fiscal_yr_id');

    	/**
    	 * Start < End
    	 */
    	if(strtotime($starts_at) >= strtotime($ends_at))
    	{
    		$this->form_validation->set_message('_cb_valid_dates', 'The Start Date must be less than End Date.');
            return FALSE;
    	}

    	/**
    	 * Fall Under Fiscal Year Rnage?
    	 */
    	$fy_record = $this->fiscal_year_model->get($fiscal_yr_id);
    	if(
    		$fy_record
    		&&
    		(
    			strtotime($starts_at) < strtotime($fy_record->starts_at_en)  || strtotime($starts_at) > strtotime($fy_record->ends_at_en)
	    			||
	    		strtotime($ends_at) < strtotime($fy_record->starts_at_en)  || strtotime($ends_at) > strtotime($fy_record->ends_at_en)
			)
		){

    		$this->form_validation->set_message('_cb_valid_dates', 'The Start/End Dates must fall under selected Fiscal Year Range.');
            return FALSE;
    	}

    	$where = [
    		'starts_at' 	=> $starts_at,
    		'ends_at' 		=> $ends_at
    	];
        if( $this->fy_quarter_model->check_duplicate($where, $id))
        {
            $this->form_validation->set_message('_cb_valid_dates', 'The Start (and/or) End Date already exists.');
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
        $this->fy_quarter_model->clear_cache();
        redirect($this->router->class);
    }


}