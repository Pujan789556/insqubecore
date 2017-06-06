<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Vouchers Controller
 *
 *
 * @category 	Accounting
 */

// --------------------------------------------------------------------

class Ac_vouchers extends MY_Controller
{
	function __construct()
	{
		parent::__construct();

		// Form Validation
		$this->load->library('Form_validation');

		// Set Template for this controller
        $this->template->set_template('dashboard');

        // Basic Data
        $this->data['site_title'] = 'Vouchers';

        // Setup Navigation
		$this->active_nav_primary([
			'level_0' => 'accounting',
			'level_1' => 'ac_vouchers'
		]);

		// Load Model
		$this->load->model('ac_account_group_model');
		$this->load->model('ac_voucher_model');

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
		$this->dx_auth->is_authorized('ac_vouchers', 'explore.voucher', TRUE);

		// dom data
		$dom_data = [
			'DOM_DataListBoxId'	=> '_iqb-data-list-box-ac-voucher', 	// List box ID
			'DOM_FilterFormId'	=> '_iqb-filter-form-ac-voucher', 		// Filter Form ID
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

		// @TODO - Only data belonging to me!!!
		$records = $this->ac_voucher_model->rows($params);
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
			'next_id' => $next_id,
			'next_url' => $next_id ? site_url( 'ac_vouchers/page/r/' . $next_id ) : NULL
		] + $dom_data;

		/**
		 * Find View
		 */
		if($layout === 'f') // Full Layout
		{
			$view = 'accounting/vouchers/_index';

			$data = array_merge($data, [
				'filters' 		=> $this->_get_filter_elements(),
				'filter_url' 	=> site_url('ac_vouchers/page/l/' )
			]);
		}
		else if($layout === 'l')
		{
			$view = 'accounting/vouchers/_list';
		}
		else
		{
			$view = 'accounting/vouchers/_rows';
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
						'accounting/vouchers/_index_header',
						['content_header' => 'Manage Vouchers'] + $data)
					->partial('content', 'accounting/vouchers/_index', $data)
					->render($this->data);
	}


