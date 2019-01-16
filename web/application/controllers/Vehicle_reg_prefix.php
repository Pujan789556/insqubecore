<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Vehicle_reg_prefix Controller
 *
 * This controller falls under "Master Setup" category.
 *
 * @category 	Master Setup
 */

// --------------------------------------------------------------------

class Vehicle_reg_prefix extends MY_Controller
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
        $this->data['site_title'] = 'Master Setup | Vehicle Registration Prefixes';

        // Setup Navigation
		$this->active_nav_primary([
			'level_0' => 'application_setup',
			'level_1' => 'general',
			'level_2' => $this->router->class
		]);

		// Load Model
		$this->load->model('vehicle_reg_prefix_model');
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
		$this->page();
	}

	/**
	 * Paginate Data List
	 *
	 * @param integer $next_id
	 * @return void
	 */
	function page( $layout='f', $next_id = 0 )
	{
		// If request is coming from refresh method, reset nextid
		$next_id 		= (int)$next_id;
		$next_url_base 	= $this->router->class . '/page/r/';

		// DOM Data
		$dom_data = [
			'DOM_DataListBoxId' 	=> '_iqb-data-list-box-vehicle_reg_prefix', 		// List box ID
			'DOM_FilterFormId'		=> '_iqb-filter-form-vehicle_reg_prefix' 			// Filter Form ID
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
			$view = 'setup/vehicle_reg_prefix/_index';

			$data = array_merge($data, [
				'filters' 		=> $this->_get_filter_elements(),
				'filter_url' 	=> site_url($this->router->class . '/page/l/')
			]);
		}
		else if($layout === 'l')
		{
			$view = 'setup/vehicle_reg_prefix/_list';
		}
		else
		{
			$view = 'setup/vehicle_reg_prefix/_rows';
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
							'setup/vehicle_reg_prefix/_index_header',
							['content_header' => 'Manage Vehicle Registration Prefixes'] + $dom_data)
						->partial('content', 'setup/vehicle_reg_prefix/_index', $data)
						->render($this->data);
	}

		private function _get_filter_elements()
		{
			$select = ['' => 'Select ...'];
			$filters = [
				[
		            'field' => 'filter_type',
		            'label' => 'Prefix Type',
		            'rules' => 'trim|integer|exact_length[1]|in_list[1,2,3]',
		            '_type'     => 'dropdown',
		            '_data'     => [ '' => 'Select...', '1' => 'OLD', '2' => 'New 4 Wheeler', '3' => 'New 2 Wheeler'],
		            '_required' => false
		        ],
	            [
					'field' => 'filter_keywords',
			        'label' => 'Name',
			        'rules' => 'trim|max_length[80]',
	                '_type'     => 'text',
	                '_required' => false
				],
			];
			return $filters;
		}

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
						'type' 				=> $this->input->post('filter_type') ?? NULL,
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
			$records = $this->vehicle_reg_prefix_model->rows($params);
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
				'next_url' => $next_id ? site_url( rtrim($next_url_base, '/\\') . '/' . $next_id  ) : NULL
			];
			return $data;
		}

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
	 * Add a new Record
	 *
	 * @return void
	 */
	public function add()
	{
		$record 		= NULL;

		// Form Submitted? Save the data
		$json_data = $this->_save('add', $record);


		// No form Submitted?
		$json_data['form'] = $this->load->view('setup/vehicle_reg_prefix/_form',
			[
				'form_elements' 	=> $this->vehicle_reg_prefix_model->validation_rules,
				'record' 			=> $record
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
		$record = $this->vehicle_reg_prefix_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Form Submitted? Save the data
		$json_data = $this->_save('edit', $record);


		// No form Submitted?
		$json_data['form'] = $this->load->view('setup/vehicle_reg_prefix/_form',
			[
				'form_elements' 	=> $this->vehicle_reg_prefix_model->validation_rules,
				'record' 			=> $record,
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
		if( !in_array($action, array('add', 'edit')) )
		{
			return $this->template->json([
				'status' => 'error',
				'message' => 'Invalid action!'
			], 404);
		}

		/**
		 * Form Submitted?
		 */
		$return_data = [];

		if( $this->input->post() )
		{
			$done 	= FALSE;
			$rules 	= $this->vehicle_reg_prefix_model->validation_rules;
            $this->form_validation->set_rules($rules);
			if($this->form_validation->run() === TRUE )
        	{
        		$post_data = $this->input->post();

        		// Prepare Data
        		$data = [
        			'type' 		=> $post_data['type'],
        			'name_en' 	=> $post_data['name_en'],
        			'name_np' 	=> $post_data['name_np'],
        		];


        		// Insert or Update?
				if($action === 'add')
				{
					$done = $this->vehicle_reg_prefix_model->insert($data, true); // No Validation on Model
				}
				else
				{
					// Now Update Data
					$done = $this->vehicle_reg_prefix_model->update($record->id, $data, true);
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

				$record 			= $this->vehicle_reg_prefix_model->find( $action === 'add' ? $done : $record->id );
				$single_row 		=  'setup/vehicle_reg_prefix/_single_row';
				$html = $this->load->view($single_row, ['record' => $record], TRUE);
				$ajax_data['updateSectionData'] = [
					'box' 		=> $action === 'add' ? '#search-result-vehicle_reg_prefix' : '#_data-row-' . $record->id,
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
				'form' 			=> $this->load->view('setup/vehicle_reg_prefix/_form',
									[
										'form_elements' 	=> $this->vehicle_reg_prefix_model->validation_rules,
										'record' 			=> $record
									], TRUE)
			]);
		}

		return $return_data;
	}

	// --------------------------------------------------------------------

	/**
     * Check Duplicate Callback
     *
     * @param string $name_en
     * @param integer|null $id
     * @return bool
     */
    public function check_duplicate_en($name_en, $id=NULL){

    	$id   = $id ? (int)$id : (int)$this->input->post('id');

        if( $this->vehicle_reg_prefix_model->check_duplicate(['name_en' => $name_en], $id))
        {
            $this->form_validation->set_message('check_duplicate_en', 'The %s already exists.');
            return FALSE;
        }
        return TRUE;
    }

    // --------------------------------------------------------------------

	/**
     * Check Duplicate Callback
     *
     * @param string $name_np
     * @param integer|null $id
     * @return bool
     */
    public function check_duplicate_np($name_np, $id=NULL){

    	$id   = $id ? (int)$id : (int)$this->input->post('id');

        if( $this->vehicle_reg_prefix_model->check_duplicate(['name_np' => $name_np], $id))
        {
            $this->form_validation->set_message('check_duplicate_np', 'The %s already exists.');
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
		// Valid Record ?
		$id = (int)$id;
		$record = $this->vehicle_reg_prefix_model->find($id);
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
		if( !safe_to_delete( 'Vehicle_reg_prefix_model', $id ) )
		{
			return $this->template->json($data);
		}

		$done = $this->vehicle_reg_prefix_model->delete($record->id);
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