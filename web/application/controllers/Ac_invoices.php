<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Invoices Controller
 *
 *
 * @category 	Accounting
 */

// --------------------------------------------------------------------

class Ac_invoices extends MY_Controller
{
	function __construct()
	{
		parent::__construct();

		// Form Validation
		$this->load->library('Form_validation');

		// Set Template for this controller
        $this->template->set_template('dashboard');

        // Basic Data
        $this->data['site_title'] = 'Invoices';

        // Setup Navigation
		$this->active_nav_primary([
			'level_0' => 'accounting',
			'level_1' => 'ac_invoices'
		]);

		// Load Model
		$this->load->model('ac_invoice_model');
		$this->load->model('ac_invoice_detail_model');

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
		$this->dx_auth->is_authorized('ac_invoices', 'explore.invoice', TRUE);

		// dom data
		$dom_data = [
			'DOM_DataListBoxId'	=> '_iqb-data-list-box-ac-invoice', 	// List box ID
			'DOM_FilterFormId'	=> '_iqb-filter-form-ac-invoice', 		// Filter Form ID
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

		$records 	= $this->ac_invoice_model->rows($params);
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
			'records' => $records,
			'next_id' => $next_id,
			'next_url' => $next_id ? site_url( 'ac_invoices/page/r/' . $next_id ) : NULL
		] + $dom_data;

		/**
		 * Find View
		 */
		if($layout === 'f') // Full Layout
		{
			$view = 'accounting/invoices/_index';

			$data = array_merge($data, [
				'filters' 		=> $this->_get_filter_elements(),
				'filter_url' 	=> site_url('ac_invoices/page/l/' )
			]);
		}
		else if($layout === 'l')
		{
			$view = 'accounting/invoices/_list';
		}
		else
		{
			$view = 'accounting/invoices/_rows';
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
						'accounting/invoices/_index_header',
						['content_header' => 'Manage Invoices'] + $data)
					->partial('content', 'accounting/invoices/_index', $data)
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
		            'label' => 'Invoice Start Date',
		            'rules' => 'trim|valid_date',
		            '_type'     => 'date',
		            '_required' => false
		        ],
		        [
		            'field' => 'filter_end_date',
		            'label' => 'Invoice End Date',
		            'rules' => 'trim|valid_date',
		            '_type'     => 'date',
		            '_required' => false
		        ],
	            [
					'field' => 'filter_keywords',
			        'label' => 'Invoice Code',
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
		$this->dx_auth->is_authorized('ac_invoices', 'explore.invoice', TRUE);

		$policy_id 	= (int)$policy_id;
		$this->ac_invoice_model->clear_cache();
		$records = $this->ac_invoice_model->rows_by_policy($policy_id);
		// echo $this->db->last_query();exit;
		$data = [
			'records' 					=> $records,
			'policy_id' 				=> $policy_id,
			'next_id' 					=> NULL
		];
		// echo '<pre>'; print_r($data);exit;
		$html = $this->load->view('accounting/invoices/_policy/_list_widget', $data, TRUE);
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
		$this->dx_auth->is_authorized('ac_invoices', 'explore.invoice', TRUE);

		$policy_id = $policy_id ? (int)$policy_id : NULL;
		$cache_var = $policy_id ? 'ac_invoice_list_by_policy_' . $policy_id : NULL;

		$this->ac_invoice_model->clear_cache($cache_var);

		$ajax_data = $this->by_policy($policy_id, TRUE);
		$json_data = [
			'status' 	=> 'success',
			'message' 	=> 'Successfully flushed the cache.',
			'reloadRow' => true,
			'rowId' 	=> '#list-widget-policy-invoices',
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
				'title' => '@TODO - Do we need manual invoicing???',
				'status' => 'error',
				'message' => 'Talk with accounting personnels and implement this feature if needed.'
			], 404);

		/**
		 * Check Permissions? OR Deny on Fail
		 */
		$this->dx_auth->is_authorized('ac_invoices', 'add.invoice', TRUE);

		$record = NULL;

		// Form Submitted? Save the data
		$json_data = $this->_save('add');


