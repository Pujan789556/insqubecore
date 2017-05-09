<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Account Parties(Regular) Controller
 *
 * @category 	Accounting
 */

// --------------------------------------------------------------------

class Ac_parties extends MY_Controller
{
	function __construct()
	{
		parent::__construct();

		// Form Validation
		$this->load->library('Form_validation');

		// Set Template for this controller
        $this->template->set_template('dashboard');

        // Basic Data
        $this->data['site_title'] = 'Accounting Parties';

        // Setup Navigation
		$this->active_nav_primary([
			'level_0' => 'accounting',
			'level_1' => $this->router->class
		]);

		// Load Model
		$this->load->model('ac_party_model');
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


	// --------------------------------------------------------------------
	// SEARCH/FILTER - FUNCTIONS
	// --------------------------------------------------------------------


	/**
	 * Paginate Data List
	 *
	 * @param integer $next_id
	 * @return void
	 */

	// $layout, $from_widget, $next_id, $ajax_extra
	function page( $layout='f', $from_widget='n', $next_id = 0, $ajax_extra = [] )
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_admin() && !$this->dx_auth->is_authorized('ac_parties', 'explore.ac_party') )
		{
			$this->dx_auth->deny_access();
		}


		// If request is coming from refresh method, reset nextid
		$next_id = (int)$next_id;
		$next_url_base = 'ac_parties/page/r/'.$from_widget;

		// DOM Data
		$dom_data = [
			'DOM_DataListBoxId' 		=> '_iqb-data-list-box-ac_party', 		// List box ID
			'DOM_FilterFormId'		=> '_iqb-filter-form-ac_party' 			// Filter Form ID
		];

		/**
		 * Get Search Result
		 */
		$data = $this->_get_filter_data( $next_url_base, $next_id );
		$data = array_merge($data, $dom_data);
		// echo $this->db->last_query();exit;
		/**
		 * Widget Specific Data
		 */
		$data['_flag__show_widget_row'] = $from_widget === 'y';


		/**
		 * Find View
		 */
		if($layout === 'f') // Full Layout
		{
			$view = $from_widget === 'y' ? 'accounting/parties/_find_widget' : 'accounting/parties/_index';

			$data = array_merge($data, [
				'filters' 		=> $this->_get_filter_elements(),
				'filter_url' 	=> site_url('ac_parties/page/l/' . $from_widget )
			]);
		}
		else if($layout === 'l')
		{
			$view = 'accounting/parties/_list';
		}
		else
		{
			$view = 'accounting/parties/_rows';
		}

		if ( $this->input->is_ajax_request() )
		{

			$html = $this->load->view($view, $data, TRUE);
			$ajax_data = [
				'status' => 'success',
				'html'   => $html
			];

			if( !empty($ajax_extra))
			{
				$ajax_data = array_merge($ajax_data, $ajax_extra);
			}
			$this->template->json($ajax_data);
		}

