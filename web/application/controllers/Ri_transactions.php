<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * RI Transactions Controller
 *
 *
 * @category 	Accounting
 */

// --------------------------------------------------------------------

class Ri_transactions extends MY_Controller
{
	function __construct()
	{
		parent::__construct();

		// Form Validation
		$this->load->library('Form_validation');

		// Set Template for this controller
        $this->template->set_template('dashboard');

        // Basic Data
        $this->data['site_title'] = 'RI Transactions';

        // Setup Navigation
		$this->active_nav_primary([
			'level_0' => 'ri',
			'level_1' => 'ri_transactions'
		]);

		// Load Model
		$this->load->model('ri_transaction_model');
		$this->load->model('ri_fac_registration_model');

	}

	// --------------------------------------------------------------------
	// SEARCH/LIST WIDGET FUNCTIONS
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
	public function page( $layout='f', $next_id = 0,  $do_filter = TRUE )
	{
		/**
		 * Check Permissions? OR Deny on Fail!
		 */
		$this->dx_auth->is_authorized('ri_transactions', 'explore.endorsement', TRUE);

		// dom data
		$dom_data = [
			'DOM_DataListBoxId'	=> '_iqb-data-list-box-ri_transactions', 	// List box ID
			'DOM_FilterFormId'	=> '_iqb-filter-form-ri_transactions', 		// Filter Form ID
		];

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
		$filter_data = $this->_get_filter_data( $do_filter );
		if( $filter_data['status'] === 'success' )
		{
			$params = array_merge($params, $filter_data['data']);
		}

		$records 	= $this->ri_transaction_model->rows($params);
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

		$data = [
			'policy_id' => NULL,
			'records' => $records,
			'next_id' => $next_id,
			'next_url' => $next_id ? site_url( 'ri_transactions/page/r/' . $next_id ) : NULL
		] + $dom_data;

		/**
		 * Find View
		 */
		if($layout === 'f') // Full Layout
		{
			$view = 'ri/transactions/_index';

			$data = array_merge($data, [
				'filters' 		=> $this->_get_filter_elements(),
				'filter_url' 	=> site_url('ri_transactions/page/l/' )
			]);
		}
		else if($layout === 'l')
		{
			$view = 'ri/transactions/_list';
		}
		else
		{
			$view = 'ri/transactions/_rows';
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
						'ri/transactions/_index_header',
						['content_header' => 'Manage RI Transactions'] + $data)
					->partial('content', 'ri/transactions/_index', $data)
					->render($this->data);
	}


		private function _get_filter_elements()
		{
			$filters = [
				[
					'field' => 'filter_policy_id',
			        'label' => 'Policy ID',
			        'rules' => 'trim|integer|max_length[20]',
	                '_type'     => 'text',
	                '_required' => false
				],
	            [
					'field' => 'filter_policy_code',
			        'label' => 'Policy Code',
			        'rules' => 'trim|max_length[40]',
	                '_type'     => 'text',
	                '_required' => false
				],
				[
	                'field' => 'filter_premium_type',
	                'label' => 'Distribution Type',
	                'rules' => 'trim|integer|max_length[1]',
	                '_type'     => 'dropdown',
	                '_data'     => IQB_BLANK_SELECT + IQB_RI_TRANSACTION_PREMIUM_TYPES,
	                '_required' => false
	            ]
			];
			return $filters;
		}