		private function _get_filter_elements()
		{
			$this->load->model('branch_model');
			$dropdown_branch 		 = $this->branch_model->dropdown();
			$filters = [
				[
	                'field' => 'filter_branch_id',
	                'label' => 'Branch',
	                'rules' => 'trim|integer|max_length[8]',
	                '_type'     => 'dropdown',
	                '_data'     => IQB_BLANK_SELECT + $dropdown_branch,
	                '_required' => false
	            ],
	            [
	                'field' => 'filter_fiscal_yr_id',
	                'label' => 'Fiscal Year',
	                'rules' => 'trim|integer|max_length[3]',
	                '_type'     => 'dropdown',
	                '_default' 	=> $this->current_fiscal_year->id,
	                '_data'     => IQB_BLANK_SELECT + $this->fiscal_year_model->dropdown(),
	                '_required' => false
	            ],
	            [
	                'field' => 'filter_fy_quarter',
	                'label' => 'Quarter',
	                'rules' => 'trim|integer|exact_length[1]',
	                '_type'     => 'dropdown',
	                '_data'     => IQB_BLANK_SELECT + fiscal_year_quarters_dropdown(),
	                '_required' => false
	            ],
	            [
		            'field' => 'filter_start_date',
		            'label' => 'Voucher Start Date',
		            'rules' => 'trim|valid_date',
		            '_type'     => 'date',
		            '_required' => false
		        ],
		        [
		            'field' => 'filter_end_date',
		            'label' => 'Voucher End Date',
		            'rules' => 'trim|valid_date',
		            '_type'     => 'date',
		            '_required' => false
		        ],
	            [
					'field' => 'filter_keywords',
			        'label' => 'Voucher Code',
			        'rules' => 'trim|max_length[20]',
	                '_type'     => 'text',
	                '_required' => false
				],
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
						'branch_id' 	=> $this->input->post('filter_branch_id') ?? NULL,
						'fiscal_yr_id' 	=> $this->input->post('filter_fiscal_yr_id') ?? NULL,
						'fy_quarter' 	=> $this->input->post('filter_fy_quarter') ?? NULL,
						'start_date' 	=> $this->input->post('filter_start_date') ?? NULL,
						'end_date' 		=> $this->input->post('filter_end_date') ?? NULL,
						'keywords' 		=> $this->input->post('filter_keywords') ?? ''
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
	 * Get all Voucher for Supplied Policy
	 *
	 * @param int $policy_id
	 * @return JSON
	 */
	function by_policy($policy_id)
	{
		/**
		 * Check Permissions? OR Deny on Fail!
		 */
		$this->dx_auth->is_authorized('ac_vouchers', 'explore.voucher', TRUE);

		$policy_id 	= (int)$policy_id;
		// $this->ac_voucher_model->clear_cache();
		$records = $this->ac_voucher_model->rows_by_policy($policy_id);
		$data = [
			'records' 					=> $records,
			'next_id' 					=> NULL
		];
		// echo '<pre>'; print_r($data);exit;
		$html = $this->load->view('accounting/vouchers/_policy/_list_widget', $data, TRUE);
		$ajax_data = [
			'status' => 'success',
			'html'   => $html
		];

		$this->template->json($ajax_data);
	}

	// --------------------------------------------------------------------
	// CRUD FUNCTIONS
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
		/**
		 * Check Permissions
		 */
		$this->dx_auth->is_authorized('ac_vouchers', 'edit.voucher', TRUE);

		// Valid Record ?
		$id = (int)$id;
		$record = $this->ac_voucher_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		/**
		 * Belongs to Me? i.e. My Branch? OR Terminate
		 */
		belongs_to_me($record->branch_id);

		/**
		 * Editable? OR Terminate
		 */
		is_voucher_editable($record);


		$voucher_detail_rows = $this->_voucher_detail_rows($record->id);

		// Form Submitted? Save the data
		$json_data = $this->_save('edit', $record);


		// No form Submitted?
		$json_data['form'] = $this->load->view('accounting/vouchers/_form',
			[
				'form_elements' 		=> $this->ac_voucher_model->validation_rules,
				'record' 				=> $record,
				'voucher_detail_rows' 	=> $voucher_detail_rows
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
	public function add()
	{
		/**
		 * Check Permissions? OR Deny on Fail
		 */
		$this->dx_auth->is_authorized('ac_vouchers', 'add.voucher', TRUE);

		$record = NULL;

		// Form Submitted? Save the data
		$json_data = $this->_save('add');


		// No form Submitted?
		$json_data['form'] = $this->load->view('accounting/vouchers/_form',
			[
				'form_elements' 		=> $this->ac_voucher_model->validation_rules,
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

			$rules = $this->ac_voucher_model->validation_rules_formatted();

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

						$done = $this->ac_voucher_model->add($data);

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
					$done = $this->ac_voucher_model->edit($record->id, $data);
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

				$record 		= $this->ac_voucher_model->row($action === 'add' ? $done : $record->id);
				$single_row 	=  'accounting/vouchers/_single_row';
				$html = $this->load->view($single_row, ['record' => $record], TRUE);
				$ajax_data['updateSectionData'] = [
					'box' 		=> $action === 'add' ? '#search-result-voucher' : '#_data-row-' . $record->id,
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
	 * Callback: Valid Voucher Date?
	 *
	 * Voucher Date must be within the current Quarter.
	 *
	 * @param type $voucher_date
	 * @return type
	 */
	public function _valid_voucher_date($voucher_date)
	{
		$start 	= strtotime($this->current_fy_quarter->starts_at);
		$end 	= strtotime($this->current_fy_quarter->ends_at);
		$vdate 	= strtotime($voucher_date);

		if ($vdate >= $start && $vdate <= $end)
		{
			return TRUE;
		}

		$this->form_validation->set_message('_valid_voucher_date', "The \"Voucher Date\" does not fall under Current Quarter({$this->current_fy_quarter->starts_at} - {$this->current_fy_quarter->ends_at}).");

        return FALSE;
	}


	// --------------------------------------------------------------------

	/**
	 * Callback: Valid Voucher Amount
	 *
	 * Compute the Debit Sum and Credit Sum which should be equal.
	 *
	 * @param type $str
	 * @return type
	 */
	public function _valid_voucher_amount($str)
	{
		$amounts = $this->input->post('amount');
		$debits 	= $amounts['dr'];
		$credits 	= $amounts['cr'];

		$debit_total 	= 0.00;
		$credit_total 	= 0.00;

		// Compute Debit Total
		foreach($debits as $amount)
		{
			$debit_total += $amount;
		}

		// Compute Debit Total
		foreach($credits as $amount)
		{
			$credit_total += $amount;
		}

		$epsilon = 0.00001;
		if( abs($debit_total - $credit_total) < $epsilon )
		{
			return TRUE;
		}

		$this->form_validation->set_message('_valid_voucher_amount', '"Total Debit Amount" must be equal to "Total Credit Amount";');
		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Delete a Voucher
	 *
	 * You can not delete any Voucher.
	 *
	 * @param integer $id
	 * @return void
	 */
	public function delete($id)
	{
		return $this->template->json([
			'status' => 'error',
			'message' => 'You can not delete a voucher!'
		], 404);
	}


	// --------------------------------------------------------------------
	//  DETAILS
	// --------------------------------------------------------------------


    /**
     * View Voucher Details
     *
     * @param integer $id
     * @return void
     */
    public function details($id)
    {
    	/**
		 * Check Permissions
		 */
		$this->dx_auth->is_authorized('ac_vouchers', 'explore.voucher', TRUE);

		/**
		 * Main Record
		 */
    	$id = (int)$id;
		$record = $this->ac_voucher_model->get($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		/**
		 * Check if Belongs to me?
		 */
		belongs_to_me( $record->branch_id );

		/**
		 * Voucher Detail Rows
		 */
		$data = $this->_voucher_detail_rows($record->id);
		$data['record'] = $record;


		$this->data['site_title'] = 'Voucher Details | ' . $record->voucher_code;
		$this->template->partial(
							'content_header',
							'templates/_common/_content_header',
							[
								'content_header' => 'Voucher -' . $record->voucher_code,
								'breadcrumbs' => ['Vouchers' => $this->router->class, 'Details' => NULL]
						])
						->partial('content', 'accounting/vouchers/_details', $data)
						->render($this->data);

    }

    	private function _voucher_detail_rows($voucher_id)
    	{
    		/**
			 * Voucher Details
			 */
			$voucher_rows = $this->ac_voucher_detail_model->rows_by_voucher($voucher_id);
			// echo $this->db->last_query();exit;

			$debit_rows = [];
			$credit_rows = [];
			foreach( $voucher_rows as $row )
			{
				// Account Group Path
				$path = $this->ac_account_group_model->get_path($row->account_group_id);
				$row->acg_path = $path;

				// Party Name
				$row->party_name = $this->_party_name($row->party_type, $row->party_id);

				if( $row->flag_type === IQB_AC_FLAG_DEBIT )
				{
					$debit_rows[] = $row;
				}
				else
				{
					$credit_rows[] = $row;
				}
			}

			return [
				'debit_rows' 	=> $debit_rows,
				'credit_rows' 	=> $credit_rows
			];
    	}

    	private function _party_name($party_type, $party_id)
    	{
    		$party_name = '';
    		if( !$party_type || !$party_id ) return $party_name;

    		// Let's build party name
    		$party_model = '';
    		switch($party_type)
    		{
    			case IQB_AC_PARTY_TYPE_GENERAL:
    				$party_model = 'ac_party_model';
    				break;

				case IQB_AC_PARTY_TYPE_AGENT:
    				$party_model = 'agent_model';
    				break;

				case IQB_AC_PARTY_TYPE_CUSTOMER:
    				$party_model = 'customer_model';
    				break;

				case IQB_AC_PARTY_TYPE_COMPANY:
    				$party_model = 'company_model';
    				break;

				case IQB_AC_PARTY_TYPE_SURVEYOR:
    				$party_model = 'surveyor_model';
    				break;

				default:
					break;
    		}
    		if($party_model)
    		{
    			$this->load->model($party_model);
    			$party_name = $this->{$party_model}->name($party_id);
    		}
    		return $party_name;
    	}

	// --------------------------------------------------------------------

}