		// No form Submitted?
		$json_data['form'] = $this->load->view('accounting/invoices/_form',
			[
				'form_elements' 		=> $this->ac_invoice_model->validation_rules,
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

			$rules = $this->ac_invoice_model->validation_rules_formatted();

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

						$done = $this->ac_invoice_model->add($data);

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
					$done = $this->ac_invoice_model->edit($record->id, $data);
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

				$record 		= $this->ac_invoice_model->row($action === 'add' ? $done : $record->id);
				$single_row 	=  'accounting/invoices/_single_row';
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
	 * Callback: Valid Invoice Date?
	 *
	 * Invoice Date must be within the current Quarter.
	 *
	 * @param type $voucher_date
	 * @return type
	 */
	public function _valid_voucher_date($voucher_date)
	{

		return false;

		$start 	= strtotime($this->current_fy_quarter->starts_at);
		$end 	= strtotime($this->current_fy_quarter->ends_at);
		$vdate 	= strtotime($voucher_date);

		if ($vdate >= $start && $vdate <= $end)
		{
			return TRUE;
		}

		$this->form_validation->set_message('_valid_voucher_date', "The \"Invoice Date\" does not fall under Current Quarter({$this->current_fy_quarter->starts_at} - {$this->current_fy_quarter->ends_at}).");

        return FALSE;
	}


	// --------------------------------------------------------------------

	/**
	 * Callback: Valid Invoice Amount
	 *
	 * Compute the Debit Sum and Credit Sum which should be equal.
	 *
	 * @param type $str
	 * @return type
	 */
	public function _valid_voucher_amount($str)
	{
		return false;

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
	 * Delete a Invoice
	 *
	 * You can not delete any Invoice.
	 *
	 * @param integer $id
	 * @return void
	 */
	public function delete($id)
	{
		return $this->template->json([
			'status' => 'error',
			'message' => 'You can not delete a invoice!'
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
		$this->dx_auth->is_authorized('ac_invoices', 'explore.invoice', TRUE);

		/**
		 * Main Record
		 */
    	$id = (int)$id;
		$record = $this->ac_invoice_model->get($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		/**
		 * Check if Belongs to me?
		 */
		belongs_to_me( $record->branch_id );

		/**
		 * Invoice Detail Rows
		 */
		$data = [
			'record' 	=> $record,
			'rows' 		=> $this->ac_invoice_detail_model->rows_by_invoice($record->id)
		];

		// echo '<pre>'; print_r($data);exit;

		$this->data['site_title'] = 'Invoice Details | ' . $record->invoice_code;
		$this->template->partial(
							'content_header',
							'templates/_common/_content_header',
							[
								'content_header' => 'Invoice -' . $record->invoice_code,
								'breadcrumbs' => ['Invoices' => $this->router->class, 'Details' => NULL]
						])
						->partial('content', 'accounting/invoices/_details', $data)
						->render($this->data);

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

	/**
	 * Print Invoice
	 *
	 * @param integer $id  Invoice ID
	 * @return void
	 */
    public function print($id, $action="print")
    {
    	/**
		 * Check Permissions
		 */
		$this->dx_auth->is_authorized('ac_invoices', 'print.invoice', TRUE);

    	/**
		 * Main Record (Complete Invoice)
		 */
    	$id = (int)$id;
		$record = $this->ac_invoice_model->get($id, IQB_FLAG_ON);
		if(!$record)
		{
			$this->template->render_404();
		}

		/**
		 * Check if Belongs to me?
		 */
		belongs_to_me( $record->branch_id );

		/**
		 * Invoice Detail Rows
		 */
		$data = [
			'record' 	=> $record,
			'rows' 		=> $this->ac_invoice_detail_model->rows_by_invoice($record->id)
		];

		_INVOICE__pdf($data, 'print');
    }

	// --------------------------------------------------------------------

    /**
	 * Print Invoice Receipt
	 *
	 * @param integer $id  Invoice ID
	 * @return void
	 */
    public function receipt($id)
    {
    	/**
		 * Check Permissions
		 */
		$this->dx_auth->is_authorized('ac_invoices', 'print.invoice', TRUE);

    	/**
		 * Main Record (Complete Invoice)
		 */
    	$id = (int)$id;
		$invoice_record = $this->ac_invoice_model->get($id, IQB_FLAG_ON);
		if(!$invoice_record)
		{
			$this->template->render_404();
		}

		/**
		 * Check if Belongs to me?
		 */
		belongs_to_me( $invoice_record->branch_id );

		/**
		 * Invoice Detail Rows
		 */
		$this->load->model('ac_receipt_model');
		$data = [
			'record' 			=> $this->ac_receipt_model->find_by(['invoice_id' => $invoice_record->id]),
			'invoice_record' 	=> $invoice_record
		];

		_RECEIPT__pdf($data, 'print');
    }
}