		private function _get_filter_data( $do_filter=TRUE )
		{
			$data = ['status' => 'empty'];

			// Return Empty on do_filter = false (set 'false' by 'add' method)
			if( !$do_filter )
			{
				return $data;
			}
			if( $this->input->post() )
			{
				$rules = $this->_get_filter_elements();
				$this->form_validation->set_rules($rules);
				if( $this->form_validation->run() )
				{
					$data['data'] = [
						'policy_id' 	=> $this->input->post('filter_policy_id') ?? NULL,
						'policy_code' 	=> $this->input->post('filter_policy_code') ?? NULL,
						'premium_type' 	=> $this->input->post('filter_premium_type') ?? NULL
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
	 * Get all Invoice for Supplied Policy
	 *
	 * @param int $policy_id
	 * @param bool $data_only Return Data Only
	 * @return JSON
	 */
	function by_policy($policy_id, $data_only = FALSE)
	{
		/**
		 * Check Permissions? OR Deny on Fail!
		 */
		$this->dx_auth->is_authorized('ri_transactions', 'explore.endorsement', TRUE);

		$policy_id 	= (int)$policy_id;
		$records = $this->ri_transaction_model->rows_by_policy($policy_id);
		$data = [
			'records' 					=> $records,
			'policy_id' 				=> $policy_id,
			'next_id' 					=> NULL
		];
		// echo '<pre>'; print_r($data);exit;
		$html = $this->load->view('ri/transactions/_policy/_list_widget', $data, TRUE);
		$ajax_data = [
			'status' => 'success',
			'html'   => $html
		];

		// Return if Ajax Data Only is Set
		if($data_only) return $ajax_data;

		$this->template->json($ajax_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Flush Cache - Per Policy
	 *
	 * @param type $policy_id
	 * @return type
	 */
	public function flush_by_policy($policy_id)
	{
		/**
		 * Check Permissions
		 */
		$this->dx_auth->is_authorized('ri_transactions', 'explore.endorsement', TRUE);

		$policy_id = $policy_id ? (int)$policy_id : NULL;
		$cache_var = $policy_id ? 'ri_txn_list_by_policy_' . $policy_id : NULL;

		$this->ri_transaction_model->clear_cache($cache_var);

		$ajax_data = $this->by_policy($policy_id, TRUE);
		$json_data = [
			'status' 	=> 'success',
			'message' 	=> 'Successfully flushed the cache.',
			'reloadRow' => true,
			'rowId' 	=> '#list-widget-policy-ri_transactions',
			'row' 		=> $ajax_data['html']
		];

		return $this->template->json($json_data);
	}

	// --------------------------------------------------------------------
	// CRUD FUNCTIONS
	// --------------------------------------------------------------------


	/**
	 * Add a new Record
	 *
	 * @return void
	 */
	public function add()
	{
		return $this->template->json([
				'title' => '@TODO - Do we need manual RI Transactions???',
				'status' => 'error',
				'message' => 'Disscuss with RI Department for its feasibility.'
			], 404);

		/**
		 * Check Permissions? OR Deny on Fail
		 */
		$this->dx_auth->is_authorized('ri_transactions', 'add.endorsement', TRUE);

		$record = NULL;

		// Form Submitted? Save the data
		$json_data = $this->_save('add');


		// No form Submitted?
		$json_data['form'] = $this->load->view('ri/transactions/_form',
			[
				'form_elements' 		=> $this->ri_transaction_model->validation_rules,
				'record' 				=> $record,
				'voucher_detail_rows' 	=> NULL
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
		return false;


		// Valid action?
		if( !in_array($action, array('add', 'edit')))
		{
			$this->template->json([
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
			$done = FALSE;

			$rules = $this->ri_transaction_model->validation_rules_formatted();

			$this->form_validation->set_rules($rules);
			if($this->form_validation->run() === TRUE )
        	{
        		$data = $this->input->post();
        		// echo '<pre>'; print_r($data);exit;

        		// Insert or Update?
				if($action === 'add')
				{
					$data['flag_internal'] = IQB_FLAG_OFF;

					try {

						$done = $this->ri_transaction_model->add($data);

					} catch (Exception $e) {

						return $this->template->json([
							'status' => $status,
							'message' => $e->getMessage()
						]);
					}
				}
				else
				{
					// Now Update Data
					$done = $this->ri_transaction_model->edit($record->id, $data);
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
        		$message = validation_errors();

        	}

        	if($status === 'success' )
			{
				$ajax_data = [
					'message' => $message,
					'status'  => $status,
					'updateSection' => true,
					'hideBootbox' => true
				];

				$record 		= $this->ri_transaction_model->row($action === 'add' ? $done : $record->id);
				$single_row 	=  'ri/transactions/_single_row';
				$html = $this->load->view($single_row, ['record' => $record], TRUE);
				$ajax_data['updateSectionData'] = [
					'box' 		=> $action === 'add' ? '#search-result-invoice' : '#_data-row-invoice-' . $record->id,
					'method' 	=> $action === 'add' ? 'prepend' : 'replaceWith',
					'html'		=> $html
				];

				return $this->template->json($ajax_data);
			}
			else
			{
				return $this->template->json([
					'status' => $status,
					'message' => $message
				]);
			}
		}
		return $return_data;
	}



	// --------------------------------------------------------------------

	/**
	 * Delete a record
	 *
	 * You can not delete any record.
	 *
	 * @param integer $id
	 * @return void
	 */
	public function delete($id)
	{
		return $this->template->json([
			'status' => 'error',
			'message' => 'You can not delete a RI Transaction!'
		], 404);
	}


	// --------------------------------------------------------------------
	//  DETAILS
	// --------------------------------------------------------------------


    /**
     * View Invoice Details
     *
     * @param integer $id
     * @return void
     */
    public function details($id)
    {
    	/**
		 * Check Permissions
		 */
		$this->dx_auth->is_authorized('ri_transactions', 'explore.endorsement', TRUE);

		/**
		 * Main Record
		 */
    	$id = (int)$id;
		$record = $this->ri_transaction_model->get($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		/**
		 * Check if Belongs to me?
		 */
		// belongs_to_me( $record->branch_id );

		/**
		 * RI Transaction Detail Rows
		 */
		$data = [
			'record' 	=> $record
		];

		$this->template->json([
			'html' 	=> $this->load->view('ri/transactions/_details', $data, TRUE),
			'title' => 'RI Transaction Details - ' .  $record->id
		]);
    }

	// --------------------------------------------------------------------
	//  FAC MANAGEMENT
	// --------------------------------------------------------------------

	/**
	 * FAC Registration
	 *
	 * Manage FAC Registration for a RI Transaction
	 *
	 * @param integer $id Treaty ID
	 * @return void
	 */
	public function register_fac($id)
	{
		/**
		 * Check Permissions
		 */
		$this->dx_auth->is_authorized('ri_transactions', 'register.fac', TRUE);

		/**
		 * Main Record & Has FAC?
		 */
    	$id 	= (int)$id;
		$record = $this->ri_transaction_model->get($id);
		if(!$record || !$record->si_treaty_fac)
		{
			$this->template->render_404();
		}

		/**
		 * FAC editable? (Must be within current quarter)
		 */
		if( !belong_to_current_fy_quarter($record->fiscal_yr_id, $record->fy_quarter) )
		{
			return $this->template->json([
				'status' => 'error',
				'title'   => 'Action Denied!',
				'message' => 'You can not modify non-current quarter FAC'
			], 403);
		}


		/**
		 * Treaty Distribution
		 */
		$fac_distribution = $this->ri_fac_registration_model->get_fac_by_ri_transaction($id);

		/**
		 * Prepare Form Data
		 */
		$v_rules = $this->ri_fac_registration_model->validation_rules;
		$form_data = [
			'form_elements' 	=> $v_rules,
			'record' 			=> $record,

			// FAC Distribution
			'reinsurers' 		=> $this->company_model->dropdown_reinsurers(),
			'fac_distribution' 	=> $fac_distribution,
		];

		$return_data = [];
		if( $this->input->post() )
		{
			$done 	= FALSE;

            $this->form_validation->set_rules($v_rules);
			if($this->form_validation->run() === TRUE )
        	{
        		$data = $this->input->post();
        		// echo '<pre>'; print_r($data);exit;
        		$done = $this->ri_fac_registration_model->register_fac($record, $data);

        		if($done)
        		{
        			$ajax_data = [
						'message' => 'Successfully Updated FAC Registration.',
						'status'  => 'success',
						'updateSection' => false,
						'hideBootbox' => true
					];
					return $this->template->json($ajax_data);
        		}
        		else
        		{
        			// Simply return could not update message. Might be some logical error or db error.
	        		return $this->template->json([
	                    'status'        => 'error',
	                    'message'       => 'Could not update!'
	                ]);
        		}
        	}
        	else
        	{
    			// Simply Return Validation Error
        		return $this->template->json([
                    'status'        => 'error',
                    'message'       => validation_errors()
                ]);
        	}
		}

		// Prepare HTML Form
		$json_data['form'] = $this->load->view('ri/fac/_form_fac_registration', $form_data, TRUE);

		// Return JSON
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

		/**
		 * Is FAC Editable?
		 *
		 * Conditions for RI Transaction Record:
		 * 		1. Must have FAC SI & FAC Premium
		 * 		2. Must be within current quarter of current fiscal year
		 *
		 */
		private function _is_fac_editable($record)
		{
			if( !$record->si_treaty_fac )
			{
				return FALSE;
			}

			// Check fy and quarter

			// if( $record->fiscal_yr_id == $this->current_fiscal_year->id )

		}

	// --------------------------------------------------------------------

		/**
		 * Callback Validation Function - Check if FAC Distribution is 100%
		 *
		 * @param integer $treaty_type_id
		 * @param integer|null $id
		 * @return bool
		 */
		public function _cb_fac_distribution__complete($str)
		{
			$company_id = $this->input->post('company_id');
			$fac_percent = $this->input->post('fac_percent');

			// Check duplicate Entries
			$unique_count = count( array_unique($company_id) );
			if( $unique_count !== count($company_id) )
			{
				$this->form_validation->set_message('_cb_fac_distribution__complete', 'Reinsurer can not be duplicate.');
	            return FALSE;
			}

			// Lets do the math
			$percent = [];
			$i = 0;
			foreach ($company_id as $rid)
			{
				$percent["$rid"] = $fac_percent[$i++];
			}

			$total = 0;
			foreach($percent as $rid=>$dp)
			{
				$total += (float)$dp;
			}
			$total = (int)$total;

			// 100% ?
	        if( $total != 100 )
	        {
	            $this->form_validation->set_message('_cb_fac_distribution__complete', 'The TOTAL of all %s must be equal to 100.');
	            return FALSE;
	        }
	        return TRUE;
		}

	// --------------------------------------------------------------------

	/**
     * View Invoice Details
     *
     * @param integer $id
     * @return void
     */
    public function preview_fac($id)
    {
    	/**
		 * Check Permissions
		 */
		$this->dx_auth->is_authorized('ri_transactions', 'explore.endorsement', TRUE);

		/**
		 * Main Record & Has FAC?
		 */
    	$id 	= (int)$id;
		$record = $this->ri_transaction_model->get($id);
		if(!$record || !$record->si_treaty_fac)
		{
			$this->template->render_404();
		}


		/**
		 * FAC Distribution
		 */
    	$fac_distribution = $this->ri_fac_registration_model->get_fac_by_ri_transaction($id);

		/**
		 * Check if Belongs to me?
		 */
		// belongs_to_me( $record->branch_id );

		/**
		 * FAC Distribution Detail Rows
		 */
		$data = [
			'fac_distribution' 	=> $fac_distribution,
			'record' 			=> $record
		];

		$this->template->json([
			'html' 	=> $this->load->view('ri/fac/_preview', $data, TRUE),
			'title' => 'FAC Distribution Details'
		]);
    }

}















