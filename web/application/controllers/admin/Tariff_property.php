<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Property Tariff Controller
 *
 * This controller falls under "Application Settings" category.
 *
 * @category 	Application Settings
 * @sub-category Account
 */

// --------------------------------------------------------------------

class Tariff_property extends MY_Controller
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
        $this->data['site_title'] = 'Application Settings | Property Tariff';

        // Setup Navigation
		$this->active_nav_primary([
			'level_0' => 'application_setup',
			'level_1' => 'portfolio',
			'level_2' => 'tariff',
			'level_3' => $this->router->class
		]);


		// Load Model
		$this->load->model('tariff_property_model');

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
		// this will generate cache name: mc_master_departments_all
		$records = $this->tariff_property_model->get_all();

		$this->template->partial(
							'content_header',
							'templates/_common/_content_header',
							[
								'content_header' => 'Manage Property Tariff',
								'breadcrumbs' => ['Application Settings' => NULL, 'Property Tariff' => NULL]
						])
						->partial('content', 'setup/tariff/property/_index', compact('records'))
						->render($this->data);
	}

	// --------------------------------------------------------------------

	/**
	 * Edit a Role
	 *
	 *
	 * @param integer $id
	 * @return void
	 */
	public function edit($id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->tariff_property_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Form Submitted? Save the data
		$rules = $this->tariff_property_model->validation_rules;
		$json_data = $this->_save('edit', $record, $rules);


		// No form Submitted?
		$json_data['form'] = $this->load->view('setup/tariff/property/_form',
			[
				'form_elements' => $rules,
				'record' 		=> $record
			], TRUE);

		// Return HTML
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Edit a Risks
	 *
	 *
	 * @param integer $id
	 * @return void
	 */
	public function risks($id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->tariff_property_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Form Submitted? Save the data
		$rules = $this->tariff_property_model->v_rules_risks();
		$json_data = $this->_save('risks', $record, $rules);


		// No form Submitted?
		$json_data['form'] = $this->load->view('setup/tariff/property/_form_risks',
			[
				'form_elements' => $rules,
				'record' 		=> $record,
				'risks' 		=> json_decode($record->risks ?? NULL)
			], TRUE);

		// Return HTML
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Edit a Tariff
	 *
	 *
	 * @param integer $id
	 * @return void
	 */
	public function tariff($id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->tariff_property_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		$this->load->model('portfolio_model');

		// Form Submitted? Save the data
		$rules = $this->tariff_property_model->v_rules_tariff();
		$json_data = $this->_save('tariff', $record, $rules);


		// No form Submitted?
		$json_data['form'] = $this->load->view('setup/tariff/property/_form_tariff',
			[
				'form_elements' => $rules,
				'record' 		=> $record,
				'tariff' 		=> json_decode($record->tariff ?? NULL),
				'portfolios' 	=> $this->portfolio_model->dropdown_children(IQB_MASTER_PORTFOLIO_PROPERTY_ID)
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
	private function _save($action, $record = NULL, $rules)
	{
		// Valid action?
		if( !in_array($action, array('add', 'edit', 'risks', 'tariff')))
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
			$this->form_validation->set_rules($rules);
			if( $this->form_validation->run() === TRUE )
			{
				$data = $this->input->post();

				$done = FALSE;

				switch ($action)
				{
					case 'add':
						# code...
						break;

					case 'edit':
						$done = $this->tariff_property_model->update($record->id, $data, TRUE);
						break;

					case 'risks':
						$done = $this->_save_risks($record->id, $data);
						break;

					case 'tariff':
						$done = $this->_save_tariff($record->id, $data);
						break;

					default:
						# code...
						break;
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
				$this->template->json([
					'status' => 'error',
					'title' => 'Validation Error!',
					'message' => validation_errors()
				]);
			}

			// Success HTML
			$success_html = '';
			if($status === 'success' )
			{
				if($action === 'add')
				{
					$records = $this->tariff_property_model->get_all();
					$success_html = $this->load->view('setup/tariff/property/_list', ['records' => $records], TRUE);
				}
				else
				{
					// Get Updated Record
					$record = $this->tariff_property_model->find($record->id);
					$success_html = $this->load->view('setup/tariff/property/_single_row', ['record' => $record], TRUE);
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
										: NULL

			];
		}

		return $return_data;
	}

	// --------------------------------------------------------------------

	private function _save_risks($id, $post_data)
	{
		// Format Risks
		$rules = $this->tariff_property_model->v_rules_risks();
		$count = count($post_data['risks']['code']);

		$risk_data = [];
		for($i = 0; $i < $count; $i++)
		{
			$risk = [];
			foreach($rules as $single)
			{
				$key = $single['_key'];
				$risk[$key] = $post_data['risks'][$key][$i];
			}
			$risk_data[] = $risk;
		}

		$data = ['risks' => json_encode($risk_data)];

		return $this->tariff_property_model->update($id, $data, TRUE);
	}

	// --------------------------------------------------------------------

	private function _save_tariff($id, $post_data)
	{
		// Format Risks
		$rules = $this->tariff_property_model->v_rules_tariff();
		$count = count($post_data['tariff']['portfolio_id']);

		$risk_data = [];
		for($i = 0; $i < $count; $i++)
		{
			$risk = [];
			foreach($rules as $single)
			{
				$key = $single['_key'];
				$risk[$key] = $post_data['tariff'][$key][$i];
			}
			$risk_data[] = $risk;
		}

		$data = ['tariff' => json_encode($risk_data)];

		return $this->tariff_property_model->update($id, $data, TRUE);
	}

	// --------------------------------------------------------------------

	/**
     * Callback - Duplicate Risk Code
     *
     * @param string $code
     * @param integer|null $id
     * @return bool
     */
    public function _cb_risk_duplicate($code){

    	$risks = $this->input->post('risks');
    	$codes = $risks['code'];
    	$count = count($risks['code']);
    	$unique_codes = [];

    	for($i=0; $i<$count; $i++)
    	{
    		$unique_codes[] = $codes[$i];
    	}
    	$unique_codes = array_unique($unique_codes);
    	$new_count = count($unique_codes);
        if( $count != $new_count )
        {
            $this->form_validation->set_message('_cb_risk_duplicate', 'The %s must be unique.');
            return FALSE;
        }
        return TRUE;
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

        if( $this->tariff_property_model->check_duplicate(['code' => $code], $id))
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
        $this->tariff_property_model->clear_cache();
        redirect($this->_url_base);
    }

    // --------------------------------------------------------------------

}