		$this->template
						->set_layout('layout-advanced-filters')
						->partial(
							'content_header',
							'accounting/parties/_index_header',
							['content_header' => 'Manage Accounting Parties'] + $dom_data)
						->partial('content', 'accounting/parties/_index', $data)
						->partial('dynamic_js', 'accounting/parties/_party_js')
						->render($this->data);
	}


		private function _get_filter_elements()
		{
			$select = ['' => 'Select ...'];
			$filters = [
				[
	                'field' => 'filter_type',
	                'label' => 'Party Type',
	                'rules' => 'trim|alpha|exact_length[1]|in_list[I,C]',
	                '_id'       => 'filter-type',
	                '_type'     => 'dropdown',
	                '_data'     => [ '' => 'Select...', 'I' => 'Individual', 'C' => 'Company'],
	            ],
	            [
		            'field' => 'filter_company_reg_no',
		            'label' => 'Company Reg Number',
		            'rules' => 'trim|max_length[20]',
		            '_type'     => 'text',
		            '_extra_attributes' => ['data-hideonload' => 'yes'],
		            '_required' => false
		        ],
		        [
		            'field' => 'filter_citizenship_no',
		            'label' => 'Citizenship Number',
		            'rules' => 'trim|max_length[20]',
		            '_type'     => 'text',
		            '_extra_attributes' => ['data-hideonload' =>'yes'],
		            '_required' => false
		        ],
		        [
		            'field' => 'filter_passport_no',
		            'label' => 'Passport Number',
		            'rules' => 'trim|alpha_dash|max_length[20]',
		            '_type'     => 'text',
		            '_extra_attributes' => ['data-hideonload' =>'yes'],
		            '_required' => false
		        ],
	            [
					'field' => 'filter_keywords',
			        'label' => 'Keywords <i class="fa fa-info-circle"></i>',
			        'rules' => 'trim|max_length[80]',
	                '_type'     => 'text',
	                '_label_extra' => 'data-toggle="tooltip" data-title="Customer Name, PAN, Citizenship, Passport etc..."'
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
						'company_reg_no' 	=> $this->input->post('filter_company_reg_no') ?? NULL,
						'citizenship_no' 	=> $this->input->post('filter_citizenship_no') ?? NULL,
						'passport_no' 		=> $this->input->post('filter_passport_no') ?? NULL,
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
			$records = $this->ac_party_model->rows($params);
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
				'records' => $records,
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
		$this->page('l', 'n');
	}

	// --------------------------------------------------------------------

	/**
	 * Filter the Data
	 *
	 * @return type
	 */
	function filter()
	{
		$this->page('l', 'n');
	}

	// --------------------------------------------------------------------
	// CRUD - FUNCTIONS
	// --------------------------------------------------------------------

	/**
	 * Edit a Recrod
	 *
	 *
	 * @param integer $id
	 * @return void
	 */
	public function edit($id, $from_widget = 'n')
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_admin() && !$this->dx_auth->is_authorized('ac_parties', 'edit.ac_party') )
		{
			$this->dx_auth->deny_access();
		}

		// Valid Record ?
		$id = (int)$id;
		$record = $this->ac_party_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Form Submitted? Save the data
		$json_data = $this->_save('edit', $record, $from_widget);


		// No form Submitted?
		$json_data['form'] = $this->load->view('accounting/parties/_form_box',
			[
				'form_elements' => $this->ac_party_model->validation_rules,
				'record' 		=> $record
			], TRUE);

		// Return HTML
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Add a new Record
	 *
	 * @return void
	 */
	public function add( $from_widget='n' )
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_admin() && !$this->dx_auth->is_authorized('ac_parties', 'add.ac_party') )
		{
			$this->dx_auth->deny_access();
		}

		$record = NULL;

		// Form Submitted? Save the data
		$json_data = $this->_save('add', NULL, $from_widget);


		// No form Submitted?
		$json_data['form'] = $this->load->view('accounting/parties/_form_box',
			[
				'form_elements' => $this->ac_party_model->validation_rules,
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
	 * @param char 	$from_widget
	 * @return array
	 */
	private function _save($action, $record = NULL, $from_widget='n')
	{

		// Valid action?
		if( !in_array($action, array('add', 'edit')))
		{
			return [
				'status' => 'error',
				'message' => 'Invalid action!'
			];
		}

		// Valid "from" ?
		if( !in_array($from_widget, array('y', 'n')))
		{
			return [
				'status' => 'error',
				'message' => 'Invalid action!'
			];
		}

		/**
		 * Form Submitted?
		 */
		$return_data = [];

		if( $this->input->post() )
		{
			$done = FALSE;

			$rules = array_merge($this->ac_party_model->validation_rules, get_contact_form_validation_rules());
            $this->form_validation->set_rules($rules);
			if($this->form_validation->run() === TRUE )
        	{
            	$data = $this->input->post();

        		// Insert or Update?
				if($action === 'add')
				{
					$done = $this->ac_party_model->insert($data, TRUE); // No Validation on Model

					// Activity Log
					$done ? $this->ac_party_model->log_activity($done, 'C'): '';
				}
				else
				{
					// Now Update Data
					$done = $this->ac_party_model->update($record->id, $data, TRUE) && $this->ac_party_model->log_activity($record->id, 'E');
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
			$return_extra = [];
			if($status === 'success' )
			{
				$ajax_data = [
					'message' => $message,
					'status'  => $status,
					'updateSection' => true,
					'hideBootbox' => true
				];

				if($action === 'add')
				{
					$record = $this->ac_party_model->find($done);
					$single_row = $from_widget === 'y' ? 'accounting/parties/_single_row_widget' : 'accounting/parties/_single_row';
					$html = $this->load->view($single_row, ['record' => $record], TRUE);

					$ajax_data['updateSectionData'] = [
						'box' 		=> '#search-result-ac_party',
						'method' 	=> 'prepend',
						'html'		=> $html
					];
				}
				else
				{
					/**
					 * Widget or Row?
					 */
					$record = $this->ac_party_model->find($record->id);
					$view 	= $from_widget === 'n' ? 'accounting/parties/_single_row' : 'accounting/parties/snippets/_widget_profile';
					$html 	= $this->load->view($view , ['record' => $record], TRUE);

					$ajax_data['updateSectionData'] = [
						'box' 		=> $from_widget === 'n' ? '#_data-row-ac_party-' . $record->id : '#iqb-widget-ac_party-profile',
						'method' 	=> 'replaceWith',
						'html'		=> $html
					];
				}
				return $this->template->json($ajax_data);
			}

			// Form
			return $this->template->json([
				'status' 		=> $status,
				'message' 		=> $message,
				'reloadForm' 	=> true,
				'form' 			=> $this->load->view('accounting/parties/_form',
									[
										'form_elements' => $this->ac_party_model->validation_rules,
										'record' 		=> $record
									], TRUE)
			]);
		}

		return $return_data;
	}

	// --------------------------------------------------------------------

	/**
	 * Delete a Customer
	 * @param integer $id
	 * @return json
	 */
	public function delete($id)
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_admin() && !$this->dx_auth->is_authorized('ac_parties', 'delete.ac_party') )
		{
			$this->dx_auth->deny_access();
		}

		// Valid Record ?
		$id = (int)$id;
		$record = $this->ac_party_model->find($id);
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
		if( !safe_to_delete( 'Ac_party_model', $id ) )
		{
			return $this->template->json($data);
		}

		$done = $this->ac_party_model->delete($record->id);

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


	// --------------------------------------------------------------------
	// DETAILS EXPLORATION
	// --------------------------------------------------------------------

    /**
     * View Customer Details
     *
     * @param integer $id
     * @return void
     */
    public function details($id)
    {
    	/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_admin() && !$this->dx_auth->is_authorized('ac_parties', 'explore.ac_party') )
		{
			$this->dx_auth->deny_access();
		}

    	$id = (int)$id;
		$record = $this->ac_party_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Helpers
		$this->load->helper('object');

		// Prepare Data
		$this->load->model('object_model');
		$data = [
			'record' 		=> $record,
			'objects' 		=> $this->object_model->get_by_customer($record->id)
		];



		$this->data['site_title'] = 'Party Details Details | ' . $record->full_name;
		$this->template->partial(
							'content_header',
							'templates/_common/_content_header',
							[
								'content_header' => 'Party Details Details <small>' . $record->full_name . '</small>',
								'breadcrumbs' => ['Accounting Parties' => 'ac_parties', 'Details' => NULL
							]
						])
						->partial('content', 'accounting/parties/_details', $data)
						->render($this->data);

    }
}