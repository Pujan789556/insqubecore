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

class States extends MY_Controller
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
        $this->data['site_title'] = 'Application Settings | States';

        // Setup Navigation
		$this->active_nav_primary([
			'level_0' => 'application_setup',
			'level_1' => 'general',
			'level_2' => $this->router->class
		]);

		// Load Model
		$this->load->model('state_model');

		// URL Base
		$this->_url_base 		 = 'admin/' . $this->router->class;
		$this->data['_url_base'] = $this->_url_base; // for view to access
	}

	// --------------------------------------------------------------------
	// DATA EXPLORE METHODS
	// --------------------------------------------------------------------

	/**
	 * Default Method
	 *
	 * Render the List
	 *
	 * @return type
	 */
	function index()
	{
		$this->page();
	}

	// --------------------------------------------------------------------

	/**
	 * Paginate Data List
	 *
	 * @param char 	$layout
	 * @param integer $next_id
	 * @return void
	 */
	function page( $layout='f', $next_id = 0 )
	{
		// If request is coming from refresh method, reset nextid
		$next_id 		= (int)$next_id;
		$next_url_base 	= $this->_url_base . '/page/r/';

		// DOM Data
		$dom_data = [
			'DOM_DataListBoxId' 	=> '_iqb-data-list-box-states', 		// List box ID
			'DOM_FilterFormId'		=> '_iqb-filter-form-states' 			// Filter Form ID
		];

		/**
		 * Get Search Result
		 */
		$data = $this->_get_filter_data( $next_url_base, $next_id );
		$data = array_merge($data, $dom_data);

		/**
		 * Find View
		 */
		if($layout === 'f') // Full Layout
		{
			$view = 'setup/states/_index';

			$data = array_merge($data, [
				'filters' 		=> $this->_get_filter_elements(),
				'filter_url' 	=> site_url($this->_url_base . '/page/l/0')
			]);
		}
		else if($layout === 'l')
		{
			$view = 'setup/states/_list';
		}
		else
		{
			$view = 'setup/states/_rows';
		}

		if ( $this->input->is_ajax_request() )
		{

			$html = $this->load->view($view, $data, TRUE);
			$ajax_data = [
				'status' => 'success',
				'html'   => $html
			];
			$this->template->json($ajax_data);
		}

		$this->template
						->set_layout('layout-advanced-filters')
						->partial(
							'content_header',
							'setup/states/_index_header',
							['content_header' => 'Manage States'] + $dom_data)
						->partial('content', 'setup/states/_index', $data)
						->render($this->data);
	}

	// --------------------------------------------------------------------

		private function _get_filter_elements()
		{
			$country_dropdown = $this->country_model->dropdown('id');
			$filters = [
				[
	                'field' => 'filter_country_id',
	                'label' => 'Country',
	                'rules' => 'trim|integer|max_length[3]',
	                '_type'     => 'dropdown',
	                '_data' => IQB_BLANK_SELECT + $country_dropdown,
	                '_required' => false
	            ],
	            [
	                'field' => 'filter_code',
	                'label' => 'State Code',
	                'rules' => 'trim|alpha_numeric|max_length[3]',
	                '_type'     => 'text',
	                '_required' => false
	            ],
	            [
					'field' => 'filter_keywords',
			        'label' => 'State Name',
			        'rules' => 'trim|max_length[100]',
	                '_type'     => 'text',
	                '_required' => false
				],
			];
			return $filters;
		}

	// --------------------------------------------------------------------

		private function _get_filter_data( $next_url_base, $next_id = 0)
		{
			$params = [];

			if( $this->input->post() )
			{
				$rules = $this->_get_filter_elements();
				$this->form_validation->set_rules($rules);
				if( $this->form_validation->run() )
				{
					$params = [
						'country_id' 		=> $this->input->post('filter_country_id') ?? NULL,
						'code' 				=> $this->input->post('filter_code') ?? NULL,
						'keywords' 			=> $this->input->post('filter_keywords') ?? ''
					];
				}
				else
				{
					$data = [
						'status' 	=> 'error',
						'message' 	=> validation_errors()
					];

					$this->template->json($data);
				}
			}

			$next_id = (int)$next_id;
			if( $next_id )
			{
				$params['next_id'] = $next_id;
			}

			/**
			 * Get Search Result
			 */
			$records = $this->state_model->rows($params);
			$records = $records ? $records : [];
			$total = count($records);

			/**
			 * Grab Next ID or Reset It
			 */
			if($total == $this->settings->per_page+1)
			{
				$next_id = $records[$total-1]->id;
				unset($records[$total-1]); // remove last record
			}
			else
			{
				$next_id = NULL;
			}

			$data = [
				'records' 			=> $records,
				'next_id'  => $next_id,
				'next_url' => $next_id ? site_url( rtrim($next_url_base, '/\\') . '/' . $next_id ) : NULL
			];
			return $data;
		}

	// --------------------------------------------------------------------

	/**
	 * Refresh The Module
	 *
	 * Simply reload the first page
	 *
	 * @return type
	 */
	function refresh()
	{
		$this->page('l');
	}

	// --------------------------------------------------------------------

	/**
	 * Filter the Data
	 *
	 * @return type
	 */
	function filter()
	{
		$this->page('l');
	}

	// --------------------------------------------------------------------

    /**
     * Flush Cache Data
     *
     * @return void
     */
    public function flush()
    {
        $this->state_model->clear_cache();
        redirect($this->_url_base);
    }


	// --------------------------------------------------------------------
	// CRUD - FUNCTIONS
	// --------------------------------------------------------------------

	/**
	 * Add a new Record
	 *
	 * @return void
	 */
	public function add()
	{
		$record = NULL;

		// Form Submitted? Save the data
		$this->_save('add', $record);

		// No form Submitted?
		$json_data['form'] = $this->load->view('setup/states/_form',
			[
				'form_elements' => $this->state_model->validation_rules,
				'record' 		=> $record
			], TRUE);

		// Return HTML
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Edit a Recrod
	 *
	 *
	 * @param integer $id
	 * @return void
	 */
	public function edit($id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->state_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Form Submitted? Save the data
		$this->_save('edit', $record);


		// No form Submitted?
		$json_data['form'] = $this->load->view('setup/states/_form',
			[
				'form_elements' => $this->state_model->validation_rules,
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
		// Valid action? Valid from_widget
		if( !in_array($action, array('add', 'edit'))  )
		{
			return $this->template->json([
				'status' => 'error',
				'message' => 'Invalid action!'
			], 404);
		}

		/**
		 * Form Submitted?
		 */
		if( $this->input->post() )
		{
			$done 	= FALSE;
			$rules 	= $this->state_model->validation_rules;
            $this->form_validation->set_rules($rules);
			if($this->form_validation->run() === TRUE )
        	{
        		$data = $this->input->post();

        		$post_data = [
        			'country_id' => $data['country_id'],
        			'code' => $data['code'],
        			'name_en' => $data['name_en'],
        			'name_np' => $data['name_np'],
        		];


        		// Insert or Update?
				if($action === 'add')
				{
					$done = $this->state_model->insert($post_data, TRUE); // No Validation on Model
				}
				else
				{
					// Now Update Data
					$done = $this->state_model->update($record->id, $post_data, TRUE);
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

        	if($status === 'success' )
			{
				$ajax_data = [
					'message' => $message,
					'status'  => $status,
					'updateSection' => true,
					'hideBootbox' => true
				];

				$record 			= $this->state_model->get( $action === 'add' ? $done : $record->id );
				$single_row 		=  'setup/states/_single_row';

				$html = $this->load->view('setup/states/_single_row', ['record' => $record], TRUE);
				$ajax_data['updateSectionData'] = [
					'box' 		=> $action === 'add' ? '#search-result-states' : '#_data-row-' . $record->id,
					'method' 	=> $action === 'add' ? 'prepend' : 'replaceWith',
					'html'		=> $html
				];

				return $this->template->json($ajax_data);
			}

			// Form
			return $this->template->json([
				'status' 		=> $status,
				'message' 		=> $message,
				'reloadForm' 	=> true,
				'form' 			=> $this->load->view('setup/states/_form',
									[
										'form_elements' => $this->state_model->validation_rules,
										'record' 		=> $record
									], TRUE)
			]);
		}

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

    	$code 			= strtoupper( $code ? $code : $this->input->post('code') );
    	$country_id     = (int)$this->input->post('country_id');
    	$id   = $id ? (int)$id : (int)$this->input->post('id');

        if( $this->state_model->check_duplicate(['code' => $code, 'country_id' => $country_id], $id))
        {
            $this->form_validation->set_message('check_duplicate', 'The %s already exists for selected Country.');
            return FALSE;
        }
        return TRUE;
    }

    // --------------------------------------------------------------------

	/**
	 * Delete a Agent
	 * @param integer $id
	 * @return json
	 */
	public function delete($id)
	{
		// !!! NOTE !!! DO NOT ALLOW TO DELETE FOR NOW
		return $this->template->json([
			'status' 	=> 'error',
			'message' 	=> 'You cannot delete the state records.'
		]);

		// Valid Record ?
		$id = (int)$id;
		$record = $this->state_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		$data = [
			'status' 	=> 'error',
			'message' 	=> 'You cannot delete the default records.'
		];
		/**
		 * Safe to Delete?
		 */
		if( !safe_to_delete( 'State_model', $id ) )
		{
			return $this->template->json($data);
		}

		$done = $this->state_model->delete($record->id);

		if($done)
		{
			$data = [
				'status' 	=> 'success',
				'message' 	=> 'Successfully deleted!',
				'removeRow' => true,
				'rowId'		=> '#_data-row-'.$record->id
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
}