<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Policies Controller
 *
 * This controller falls under "Application Settings" category.
 *
 * @category 	Application Settings
 */

// --------------------------------------------------------------------

class Policies extends MY_Controller
{
	/**
	 * Files Upload Path - Data (Invoices)
	 */
	public static $data_upload_path = INSQUBE_DATA_ROOT . 'policies/';

	// --------------------------------------------------------------------

	function __construct()
	{
		parent::__construct();

		// Form Validation
		$this->load->library('Form_validation');

		// Set Template for this controller
        $this->template->set_template('dashboard');

        // Basic Data
        $this->data['site_title'] = 'Policies';

        // Setup Navigation
		$this->active_nav_primary([
			'level_0' => 'policies',
		]);

		// Load Model
		$this->load->model('policy_model');
		$this->load->model('object_model');
		$this->load->model('endorsement_model');

		// Policy Configuration/Helper
		$this->load->config('policy');
		$this->load->helper('policy');
		$this->load->helper('object');
	}

	// --------------------------------------------------------------------
	// SEARCH OPERATIONS
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
	function page( $layout='f', $next_id = 0,  $ajax_extra = [] )
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('policies', 'explore.policy') )
		{
			$this->dx_auth->deny_access();
		}


		// If request is coming from refresh method, reset nextid
		$next_id = (int)$next_id;

		$params = array();
		if( $next_id )
		{
			$params = ['next_id' => $next_id];
		}

		/**
		 * Extract Filter Elements
		 */
		$filter_data = $this->_get_filter_data( );
		if( $filter_data['status'] === 'success' )
		{
			$params = array_merge($params, $filter_data['data']);
		}

		$records 	= $this->policy_model->rows($params);
		$records 	= $records ? $records : [];
		$total 		= count($records);

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

		// DOM Data
		$dom_data = [
			'DOM_DataListBoxId' 		=> '_iqb-data-list-box-policy', 		// List box ID
			'DOM_FilterFormId'		=> '_iqb-filter-form-policy' 			// Filter Form ID
		];

		$data = [
			'records' => $records,
			'next_id' => $next_id,
			'next_url' => $next_id ? site_url( 'policies/page/r/' . $next_id ) : NULL
		] + $dom_data;

		/**
		 * Find View
		 */
		if($layout === 'f') // Full Layout
		{
			$view = 'policies/_index';

			$data = array_merge($data, [
				'filters' 		=> $this->_get_filter_elements(),
				'filter_url' 	=> site_url('policies/page/l/' )
			]);
		}
		else if($layout === 'l')
		{
			$view = 'policies/_list';
		}
		else
		{
			$view = 'policies/_rows';
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

		/**
		 * Filter Configurations
		 */
		$data['filters'] = $this->_get_filter_elements();
		$data['filter_url'] = site_url('policies/filter/');

		$this->template
						->set_layout('layout-advanced-filters')
						->partial(
							'content_header',
							'policies/_index_header',
							['content_header' => 'Manage Policies'] + $dom_data)
						->partial('content', 'policies/_index', $data)
						->partial('dynamic_js', 'policies/_list_js')
						->render($this->data);
	}

		private function _get_filter_elements()
		{
			$this->load->model('portfolio_model');

			$status_dropdown = _POLICY_status_dropdown(false);

			$admin_filters = [];
			if( $this->dx_auth->is_admin() )
			{
				$branch_dropdown = branch_dropdown('en', false);
				$admin_filters = [
					[
		                'field' => 'filter_branch_id',
		                'label' => 'Branch',
		                'rules' => 'trim|integer|in_list['.implode(',',array_keys($branch_dropdown)).']',
		                '_id'       => 'filter-branch',
		                '_type'     => 'dropdown',
		                '_data'     => IQB_BLANK_SELECT + $branch_dropdown,
		            ]
		        ];
			}

			$filters = [
				[
	                'field' => 'filter_status',
	                'label' => 'Policy Status',
	                'rules' => 'trim|alpha|exact_length[1]|in_list['.implode(',',array_keys($status_dropdown)).']',
	                '_id'       => 'filter-status',
	                '_type'     => 'dropdown',
	                '_data'     => IQB_BLANK_SELECT + $status_dropdown,
	            ],
				[
	                'field' => 'filter_portfolio_id',
	                'label' => 'Portfolio',
	                'rules' => 'trim|integer|max_length[11]',
	                '_id'       => 'filter-portfolio',
	                '_type'     => 'dropdown',
	                '_data'     => IQB_BLANK_SELECT + $this->portfolio_model->dropdown_children_tree(),
	            ],
	            [
		            'field' => 'filter_code',
		            'label' => 'Policy Code',
		            'rules' => 'trim|max_length[40]',
		            '_type'     => 'text',
		            '_required' => false
		        ],
		        [
		            'field' => 'filter_issued_from',
		            'label' => 'Issued Date (From)',
		            'rules' => 'trim|valid_date',
		            '_type'     => 'date',
		            '_required' => false
		        ],
		        [
		            'field' => 'filter_issued_to',
		            'label' => 'Issued Date (To)',
		            'rules' => 'trim|valid_date',
		            '_type'     => 'date',
		            '_required' => false
		        ],
	            [
					'field' => 'filter_keywords',
			        'label' => 'Keywords <i class="fa fa-info-circle"></i>',
			        'rules' => 'trim|max_length[80]',
	                '_type'     => 'text',
	                '_label_extra' => 'data-toggle="tooltip" title="Customer Name, PAN, Citizenship, Passport etc..."'
				],
			];
			return array_merge($admin_filters, $filters);
		}

		private function _get_filter_data()
		{
			$data = ['status' => 'empty'];

			if( $this->input->post() )
			{
				$rules = $this->_get_filter_elements();
				$this->form_validation->set_rules($rules);
				if( $this->form_validation->run() )
				{
					$data['data'] = [
						'branch_id' 		=> $this->input->post('filter_branch_id') ?? NULL,
						'code' 				=> $this->input->post('filter_code') ?? NULL,
						'status' 			=> $this->input->post('filter_status') ?? NULL,
						'portfolio_id' 		=> $this->input->post('filter_portfolio_id') ?? NULL,
						'issued_from' 		=> $this->input->post('filter_issued_from') ?? NULL,
						'issued_to' 		=> $this->input->post('filter_issued_to') ?? NULL,
						'keywords' 			=> $this->input->post('filter_keywords') ?? ''
					];
					$data['status'] = 'success';
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
	 * Get all Policies by Customer
	 *
	 * @param int $customer_id
	 * @param int $flush_cache
	 * @return JSON
	 */
	function by_customer($customer_id, $flush_cache = 0)
	{
		/**
		 * Check Permissions? OR Deny on Fail!
		 */
		$this->dx_auth->is_authorized('policies', 'explore.policy', TRUE);

		$customer_id 	= (int)$customer_id;

		/**
		 * Clear Cache??
		 */
		if($flush_cache)
		{
			$cache_var = 'policy_cst_' . $customer_id;
			$this->policy_model->clear_cache($cache_var);
		}

		$records = $this->policy_model->rows_by_customer($customer_id);
		$data = [
			'records' 					=> $records,
			'customer_id' 				=> $customer_id,
			'next_id' 					=> NULL
		];
		$html = $this->load->view('policies/_customer/_list_widget', $data, TRUE);
		$ajax_data = [
			'status' => 'success',
			'html'   => $html
		];

		$this->template->json($ajax_data);
	}



	// --------------------------------------------------------------------
	// CRUD OPERATIONS
	// --------------------------------------------------------------------


	/**
	 * Add a new Record
	 *
	 * @return void
	 */
	public function add()
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('policies', 'add.policy') )
		{
			$this->dx_auth->deny_access();
		}

		$form_data = [
			'form_elements' => $this->policy_model->validation_rules('add_edit_draft'),
			'record' 				=> NULL,
			'endorsement_record' 	=> NULL
		];

		// Form Submitted? Save the data
		$this->_save('add', $form_data);
	}

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
		// Capture the ID
		$id = (int)$id;

		// If Submit, must match (post ID = method ID)
		if($this->input->post())
		{
			$post_id = (int)$this->input->post('id');

			if($post_id !== $id)
			{
				return $this->template->json([
					'status' => 'error',
					'message' => 'Data mismatch (post vs method param)'
				],404);
			}
		}

		// Valid Record ?
		$record = $this->policy_model->row($id);
		if(!$record)
		{
			return $this->template->json([
				'status' => 'error',
				'message' => 'Policy not found!'
			],404);
		}

		/**
		 * Belongs to Me? i.e. My Branch? OR Terminate
		 */
		belongs_to_me($record->branch_id);


		/**
		 * Check Editable?
		 */
		_POLICY_is_editable($record->status);


		/**
		 * Load Tags as record attribute
		 */
		$this->load->model('rel_policy_tag_model');
		$record->tags = $this->rel_policy_tag_model->by_policy($record->id, TRUE);

		// Validation Rule
		$v_rules = $this->policy_model->validation_rules('add_edit_draft', FALSE, $record);

		// Object Details
		$object_record = $this->object_model->row($record->object_id);
		$record->object_name = _OBJ_select_text($object_record);
		$form_data = [
			'form_elements' 		=> $v_rules,
			'record' 				=> $record,
			'endorsement_record' 	=> $this->endorsement_model->get_first($record->id)
		];

		// Form Submitted? Save the data
		$this->_save('edit', $form_data, $from_widget);
	}

	// --------------------------------------------------------------------

	/**
	 * Save a Record
	 *
	 * @param string $action [add|edit]
	 * @param object|null $record Record Object or NULL
	 * @return array
	 */
	private function _save($action, $form_data, $from_widget = 'n')
	{

		// Valid action?
		if( !in_array($action, array('add', 'edit')))
		{
			return $this->template->json([
				'status' => 'error',
				'message' => 'Invalid action!'
			],404);
		}

		// Valid "from" ?
		if( !in_array($from_widget, array('y', 'n')))
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
		$record = $form_data['record'];

		if( $this->input->post() )
		{
			$done = FALSE;

			// These Rules are Sectioned, We need to merge Together
			$v_rules = $this->policy_model->validation_rules('add_edit_draft', TRUE, $record);
            $this->form_validation->set_rules($v_rules);
			if($this->form_validation->run() === TRUE )
        	{
        		$data = $this->input->post();

        		try {
    				// Insert or Update?
					if($action === 'add')
					{
						$done = $this->policy_model->add_debit_note($data); // No Validation on Model
					}
					else
					{

						// Now Update Data
						$done = $this->policy_model->edit_debit_note($record->id, $data);


						/**
						 * Policy Package Changed?
						 * --------------------------
						 * If changed, we have to reset the premium info
						 */
						if($done)
						{
							// $this->__reset_premium_on_debitnote_update($record, $data);
						}
					}
        		} catch (Exception $e) {
        			return $this->template->json([
						'status' => 'error',
						'message' => $e->getMessage()
					], 500);
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

				if($action === 'add')
				{
					$record = $this->policy_model->row($done);
					$html = $this->load->view('policies/_single_row', ['record' => $record], TRUE);

					$ajax_data['updateSectionData'] = [
						'box' 		=> '#search-result-policy',
						'method' 	=> 'prepend',
						'html'		=> $html
					];
				}
				else
				{
					/**
					 * Widget or Row?
					 */
					$record = $from_widget === 'n'
								? $this->policy_model->row($record->id)
								: $this->policy_model->get($record->id);

					$view = $from_widget === 'n'
									? 'policies/_single_row'
									: 'policies/tabs/_tab_overview';

					$view_data = [
						'record' => $record
					];
					if($from_widget === 'y')
					{
						/**
						 * Get the Policy Fresh/Renewal Txn Record
						 */
						try {

							$endorsement_record = $this->endorsement_model->get_first( $record->id);

						} catch (Exception $e) {

							return $this->template->json([
								'status' => 'error',
								'message' => $e->getMessage()
							], 404);
						}

						$view_data['endorsement_record'] = $endorsement_record;

						/**
						 * Beema Samiti Report Headings
						 */
						$this->load->model('rel_policy_bsrs_heading_model');
						$view_data['bsrs_headings_policy'] = $this->rel_policy_bsrs_heading_model->by_policy($record->id);

						/**
						 * Creditors List
						 */
						$this->load->model('rel_policy_creditor_model');
						$view_data['creditors'] = $this->rel_policy_creditor_model->rows(['REL.policy_id' => $record->id]);

					}

					$html = $this->load->view($view, $view_data, TRUE);
					$ajax_data['updateSectionData']  = [
						'box' 		=> $from_widget === 'n' ? '#_data-row-policy-' . $record->id : '#tab-policy-overview-inner',
						'method' 	=> 'replaceWith',
						'html'		=> $html
					];
				}
				return $this->template->json($ajax_data);
			}
			else
			{
				return $this->template->json([
					'status' 		=> $status,
					'message' 		=> $message,
					'reloadForm' 	=> true,
					'form' 			=> $this->load->view('policies/_form', $form_data, TRUE)
				]);
			}
		}

		/**
		 * Render The Form
		 */
		$json_data = [
			'form' => $this->load->view('policies/_form_box', $form_data, TRUE)
		];
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Edit a Beema Samiti Report Tags
	 *
	 * These are the tags required to compute Beema Samiti Reports
	 *
	 * @param integer $id
	 * @return void
	 */
	public function bs_tags($id)
	{
		// Capture the ID
		$id = (int)$id;

		// If Submit, must match (post ID = method ID)
		if($this->input->post())
		{
			$post_id = (int)$this->input->post('id');

			if($post_id !== $id)
			{
				return $this->template->json([
					'status' => 'error',
					'message' => 'Data mismatch (post vs method param)'
				],404);
			}
		}

		// Valid Record ?
		$record = $this->policy_model->row($id);
		if(!$record)
		{
			return $this->template->json([
				'status' => 'error',
				'message' => 'Policy not found!'
			],404);
		}

		/**
		 * Belongs to Me? i.e. My Branch? OR Terminate
		 */
		belongs_to_me($record->branch_id);


		/**
		 * Check Editable?
		 */
		_POLICY_is_editable($record->status);


		// Validation Rule
		$v_rules = $this->policy_model->get_bs_report_heading_rules();


		/**
		 * Heading Type-wise - Beema Samiti Report Headings(tags)
		 */
		$this->load->model('bsrs_heading_model');
		$this->load->model('rel_policy_bsrs_heading_model');
		$bsrs_headings_portfolio 	= $this->bsrs_heading_model->by_portfolio($record->portfolio_id, 'policy');
		$bsrs_headings_policy 		= $this->rel_policy_bsrs_heading_model->by_policy($record->id);

		if( $this->input->post() )
		{
			$done = FALSE;

			// These Rules are Sectioned, We need to merge Together
            $this->form_validation->set_rules($v_rules);
			if($this->form_validation->run() === TRUE )
        	{
        		$bsrs_heading_ids = array_unique( $this->input->post('bsrs_heading_id') );
        		if($bsrs_heading_ids)
        		{
        			$done = $this->rel_policy_bsrs_heading_model->save($record->id, $bsrs_heading_ids);
        		}

        		$return_data = [
        			'status' => $done ? 'success' : 'error',
        			'message' => $done ? 'Successfully updated.' : 'Could not update.',
        		];

        		if($done)
        		{
        			$return_data = array_merge($return_data, [
        				'updateSection' => true,
						'hideBootbox' => true
					]);

        			// Updated List
        			$bsrs_headings_policy 	= $this->rel_policy_bsrs_heading_model->by_policy($record->id);
        			$view_data 	= [ 'record' => $record, 'bsrs_headings_policy' => $bsrs_headings_policy];
        			$html 		= $this->load->view('policies/snippets/_policy_bsrs_headings', $view_data, TRUE);

        			$return_data['updateSectionData']  = [
						'box' 		=> '#policy-bsrs-headings',
						'method' 	=> 'replaceWith',
						'html'		=> $html
					];
        		}
        		return $this->template->json($return_data);

        	}
        	else
        	{
        		return $this->template->json([
					'status' => 'error',
					'title'  => 'Validation Error!',
					'message' => validation_errors()
				],422);
        	}
        }


		$form_data = [
			'form_elements' => $v_rules,
			'record' 		=> $record,
			'bsrs_headings_portfolio' 	=> $bsrs_headings_portfolio,
			'bsrs_headings_policy' 		=> $bsrs_headings_policy
		];


		/**
		 * Render The Form
		 */
		$json_data = [
			'form' => $this->load->view('policies/_form_bs_tags', $form_data, TRUE)
		];
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Edit a Recrod from Endorsement
	 *
	 *
	 * @param integer $id
	 * @return void
	 */
	public function edit_endorsement($id)
	{

		/**
		 * !!! NOTE !!!
		 * Let's disable this feature as we have dates directly editable on endorsement.
		 */
		$this->template->render_404();
		exit();

		// Capture the ID
		$id = (int)$id;

		// Valid Record ?
		$record = $this->policy_model->row($id);
		if(!$record)
		{
			return $this->template->json([
				'status' => 'error',
				'message' => 'Policy not found!'
			],404);
		}

		$endorsement_record = $this->endorsement_model->get_current_endorsement($record->id);
		if(!$endorsement_record)
		{
			return $this->template->json([
				'status' => 'error',
				'message' => 'Endorsement not found!'
			],404);
		}

		/**
		 * Belongs to Me? i.e. My Branch? OR Terminate
		 */
		belongs_to_me($record->branch_id);


		/**
		 * Editable Permission? We should check permission of Txn not of Policy
		 */
		_ENDORSEMENT_is_editable($endorsement_record->status, $endorsement_record->flag_current);


		/**
		 * Endorsement Type Allows Policy to Edit?
		 */
		if( !_ENDORSEMENT_is_policy_editable($endorsement_record->txn_type) )
		{
			return $this->template->json([
				'status' 	=> 'error',
				'title' 	=> 'Invalid Endorsement Type!',
				'message' 	=> 'You <strong>CAN NOT EDIT</strong> policy information for this type of Transaction/Endorsement.'
			],403);
		}


		/**
         * Do we have audit data available? If yes, pass it instead of policy's original data
         *
         * !!!NOTE: We need to pass the original record for getting old data. That's why clone.
         */
		$edit_record = clone $record; // We need to pass the original record for getting old data.
        $audit_record = $endorsement_record->audit_policy ? json_decode($endorsement_record->audit_policy) : NULL;
        if($audit_record)
        {
            // Get the New data
            $new_data = (array)$audit_record->new;

            // Overwrite the Policy record with this data
            foreach($new_data as $key=>$value)
            {
            	$edit_record->{$key} = $value;
            }

            // Build datetime fields
            $fields = ['start', 'end', 'issued'];
            foreach($fields as $f)
            {
                $datetime_field = "{$f}_datetime";
                $date = "{$f}_date";
                $time = "{$f}_time";
                $edit_record->{$datetime_field} = $edit_record->{$date} . ' ' . $edit_record->{$time};
            }
        }

		// Validation Rule
		$v_rules = $this->policy_model->get_endorsement_validation_rules();
		$form_data = [
			'form_elements' => $v_rules,
			'record' 		=> $edit_record
		];

		// Form Submitted? Save the data
		$this->_save_endorsement($form_data, $v_rules, $record, $endorsement_record);
	}

	// --------------------------------------------------------------------

	/**
	 * Save a Record from Endorsement
	 *
	 */
	private function _save_endorsement($form_data, $v_rules, $record, $endorsement_record)
	{
		/**
		 * Form Submitted?
		 */
		if( $this->input->post() )
		{
			$done = FALSE;

			$this->form_validation->set_rules($v_rules);
			if($this->form_validation->run() === TRUE )
        	{
        		$post_data = $this->input->post();
        		$audit_data = [
        			'endorsement_id' 	=> $endorsement_record->id,
        			'policy_id'  		=> $record->id,
        			'audit_policy' 		=> $this->_get_endorsement_audit_data($record, $post_data)
        		];

        		/**
        		 * Save Data (Insert if new, Update else)
        		 */
        		$this->load->model('audit_endorsement_model');
        		if($endorsement_record->audit_endorsement_id)
        		{
        			$done = $this->audit_endorsement_model->update($endorsement_record->audit_endorsement_id, $audit_data, TRUE);
        		}
        		else
        		{
        			$done = $this->audit_endorsement_model->insert($audit_data, TRUE);
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

				return $this->template->json([
					'status' 		=> $status,
					'message' 		=> $message,
					'updateSection' => false,
					'hideBootbox' 	=> true
				]);
        	}
        	else
        	{
        		return $this->template->json([
					'status' 		=> 'error',
					'message' 		=> validation_errors()
				]);
        	}
		}

		/**
		 * Render The Form
		 */
		$json_data = [
			'form' => $this->load->view('policies/_form_endorsement', $form_data, TRUE)
		];
		$this->template->json($json_data);
	}

		private function _get_endorsement_audit_data($old_record, $post_data)
		{
			$old_data 	= [];
			$new_data 	= [];
			$old_record = (array)$old_record;
			$post_data 	= $this->__refactor_datetime_fields($post_data);
			foreach($this->policy_model->endorsement_fields as $key)
			{
				$old_data[$key] = $old_record[$key];
				$new_data[$key] = $post_data[$key];
			}

			return json_encode([
				'new' => $new_data,
				'old' => $old_data
			]);
		}

		private function __refactor_datetime_fields($data)
        {
            // Dates
            $data['issued_date']    = date('Y-m-d', strtotime($data['issued_datetime']));
            $data['start_date']     = date('Y-m-d', strtotime($data['start_datetime']));
            $data['end_date']       = date('Y-m-d', strtotime($data['end_datetime']));

            // Times
            $data['issued_time']    = date('H:i:00', strtotime($data['issued_datetime']));
            $data['start_time']     = date('H:i:00', strtotime($data['start_datetime']));
            $data['end_time']       = date('H:i:00', strtotime($data['end_datetime']));

            // unset
            unset($data['issued_datetime']);
            unset($data['start_datetime']);
            unset($data['end_datetime']);

            return $data;
        }

	// --------------------------------------------------------------------

	/**
	 * Delete a Policy
	 *
	 * Only Draft Version of a Policy can be deleted.
	 *
	 * @param integer $id
	 * @return json
	 */
	public function delete($id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->policy_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		/**
		 * Belongs to Me? i.e. My Branch? OR Terminate
		 */
		belongs_to_me($record->branch_id);


		/**
		 * Check Permissions
		 *
		 * Deletable Status
		 * 		draft
		 *
		 * Deletable Permission
		 * 		delete.draft.policy
		 */

		// Deletable Status?
		if( $record->status !== IQB_POLICY_STATUS_DRAFT )
		{
			$this->dx_auth->deny_access();
		}

		// Deletable Permission ?
		$__flag_authorized 		= FALSE;
		if( $this->dx_auth->is_authorized('policies', 'delete.draft.policy') )
		{
			$__flag_authorized = TRUE;
		}

		if( !$__flag_authorized )
		{
			$this->dx_auth->deny_access();
		}


		$data = [
			'status' 	=> 'error',
			'message' 	=> 'You cannot delete the default records.'
		];
		/**
		 * Safe to Delete?
		 */
		if( !safe_to_delete( 'Policy_model', $id ) )
		{
			return $this->template->json($data);
		}

		$done = $this->policy_model->delete($record->id);

		if($done)
		{
			$data = [
				'status' 	=> 'success',
				'message' 	=> 'Successfully deleted!',
				'removeRow' => true,
				'rowId'		=> '#_data-row-policy-'.$record->id
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
	 * Add a Creditor
	 *
	 * @return void
	 */
	public function save_creditor($id, $creditor_id = NULL, $creditor_branch_id = NULL)
	{

		$this->load->model('rel_policy_creditor_model');

		// Capture the ID
		$id 				= (int)$id;
		$creditor_id 		= (int)$creditor_id;
		$creditor_branch_id = (int)$creditor_branch_id;


		// Valid Record ?
		$policy_record = $this->policy_model->row($id);
		if(!$policy_record)
		{
			return $this->template->json([
				'status' => 'error',
				'message' => 'Policy not found!'
			],404);
		}

		$record = $this->rel_policy_creditor_model->get($id, $creditor_id, $creditor_branch_id);

		/**
		 * Belongs to Me? i.e. My Branch? OR Terminate
		 */
		belongs_to_me($policy_record->branch_id);


		/**
		 * Check Editable?
		 */
		_POLICY_is_editable($policy_record->status);


		$form_data = [
			'form_elements' => $this->policy_model->get_creditor_validation_rules($record),
			'record' 		=> $record,
			'policy_record' => $policy_record
		];

		// Form Submitted? Save the data
		$this->_save_creditor($form_data, $policy_record, $record);
	}

	// --------------------------------------------------------------------

	/**
	 * Save a Record from Endorsement
	 *
	 */
	private function _save_creditor($form_data, $policy_record, $record = NULL)
	{
		/**
		 * Form Submitted?
		 */
		if( $this->input->post() )
		{
			$done = FALSE;
			$v_rules = $this->policy_model->get_creditor_validation_rules();
			$this->form_validation->set_rules($v_rules);
			if($this->form_validation->run() === TRUE )
        	{
        		$post_data = $this->input->post();

        		$data = [
        			'policy_id' 			=> $policy_record->id,
        			'creditor_id' 		 	=> $post_data['creditor_id'],
        			'creditor_branch_id' 	=> $post_data['creditor_branch_id']
        		];

        		$done = $this->rel_policy_creditor_model->save($data, $record);

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

				$updateSectionData = NULL;
				if($done)
				{
					$creditors = $this->rel_policy_creditor_model->rows(['REL.policy_id' => $policy_record->id]);
					$html = $this->load->view(
											'policies/_rows_creditor',
											[
												'creditors' => $creditors,
												'policy_record' => $policy_record
											], TRUE);

					$updateSectionData = [
						'box' 		=> '#policy-creditor-list',
						'method' 	=> 'html',
						'html'		=> $html
					];
				}

				return $this->template->json([
					'status' 		=> $status,
					'message' 		=> $message,
					'hideBootbox' 	=> true,
					'updateSection' => $done,
					'updateSectionData' => $updateSectionData
				]);
        	}
        	else
        	{
        		return $this->template->json([
					'status' 		=> 'error',
					'message' 		=> 'Validation Error.',
					'reloadForm' 	=> true,
					'form' 			=> $this->load->view('policies/_form_creditor', $form_data, TRUE)
				]);
        	}
		}

		/**
		 * Render The Form
		 */
		$json_data = [
			'form' => $this->load->view('policies/_form_creditor', $form_data, TRUE)
		];
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Add a Creditor
	 *
	 * @return void
	 */
	public function delete_creditor($id, $creditor_id, $creditor_branch_id)
	{

		$this->load->model('rel_policy_creditor_model');

		// Capture the ID
		$id 				= (int)$id;
		$creditor_id 		= (int)$creditor_id;
		$creditor_branch_id = (int)$creditor_branch_id;


		// Valid Record ?
		$policy_record = $this->policy_model->row($id);
		if(!$policy_record)
		{
			return $this->template->json([
				'status' => 'error',
				'message' => 'Policy not found!'
			],404);
		}

		$record = $this->rel_policy_creditor_model->get($id, $creditor_id, $creditor_branch_id);
		if(!$record)
		{
			return $this->template->json([
				'status' => 'error',
				'message' => 'Creditor not found!'
			],404);
		}

		/**
		 * Belongs to Me? i.e. My Branch? OR Terminate
		 */
		belongs_to_me($policy_record->branch_id);


		/**
		 * Check Editable?
		 */
		_POLICY_is_editable($policy_record->status);

		/**
		 * Let's Delete the Creditor
		 */
		$done = $this->rel_policy_creditor_model->delete_single($record->policy_id, $record->creditor_id, $record->creditor_branch_id);
		if($done)
		{
			$row_id = '_policy-creditor-' . $record->policy_id . '-' . $record->creditor_id . '-' . $record->creditor_branch_id;
			$data = [
				'status' 	=> 'success',
				'message' 	=> 'Successfully deleted!',
				'removeRow' => true,
				'rowId'		=> '#' . $row_id
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
	// CRUD HELPER - FUNCTIONS
	// --------------------------------------------------------------------

		/**
		 * Get Policy Packages for Portfolio
		 *
		 * Get the policy packages for specified portfolio
		 *
		 * @param integer $portfolio_id
		 * @param string 	$method 	Return Method
		 * @return mixed
		 */
		public function gppp($portfolio_id)
		{
			// Valid Record ?
			$portfolio_id = (int)$portfolio_id;

			// Policy Package Options
			$ppo = _OBJ_policy_package_dropdown($portfolio_id, false);

			if( !empty($ppo))
			{
				$this->template->json([
					'status' => 'success',
					'ppo' => $ppo,
					'blank' => count($ppo) !== 1 // Show '' => 'Select...' option or not
				]);
			}

			$this->template->json([
				'title'  => 'Not Found!',
				'status' => 'error',
				'message' => 'Either "Portfolio" or "Policy Packages" not found for supplied portfolio.'
			], 404);
		}

		// --------------------------------------------------------------------

		/**
		 * Get Creditor Company Branches for Supplied  Creditor Company
		 *
		 * @param integer $portfolio_id
		 * @param string 	$method 	Return Method
		 * @return mixed
		 */
		public function gccbc($company_id)
		{
			// Valid Record ?
			$company_id = (int)$company_id;
			$this->load->model('company_branch_model');

			$options = $this->company_branch_model->dropdown_by_company($company_id);
			if( !empty($options))
			{
				$this->template->json([
					'status' => 'success',
					'options' => $options
				]);
			}
			else
			{
				$this->template->json([
					'status' => 'error',
					'message' => 'No Branch Found. Please ask your IT Support to add "Company Branch" of selected "Creditor Company" and try again.'
				], 404);
			}
		}

		// --------------------------------------------------------------------

		/**
		 * Reset Premium on Debit Note Update
		 *
		 * Reset premium on change of any of the followings
		 * 	- portfolio
		 * 	- policy package
		 * 	- customer
		 * 	- policy object
		 * 	- flag direct discount/agent commission
		 *
		 * @param object $before_update Policy Record Before Update
		 * @param array $data Post Data
		 * @return void
		 */
		private function __reset_premium_on_debitnote_update($before_update, $data)
		{
			// Process data to get fractioned date/time
			$after_update = (object)$this->policy_model->before_update__defaults($data);

			$fields = ['portfolio_id', 'policy_package', 'customer_id', 'object_id', 'flag_dc', 'start_date', 'end_date'];
			$__flag_reset = FALSE;
			foreach($fields as $column)
			{
				if($before_update->{$column} != $after_update->{$column})
				{
					$__flag_reset = TRUE;
					break;
				}
			}
			if( $__flag_reset === TRUE )
			{

				return $this->endorsement_model->reset_by_policy($before_update->id);
			}
			return TRUE;
		}

	    // --------------------------------------------------------------------

		/**
		 * Callback: Valid Creditor Branch
		 *
		 *
		 * @param int $creditor_branch_id
		 * @return type
		 */
	    public function _cb_valid_creditor_branch($creditor_branch_id)
	    {
	    	$creditor_branch_id = (int)$creditor_branch_id;
	    	$creditor_id = (int)$this->input->post('creditor_id');
	    	$this->load->model('company_branch_model');

	    	if( !$this->company_branch_model->valid_branch($creditor_id, $creditor_branch_id) )
	    	{
	    		$this->form_validation->set_message('_cb_valid_creditor_branch', 'The supplied "Branch" does not belong to selected "Creditor Company".');
	            return FALSE;
	    	}

	    	/**
	    	 * Check if already exists?
	    	 */
	    	$this->load->model('rel_policy_creditor_model');
	    	$policy_id = $this->input->post('policy_id');
	    	$where = [
	    		'policy_id' 			=> $policy_id,
	            'creditor_id'           => $creditor_id,
	        ];
	        if( $this->rel_policy_creditor_model->check_duplicate($where, $creditor_branch_id) )
	    	{
	    		$this->form_validation->set_message('_cb_valid_creditor_branch', 'The supplied Creditor already exists.');
	            return FALSE;
	    	}

	        return TRUE;
	    }

	    // --------------------------------------------------------------------

		/**
	     * Callback : Validate Backdate
	     *
	     * If user has supplied backdate, please make sure that :
	     * 		1. The user is allowed to enter Backdate
	     * 		2. If so, the supplied date should be withing backdate limit
	     *
	     * @param date $date
	     * @return bool
	     */
	    public function _cb_valid_backdate($date)
	    {
	    	$timestamp 		= strtotime($date);

	    	/**
	    	 * Not a past date?
	    	 * ---------------
	    	 * Simply return true;
	    	 */
	    	$dateonly_timestamp = strtotime(date('Y-m-d', $timestamp));
	    	$today_timestamp 	= strtotime(date('Y-m-d'));
	    	if($today_timestamp <=  $dateonly_timestamp )
	    	{
	    		return TRUE;
	    	}


	    	/**
	    	 * Backdate Allowed?
	    	 */
	    	if( !$this->dx_auth->is_backdate_allowed() )
	    	{
	    		$this->form_validation->set_message('_cb_valid_backdate', 'You are not authorized to enter "Back Dates"');
	            return FALSE;
	    	}

	    	/**
	    	 * Backdate Limit Set by Administrator?
	    	 */
	    	$back_date_limit = $this->settings->back_date_limit;
	    	if( !$back_date_limit || !valid_date($back_date_limit) )
	    	{
	    		$this->form_validation->set_message('_cb_valid_backdate', '"Back Date Limit" is not setup properly.<br/>Please contact Administrator for further assistance.');
	            return FALSE;
	    	}

	    	/**
	    	 * Within Backdate Range?
	    	 * ----------------------
	    	 * i.e. Back Date <= Supplied Date
	    	 */
	    	$back_date_timestamp = strtotime($back_date_limit);

	    	if($timestamp < $back_date_timestamp )
	    	{
	    		$this->form_validation->set_message('_cb_valid_backdate', 'Supplied date(%s) can not exceed "Back Date Limit"');
	            return FALSE;
	    	}
	        return TRUE;
	    }

	    // --------------------------------------------------------------------

		/**
	     * Callback : Valid Duration
	     *
	     * Case I:
	     * 		Proposed Date <= Issued Date
	     *
	     * Case II:
	     * 		Issued Date <= Start Date
	     *
	     * Case III:
	     * 		Start Date < End Date
	     *
	     * Case IV:
	     * 		(End Date - Start Date) should not exceed the Portfolio's Default Duration
	     *
	     * @param date $end_datetime
	     * @return bool
	     */
	    public function _cb_valid_policy_duration($end_datetime)
	    {
	    	$proposed_date 		= $this->input->post('proposed_date');
	    	$issued_datetime 	= $this->input->post('issued_datetime');
	    	$start_datetime 	= $this->input->post('start_datetime');
	    	$portfolio_id 		= (int)$this->input->post('portfolio_id');

	    	$proposed_timestamp = strtotime($proposed_date);
	    	$issued_timestamp 	= strtotime($issued_datetime);
	    	$start_timestamp    = strtotime($start_datetime);
	        $end_timestamp      = strtotime($end_datetime);

	    	/**
	    	 * Case I: Proposed Date <= Issued Date
	    	 */
	    	if( $proposed_timestamp > $issued_timestamp )
	    	{
	    		$this->form_validation->set_message('_cb_valid_policy_duration', '"Proposed Date" must not exceed "Issued Date & Time"');
	            return FALSE;
	    	}

	    	/**
	    	 * Case II: Issued Date <= Start Date
	    	 */
	    	if( $issued_timestamp > $start_timestamp )
	    	{
	    		$this->form_validation->set_message('_cb_valid_policy_duration', '"Issued Date & Time" must not exceed "Start Date & Time"');
	            return FALSE;
	    	}


	    	/**
	    	 * Case III: Start Date < End Date
	    	 */
	    	if( $start_timestamp > $end_timestamp )
	    	{
	    		$this->form_validation->set_message('_cb_valid_policy_duration', '"Start Date & Time" must not exceed "End Date & Time"');
	            return FALSE;
	    	}

	    	/**
	    	 * Case IV: (End Date - Start Date) should not exceed the Portfolio's Default Duration
	    	 */
	    	if( !$portfolio_id )
	    	{
	    		$this->form_validation->set_message('_cb_valid_policy_duration', 'Please select portfolio first to compute "Policy Short Term Info"');
	            return FALSE;
	    	}

	    	$fy_record = $this->fiscal_year_model->get_fiscal_year( $issued_datetime );


	    	/**
	    	 * Portfolio Default Duration Applies?
	    	 */
	    	$this->load->model('portfolio_setting_model');
			$pfs_record = $this->portfolio_setting_model->get_by_fiscal_yr_portfolio($fy_record->id, $portfolio_id);
			if( !$pfs_record )
			{
				$this->form_validation->set_message('_cb_valid_policy_duration', "No portfolio setting record found for supplied portfolio. Please check with Administrator!");
	            return FALSE;
			}

			if($pfs_record->flag_default_duration === IQB_FLAG_YES )
			{
				$difference         = $end_timestamp - $start_timestamp;
		        $days               = floor($difference / (60 * 60 * 24));
		        $default_duration 	= (int)$pfs_record->default_duration;
		    	if( $days > $default_duration )
		    	{
		    		$this->form_validation->set_message('_cb_valid_policy_duration', "End date should not be higher than portfolio's default duration ({$default_duration} days)");
		            return FALSE;
		    	}
			}

	        return TRUE;
	    }

	    // --------------------------------------------------------------------

		/**
	     * Callback : Valid Object Defaults
	     *
	     * Checks the Object Validity for the given Policy
	     * 		1. Object Owner and Selected Customer Should Match
	     * 		2. If the object is already assigned to other policy which is not
	     * 			Canceled|Expired, You can not assign this object to new policy.
	     *		3. Object Portfolio/Subportfolio should match Object portfolio/subportfolio
	     *
	     * @param string $str
	     * @return bool
	     */
	    public function _cb_valid_object_defaults($object_id)
	    {
	    	$object_id 		= (int)$object_id;
	    	$customer_id 	= (int)$this->input->post('customer_id');
	    	$portfolio_id 		= (int)$this->input->post('portfolio_id');

	    	/**
	    	 * Case 1 : Check Ownership
	    	 * ------------------------
	    	 */
	    	if( !$object_id OR !$customer_id)
	    	{
	    		$this->form_validation->set_message('_cb_valid_object_defaults', 'Customer and/or Object not supplied.');
	            return FALSE;
	    	}

	    	$object_record = $this->object_model->row($object_id);
	    	if(!$object_record)
	    	{
	    		$this->form_validation->set_message('_cb_valid_object_defaults', 'You are trying to manipulate CUSTOMER & OBJECT, which unfortunately, DOES NOT WORK!');
	            return FALSE;
	    	}

	    	if( $object_record->customer_id != $customer_id )
	    	{
	    		$this->form_validation->set_message('_cb_valid_object_defaults', 'The selected Object does not belong to the selected Customer.');
	            return FALSE;
	    	}

	    	/**
	    	 * Case 2: Object Editable?
	    	 * ------------------------
	    	 *
	    	 *  ! IMPORTANT !
	    	 *
	    	 * Is this object is free to assign to new/editable policy?
	    	 *
	    	 * Logic:
	    	 * 	If this policy object is already assigned to a policy which is NOT (CANCELED|EXPIRED)
	    	 * 	OR
	    	 *  If this policy object is already assigned to a policy which is editable
	    	 *
	    	 * For that, Simply get the latest policy record of this object and check
	    	 *
	    	 */
	    	$id = $this->input->post('id') ?? NULL;
	    	$id = $id ? (int)$id : NULL;
	    	$policy_record = $this->object_model->get_latest_policy($object_id);

	    	if( $policy_record )
	    	{
	    		// Add Mode
	    		if(!$id)
	    		{
	    			// If found Editable Policy Record, It is already assigned to another policy which is working with this object
	    			if( !in_array( $policy_record->status, [IQB_POLICY_STATUS_CANCELED, IQB_POLICY_STATUS_EXPIRED] ) )
		    		{
		    			$this->form_validation->set_message('_cb_valid_object_defaults', 'The selected object is already assigned to another active Policy.');
		            	return FALSE;
		    		}
	    		}

	    		// Edit Mode
	    		else
	    		{
	    			// If policy Do not Match, THe policy status must be Expired|Canceled
	    			if( $policy_record->id != $id )
		    		{
		    			$this->form_validation->set_message('_cb_valid_object_defaults', 'The selected object is already assigned to another Policy.');
		            	return FALSE;
		    		}
	    		}
	    	}

	    	/**
	    	 * Case 3 : Portfolio/Sub-portfolio Match between Policy-Object
	    	 * ------------------------------------------------------------
	    	 */
	    	if( $object_record->portfolio_id != $portfolio_id )
    		{
    			$this->form_validation->set_message('_cb_valid_object_defaults', "The object's Portfolio/Sub-portfolio MUST match with Policy's Portfolio/Sub-Portfolio");
            	return FALSE;
    		}

	        return TRUE;
	    }


	// --------------------------------------------------------------------
	//  POLICY DETAILS
	// --------------------------------------------------------------------


    /**
     * View Policy Details
     *
     * @param integer $id
     * @return void
     */
    public function details($id)
    {
    	/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('policies', 'explore.policy') )
		{
			$this->dx_auth->deny_access();
		}

    	$id = (int)$id;
		$record = $this->policy_model->get($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		/**
		 * Get the Policy Fresh/Renewal Txn Record
		 */
		try {

			$endorsement_record = $this->endorsement_model->get_first( $record->id );

		} catch (Exception $e) {

			return $this->template->json([
				'status' => 'error',
				'message' => $e->getMessage()
			], 404);
		}

		/**
		 * Load Portfolio Specific Helper File
		 */
		try { load_portfolio_helper($record->portfolio_id);} catch (Exception $e) {
			return $this->template->json([ 'status' => 'error', 'message' => $e->getMessage()], 404);
		}

		$this->load->model('rel_policy_bsrs_heading_model');
		$bsrs_headings_policy 		= $this->rel_policy_bsrs_heading_model->by_policy($record->id);

		$this->load->model('rel_policy_creditor_model');
		$creditors = $this->rel_policy_creditor_model->rows(['REL.policy_id' => $record->id]);

		$data = [
			'record' 				=> $record,
			'endorsement_record' 	=> $endorsement_record,
			'bsrs_headings_policy' 	=> $bsrs_headings_policy,
			'creditors' 			=> $creditors
		];


		if ( $this->input->is_ajax_request() )
		{
			$html = $this->load->view('policies/tabs/_tab_overview', $data, TRUE);
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


		$page_header = 'Policy Details';

		$this->data['site_title'] = 'Policy Details | ' . $record->code;
		$this->template->partial(
							'content_header',
							'templates/_common/_content_header',
							[
								'content_header' => $page_header,
								'breadcrumbs' => ['Policies' => 'policies', 'Details' => NULL]
						])
						->partial('content', 'policies/_details', $data)
						->partial('dynamic_js', 'policies/_policy_js')
						->render($this->data);

    }

    // --------------------------------------------------------------------
	//  POLICY PRINT - Debit Note & Schedule
    // --------------------------------------------------------------------

    /**
	 * Print Policy Debit Note
	 *
	 * @param integer $id  Policy ID
	 * @return void
	 */
    public function debitnote($id)
    {
    	/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('policies', 'generate.policy.schedule') )
		{
			$this->dx_auth->deny_access();
		}

    	$id = (int)$id;
		$record = $this->policy_model->get($id);
		if(!$record)
		{
			$this->template->render_404();
		}


		/**
		 * Already Active, Go for schedule
		 */
		if(in_array($record->status, [IQB_POLICY_STATUS_ACTIVE, IQB_POLICY_STATUS_CANCELED, IQB_POLICY_STATUS_EXPIRED]))
		{
			redirect('policies/schedule/' . $record->id );
			exit(0);
		}

		load_portfolio_helper($record->portfolio_id);
		$schedule_view 	= _POLICY__get_schedule_view($record->portfolio_id);
		if(!$schedule_view)
		{
			return $this->template->json([
				'status' => 'error',
				'message' => "No schedule view exists for given portfolio({$record->portfolio_name})."
			], 404);
		}
		try {

			$endorsement_record = $this->endorsement_model->get_first( $record->id );

		} catch (Exception $e) {

			return $this->template->json([
				'status' => 'error',
				'message' => $e->getMessage()
			], 404);
		}

		/**
		 * Generate Dynamic HTML for Schedule
		 */
		$this->load->model('rel_policy_creditor_model');
		$creditors = $this->rel_policy_creditor_model->rows(['REL.policy_id' => $record->id]);
		$data = [
			'record' 				=> $record,
			'endorsement_record' 	=> $endorsement_record,
			'creditors' 			=> $creditors
		];
		$html = $this->load->view( $schedule_view, $data, TRUE);


		/**
		 * Render Print View
		 */
		try {

			_POLICY__schedule_pdf( $record, 'print', $html );
		}
		catch (Exception $e) {

			return $this->template->json([
				'status' => 'error',
				'message' => $e->getMessage()
			], 404);
		}
		exit(0);
    }

    // --------------------------------------------------------------------

	/**
	 * Print Policy Schedule
	 *
	 * @param integer $id  Policy ID
	 * @return void
	 */
    public function schedule($id)
    {
    	/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('policies', 'generate.policy.schedule') )
		{
			$this->dx_auth->deny_access();
		}

    	$id = (int)$id;
		$record = $this->policy_model->get($id);
		if(!$record)
		{
			$this->template->render_404();
		}


		/**
		 * Non-active? Go to Debit Note!
		 */
		if( !in_array($record->status, [IQB_POLICY_STATUS_ACTIVE, IQB_POLICY_STATUS_CANCELED, IQB_POLICY_STATUS_EXPIRED]))
		{
			redirect('policies/debitnote/' . $record->id );
			exit(0);
		}

		/**
		 * PDF exists?
		 */

		$this->load->helper('file');
		$filename 		= _POLICY__schedule_filename( $record->code );
		$file_full_path = rtrim(self::$data_upload_path, '/') . '/' . $filename;
		if( file_exists($file_full_path) )
		{
			render_pdf($file_full_path);
		}
		else
		{
			/**
			 * Save PDF & Render on Browser
			 */
			load_portfolio_helper($record->portfolio_id);
			$schedule_view 	= _POLICY__get_schedule_view($record->portfolio_id);
			if(!$schedule_view)
			{
				return $this->template->json([
					'status' => 'error',
					'message' => "No schedule view exists for given portfolio({$record->portfolio_name})."
				], 404);
			}
			try {

				$endorsement_record = $this->endorsement_model->get_first( $record->id);

			} catch (Exception $e) {

				return $this->template->json([
					'status' => 'error',
					'message' => $e->getMessage()
				], 404);
			}

			/**
			 * Creditors
			 */
			$this->load->model('rel_policy_creditor_model');
			$creditors = $this->rel_policy_creditor_model->rows(['REL.policy_id' => $record->id]);

			/**
			 * Invoice & Receipt Data
			 */
			$this->load->model('ac_invoice_model');
			$first_invoice = $this->ac_invoice_model->first_invoice($record->id);

			/**
			 * Generate Dynamic HTML for Schedule
			 */
			$data = [
				'record' 				=> $record,
				'endorsement_record' 	=> $endorsement_record,
				'creditors' 			=> $creditors,
				'first_invoice' 		=> $first_invoice
			];
			$html = $this->load->view( $schedule_view, $data, TRUE);


			try {

				// Save PDF
				_POLICY__schedule_pdf( $record, 'save', $html );

				// Rendr PDF on Browser
				render_pdf($file_full_path);
			}
			catch (Exception $e) {

				return $this->template->json([
					'status' => 'error',
					'message' => $e->getMessage()
				], 404);
			}
		}
		exit(0);
    }


    // --------------------------------------------------------------------
	//  POLICY STATUS UPGRADE/DOWNGRADE
	// --------------------------------------------------------------------

	/**
	 * Upgrade/Downgrade Status of a Policy
	 *
	 * @param integer $id Policy ID
	 * @param char $to_status_code Status Code
	 * @return json
	 */
	public function status($id, $to_status_code)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->policy_model->get($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		/**
		 * Belongs to Me? i.e. My Branch? OR Terminate
		 */
		belongs_to_me($record->branch_id);

		/**
		 * Check Permission
		 * -----------------
		 * You need to have permission to modify the given status.
		 */
		$this->__check_status_permission($to_status_code, $record);


		/**
		 * Meet the Status Pre-Requisite ?
		 */
		$this->__status_qualifies($to_status_code, $record);


		/**
		 * Let's Update the Status
		 */
		try {

			if( $this->policy_model->update_status($record, $to_status_code) )
			{

				/**
				 * @TODO: Post Status Update Tasks
				 * example send SMS on policy activation etc ...
				 */

				/**
				 * Update View
				 */
				$record = $this->policy_model->get($id);
				$view = 'policies/tabs/_tab_overview';

				/**
				 * Load Portfolio Specific Helper File
				 */
				try { load_portfolio_helper($record->portfolio_id);} catch (Exception $e) {
					return $this->template->json([ 'status' => 'error', 'message' => $e->getMessage()], 404);
				}


				/**
				 * Get the Policy Fresh/Renewal Txn Record
				 */
				try {

					$endorsement_record = $this->endorsement_model->get_first( $record->id);
				} catch (Exception $e) {

					return $this->template->json([ 'status' => 'error', 'message' => $e->getMessage() ], 404);
				}

				/**
				 * Beema Samiti Report Headings
				 */
				$this->load->model('rel_policy_bsrs_heading_model');
				$this->load->model('rel_policy_creditor_model');
				$view_data = [
					'record' 				=> $record,
					'endorsement_record' 	=> $endorsement_record,
					'bsrs_headings_policy' 	=> $this->rel_policy_bsrs_heading_model->by_policy($record->id),
					'creditors' 			=> $this->rel_policy_creditor_model->rows(['REL.policy_id' => $record->id])
				];
				$html = $this->load->view($view, $view_data, TRUE);
				$ajax_data = [
					'message' 	=> 'Successfully Updated!',
					'status'  	=> 'success',
					'multipleUpdate' => [
						[
							'box' 		=> '#tab-policy-overview-inner',
							'method' 	=> 'replaceWith',
							'html' 		=> $html
						],
						[
							'box' 		=> '#page-title-policy-code',
							'method' 	=> 'html',
							'html' 		=> $record->code
						]
					]
				];
				return $this->template->json($ajax_data);
			}

		} catch (Exception $e) {

			return $this->template->json([
				'status' 	=> 'error',
				'message' 	=> $e->getMessage()
			], 400);
		}

		return $this->template->json([
			'status' 	=> 'error',
			'message' 	=> 'Could not be updated!'
		], 400);
	}

	// --------------------------------------------------------------------

		/**
		 * Check Status up/down permission
		 *
		 * @param alpha $to_updown_status Status Code to UP/DOWN
		 * @param object $record Policy Record
		 * @return mixed
		 */
		private function __check_status_permission($to_updown_status, $record)
		{
			/**
			 * Check Permission
			 * ------------------------------
			 *
			 * You need to have permission to modify the given status.
			 * Plus, you need to have some pre-requisite before you
			 * upgrade an status
			 *
			 * Case 1: Send To Verify
			 * 		- Check if premium info is not NULL
			 *
			 * Case 2: Verify
			 * 		- Check if premium info is not NULL
			 */

			$status_keys = array_keys(_POLICY_status_dropdown(FALSE));

			// Valid Status Code?
			if( !in_array($to_updown_status, $status_keys ) )
			{
				return $this->template->json([
					'status' 	=> 'error',
					'message' 	=> 'Invalid Status Code!'
				], 403);
			}

			// Valid Permission?
			$__flag_valid_permission = FALSE;
			$permission_name 	= '';
			switch ($to_updown_status)
			{
				case IQB_POLICY_STATUS_DRAFT:
					$permission_name = 'status.to.draft';
					break;

				case IQB_POLICY_STATUS_VERIFIED:
					$permission_name = 'status.to.verified';
					break;

				case IQB_POLICY_STATUS_ACTIVE:
					$permission_name = 'status.to.active';
					break;

				case IQB_POLICY_STATUS_CANCELED:
					$permission_name = 'status.to.cancel';
					break;

				default:
					break;
			}
			if( $permission_name !== ''  && $this->dx_auth->is_authorized('policies', $permission_name) )
			{
				$__flag_valid_permission = TRUE;
			}

			if( !$__flag_valid_permission )
			{
				$this->dx_auth->deny_access();
			}

			return $__flag_valid_permission;
		}

		// --------------------------------------------------------------------

		/**
		 * Status Qualifies to UP/DOWN
		 *
		 * @param alpha $to_updown_status Status Code to UP/DOWN
		 * @param object $record Policy Record
		 * @param bool $terminate_on_fail Terminate right here on fails
		 * @return mixed
		 */
		private function __status_qualifies($to_updown_status, $record, $terminate_on_fail = TRUE)
		{
			/**
			 * Qualifies the status ladder?
			 */
			$__flag_passed 	= $this->policy_model->status_qualifies($record->status, $to_updown_status);
			$failed_message = 'Status does not qualifies to upgrade/downgrade.';

			/**
			 *  You can not manually update/downgrade the following status
			 * 		active, expired
			 */
			if(
				$__flag_passed === TRUE
				&&
				!in_array($to_updown_status, [
					IQB_POLICY_STATUS_ACTIVE,
					IQB_POLICY_STATUS_CANCELED,
					IQB_POLICY_STATUS_EXPIRED
				])
			)
			{
				$failed_message = 'No manually status update/downgrade to supplied status is not allowed.';
			}

			/**
			 * Get the Current Txn Record
			 */
			$endorsement_record = $__flag_passed === TRUE
									? $this->endorsement_model->get_first($record->id) : NULL;

			/**
			 * Premium Must be Updated Before Verifying
			 */
			if(
				$__flag_passed === TRUE
					&&
				( $record->status === IQB_POLICY_STATUS_DRAFT && $to_updown_status === IQB_POLICY_STATUS_VERIFIED )
			)
			{

				/**
				 * Case 0: Backdate Check
				 */
				try {
					backdate_process($record->start_date);
            		backdate_process($record->issued_date);
				} catch (Exception $e) {
					return $this->template->json([
						'status' 	=> 'error',
						'title' 	=> 'Exception Occured!!',
						'message' 	=> $e->getMessage()
					], 400);
				}

				/**
				 * Case 0.1: Creditor Setup?
				 */
				if($record->flag_on_credit == IQB_FLAG_YES)
				{
					$this->load->model('rel_policy_creditor_model');
					if( !$this->rel_policy_creditor_model->check_duplicate(['policy_id' => $record->id]) )
					{
						$__flag_passed 		= FALSE;
						$failed_message 	= 'Please Update "Policy Creditor (Bank/Finance) Information" First!';
					}
				}


				/**
				 * Case 1: Premium Must be Updated
				 */
				if( $__flag_passed && !$endorsement_record->net_amt_basic_premium )
				{
					$__flag_passed 		= FALSE;
					$failed_message 	= 'Please Update "Policy Premium" First!';
				}

				/**
				 * Case 2: Beema Samiti Reporting Information Must be Updated
				 */
				else
				{

					/**
					 * !!! NOTE !!!
					 *
					 * Agriculture Portfolios - NOT Required
					 */
					if( !in_array( $record->portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__AGR) ) )
					{
						$this->load->model('rel_policy_bsrs_heading_model');
						$rel_exists = $this->rel_policy_bsrs_heading_model->rel_exists($record->id);

						if(!$rel_exists)
						{
							$__flag_passed 	= FALSE;
							$failed_message = 'Please Update "Beema Samiti Reporting Information" First!';
						}
					}
				}
			}


			/**
			 * !!! You can not downgrade status from "Verified" if TXN has been  "RI-Approved" or "Vouchered"
			 */
			if(
				$__flag_passed === TRUE
					&&
				( $record->status === IQB_POLICY_STATUS_VERIFIED  && $to_updown_status === IQB_POLICY_STATUS_DRAFT )
					&&
				in_array($endorsement_record->status, [IQB_ENDORSEMENT_STATUS_RI_APPROVED, IQB_ENDORSEMENT_STATUS_VOUCHERED] )
			)
			{
				$__flag_passed 		= FALSE;
				$failed_message 	= 'You cannot downgrade policy status once you have "RI-Approved" or "Voucher Generated"!';
			}

			/**
			 * Terminate Right here if Failed
			 */
			if( !$__flag_passed && $terminate_on_fail )
			{
				return $this->template->json([
					'status' 	=> 'error',
					'title' 	=> 'Invalid Status Transaction',
					'message' 	=> $failed_message
				], 400);
			}

			return $__flag_passed;
		}
}