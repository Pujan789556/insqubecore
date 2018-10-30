<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Ledger Controller
 *
 *
 * @category 	Accounting
 */

// --------------------------------------------------------------------

class Ac_ledgers extends MY_Controller
{
	function __construct()
	{
		parent::__construct();

		// Form Validation
		$this->load->library('Form_validation');

		// Set Template for this controller
        $this->template->set_template('dashboard');

        // Basic Data
        $this->data['site_title'] = 'Ledgers';

        // Setup Navigation
		$this->active_nav_primary([
			'level_0' => 'accounting',
			'level_1' => 'reports',
			'level_2' => $this->router->class
		]);

		// Load Model
		$this->load->model('ac_account_group_model');
		$this->load->model('ac_account_model');
		$this->load->model('ac_voucher_model');
		$this->load->model('ac_opening_balance_model');
	}

	// --------------------------------------------------------------------
	// SEARCH/LIST WIDGET FUNCTIONS
	// --------------------------------------------------------------------

	/**
	 * Default Index Method
	 *
	 * @return void
	 */
	public function index( )
	{
		/**
		 * Check Permissions? OR Deny on Fail!
		 */
		$this->dx_auth->is_authorized('ac_ledgers', 'explore.ledger', TRUE);

		$data = [
			'DOM_DataListBoxId'	=> '_iqb-data-list-box-ac_ledgers', 	// List box ID
			'DOM_FilterFormId'	=> '_iqb-filter-form-ac_ledgers', 		// Filter Form ID
			'filters' 			=> $this->_get_filter_elements(),
			'filter_url' 		=> site_url($this->router->class . '/filter/'),
			'print_url' 		=> site_url($this->router->class . '/filter/1/')
		];

		$this->template
					->set_layout('layout-advanced-filters')
					->partial(
						'content_header',
						'accounting/ledgers/_index_header',
						['content_header' => 'Manage Ledgers'] + $data)
					->partial('content', 'accounting/ledgers/_index', $data)
					->partial('dynamic_js', 'accounting/ledgers/_js')
					->render($this->data);
	}

	// --------------------------------------------------------------------

	/**
	 * Get Ledger Details
	 *
	 * @return void
	 */
	public function filter( $print = 0 )
	{
		/**
		 * Check Permissions? OR Deny on Fail!
		 */
		$this->dx_auth->is_authorized('ac_ledgers', 'explore.ledger', TRUE);


		$params = array();

		/**
		 * Extract Filter Elements
		 */
		$filter_data = $this->_get_filter_data( TRUE );
		if( $filter_data['status'] === 'success' )
		{
			$params = array_merge($params, $filter_data['data']);
		}

		/**
		 * Account Details
		 */
		$record = $this->ac_account_model->find($params['account_id']);
		if(!$record)
		{
			$this->template->render_404();
		}


		/**
		 * Compute Data
		 */
		try {
			$data = $this->_ledger_data($params);
		} catch (Exception $e) {
			return $this->template->json([
				'status' => 'error',
				'title'  => 'Exception Occured!',
				'message' => $e->getMessage()
			], 404);
		}
		$data['record'] = $record;


		/**
		 * Print or Display Result
		 */
		if( $print == '1')
		{
			return $this->_print($data);
		}


		$view = 'accounting/ledgers/_list';
		$html = $this->load->view($view, $data, TRUE);
		$ajax_data = [
			'status' => 'success',
			'html'   =>  $html
		];
		$this->template->json($ajax_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Print a Ledger
	 *
	 * @return html
	 */
	public function _print( $data )
	{
		/**
		 * @TODO:
		 * 	cache the file
		 * 	if exists, render output
		 */
		$view = 'accounting/ledgers/print/ledger';
		$data['mode'] = 'print';
		$html = $this->load->view($view, $data, TRUE);

		$title = "Ledger - " . $data['record']->name . ' ' . $data['ledger_dates']['from'] . ' to ' . $data['ledger_dates']['to'];

        $this->load->library('pdf');
        $mpdf = $this->pdf->load();

        $mpdf->SetMargins(10, 5, 10, 5);
        $mpdf->margin_header = 5;
        $mpdf->margin_footer = 5;
        $mpdf->SetProtection(array('print'));
        $mpdf->SetTitle($title);
        $mpdf->SetAuthor($this->settings->orgn_name_en);

        $mpdf->SetDisplayMode('fullpage');

        $mpdf->WriteHTML($html);

        $filename = $title . '.pdf';
        $mpdf->Output($filename, 'I');
	}

	// --------------------------------------------------------------------


		private function _ledger_data($params)
		{
			$fiscal_yr_id 	= $params['fiscal_yr_id'];
			$account_id 	= $params['account_id'];

			// Party Info
			$party_type = $params['party_type'];
			$party_id 	= $params['party_id'];

			// Fiscal Year Record
			$fy_record 	= $this->fiscal_year_model->get($fiscal_yr_id);
			if(!$fy_record)
			{
				throw new Exception("Exception [Method: _ledger_data()]: No Fiscal Year Record Found.<br/><br/>Please ask administrator to setup this data.");
			}

			// Opening Balance
			$ob_record = $this->ac_opening_balance_model->by_account_fiscal_yr($account_id, $fiscal_yr_id, $party_type, $party_id);


			// Get the goodies
			$goodies = $this->_filter_goodies($params);

			/**
			 * Compute Balance Forward Aggregate
			 */
			$bf_where = $goodies['query_params']['bf'];
			$bf_where['VD.flag_type'] = IQB_AC_FLAG_DEBIT;
			$bf_debit = $this->db->select("SUM(VD.amount) AS dr_total")
								->from('ac_voucher_details AS VD')
								->join('ac_vouchers V', 'V.id = VD.voucher_id')
								->where($bf_where)
								->get()->row();

			$bf_where['VD.flag_type'] = IQB_AC_FLAG_CREDIT;
			$bf_credit = $this->db->select("SUM(VD.amount) AS cr_total")
								->from('ac_voucher_details AS VD')
								->join('ac_vouchers V', 'V.id = VD.voucher_id')
								->where($bf_where)
								->get()->row();



			/**
			 * Compute Transaction Reords
			 */
			$txn_where 	= $goodies['query_params']['txn'];
			$records 	= $this->db->select(

									// Voucher
									"V.id, V.voucher_code, V.voucher_date, V.narration, " .

									// Voucher Details
									"VD.flag_type, VD.amount"
								)
								->from('ac_vouchers AS V')
								->join('ac_voucher_details VD', 'V.id = VD.voucher_id')
								->where($txn_where)
								->get()->result();

			// echo $this->db->last_query();exit;

			$bf_record = (object) [
			    'dr' => $bf_debit->dr_total ?? 0,
			    'cr' => $bf_credit->cr_total ?? 0,
		  	];

		  	// Add Opening Balance
		  	if( isset($ob_record->dr) )
		  	{
		  		$bf_record->dr = $bf_record->dr + (float)$ob_record->dr;
		  	}
		  	if( isset($ob_record->cr) )
		  	{
		  		$bf_record->cr = $bf_record->cr + (float)$ob_record->cr;
		  	}


		  	// Party Name if Any
		  	$party_name = $this->_party_name($params['party_type'], $params['party_id']);
			return [
				'bf_record' 	=> $bf_record,
				'records' 		=> $records,
				'ledger_dates' 	=> $goodies['ledger_dates'],
				'party_name' 	=> $party_name
			];
		}

		private function _filter_goodies($params)
		{

			$account_id 	= $params['account_id'];
			$fiscal_yr_id 	= $params['fiscal_yr_id'];
			$fy_record 		= $this->fiscal_year_model->get($fiscal_yr_id);


			/**
			 * Balance Forward Dates
			 * 		From 	= Fiscal Year Start Date
			 * 		To 		= Filter Start Date | Quarter Start Date | Month  Start Date
			 *
			 * Transaction Dates
			 * 		From 	= Filter Start Date | Quarter Start Date | Month Start Date
			 * 		To 		= Filter End Date | Quarter End Date | Month End Date
			 */
			$query_params['bf'] 	= [
				'V.fiscal_yr_id'  	=> $fiscal_yr_id,
				'V.flag_complete' 	=> IQB_FLAG_ON,
				'VD.account_id' 	=> $account_id
			];
			$query_params['txn'] 	= [
				'V.fiscal_yr_id'  	=> $fiscal_yr_id,
				'V.flag_complete' 	=> IQB_FLAG_ON,
				'VD.account_id' 	=> $account_id
			];

			$ledger_dates = [];

			if( $params['fy_duration_type'] == IQB_REPORT_TYPE_QUARTELRY && !empty($params['fy_quarter_month']) )
			{
				// Balance Forward Date Range
				$query_params['bf']['V.fy_quarter'] = $params['fy_quarter_month'];

				// Transaction  Date Range
				$query_params['txn']['V.fy_quarter'] = $params['fy_quarter_month'];

				// Get Quarter Record
				$this->load->model('fy_quarter_model');
				$qtr_record = $this->fy_quarter_model->get_by_fiscal_year_quarter($fiscal_yr_id, $params['fy_quarter_month']);
				if($qtr_record)
				{
					// Ledger Dates
					$ledger_dates['from'] = $qtr_record->starts_at;
					$ledger_dates['to'] = $qtr_record->ends_at;
				}
				else
				{
					throw new Exception("Exception [Method: _filter_goodies()]: No Quarter Record Found for supplied Fiscal Year and Quarter.<br/><br/>Please ask administrator to setup this data.");
				}


			}
			else if( $params['fy_duration_type'] == IQB_REPORT_TYPE_MONTHLY && !empty($params['fy_quarter_month']) )
			{
				// Get Date Range from That Month
				$this->load->model('fy_month_model');
				$fy_month_record = $this->fy_month_model->get_by_fy_month($fiscal_yr_id, $params['fy_quarter_month']);

				if($fy_month_record)
				{
					// Balance Forward Date Range
					$query_params['bf']['V.voucher_date >='] = $fy_record->starts_at_en;
					$query_params['bf']['V.voucher_date <'] = $fy_month_record->starts_at;

					// Transaction Date Range
					$query_params['txn']['V.voucher_date >='] = $fy_month_record->starts_at;
					$query_params['txn']['V.voucher_date <='] = $fy_month_record->ends_at;

					// Ledger Dates
					$ledger_dates['from'] 	= $fy_month_record->starts_at;
					$ledger_dates['to'] 	= $fy_month_record->ends_at;
				}
				else
				{
					throw new Exception("Exception [Method: _filter_goodies()]: No Month Record Found for supplied Fiscal Year and Month.<br/><br/>Please ask administrator to setup this data.");
				}
			}
			else if( !empty($params['start_date']) )
			{
				// Balance Forward Date Range
				$query_params['bf']['V.voucher_date >='] = $fy_record->starts_at_en;;
				$query_params['bf']['V.voucher_date <'] = $params['start_date'];

				// Transaction Date Range
				$query_params['txn']['V.voucher_date >='] = $params['start_date'];

				// Ledger Dates
				$ledger_dates['from'] 	= $params['start_date'];

				// End Date should not exceed fiscal year end date
				if( !empty($params['end_date']) && strtotime($params['end_date']) <= strtotime($fy_record->ends_at_en))
				{
					$query_params['txn']['V.voucher_date <='] = $params['end_date'];

					// Ledger Dates
					$ledger_dates['to'] = $params['end_date'];
				}
				else
				{
					// Ledger Dates
					$ledger_dates['to'] = $fy_record->ends_at_en;
				}
			}

			/**
			 * Branch ID ???
			 */
			if( !empty($params['branch_id']) )
			{
				// Balance
				$query_params['bf']['V.branch_id'] = $params['branch_id'];

				// Transaction
				$query_params['txn']['V.branch_id'] = $params['branch_id'];
			}

			/**
			 * Party ??
			 */
			if( !empty($params['party_type']) && !empty($params['party_id']) )
			{
				// Balance
				$query_params['bf']['VD.party_type'] = $params['party_type'];
				$query_params['bf']['VD.party_id'] = $params['party_id'];

				// Transaction
				$query_params['txn']['VD.party_type'] = $params['party_type'];
				$query_params['txn']['VD.party_id'] = $params['party_id'];
			}

			return [
				'query_params' => $query_params,
				'ledger_dates' => $ledger_dates
			];
		}


		private function _get_filter_elements($formatted = FALSE)
		{
			$this->load->model('branch_model');
			$dropdown_accounts 		= $this->ac_account_model->dropdown();
			$dropdown_branch 		= $this->branch_model->dropdown();
			$dropdown_party_types 	= ac_party_types_dropdown(false);

			$filters = [

				/**
				 * Section I: Account ID, Fiscal Year, Branch
				 */
				'section-1' => [
					[
		                'field' => 'filter_account_id',
		                'label' => 'Account',
		                'rules' => 'trim|required|integer|max_length[11]',
		                '_type'     => 'dropdown',
		                '_id' 		=> 'filter_account_id',
		                '_data'     => IQB_BLANK_SELECT + $dropdown_accounts,
		                '_extra_attributes' => 'style="display:block" data-ddtype="select"',
		                '_required' => false
		            ],
		            [
		                'field' => 'filter_fiscal_yr_id',
		                'label' => 'Fiscal Year',
		                'rules' => 'trim|required|integer|max_length[3]',
		                '_type'     => 'dropdown',
		                '_default' 	=> $this->current_fiscal_year->id,
		                '_data'     => $this->fiscal_year_model->dropdown(),
		                '_required' => false
		            ],
					[
		                'field' => 'filter_branch_id',
		                'label' => 'Branch',
		                'rules' => 'trim|integer|max_length[8]',
		                '_type'     => 'dropdown',
		                '_data'     => IQB_BLANK_SELECT + $dropdown_branch,
		                '_required' => false
		            ],
				],

				/**
				 * Section II: Party Name ( Party Type, Party ID)
				 */
				'section-2' => [
					[
                        'field' => 'filter_party_type',
                        'label' => 'Party Type',
                        'rules' => 'trim|alpha|exact_length[1]|in_list[' . implode(',', array_keys($dropdown_party_types)) . ']',
                        '_type'     => 'dropdown',
                        '_data'     => IQB_BLANK_SELECT + $dropdown_party_types,
                        '_extra_attributes' => 'data-field="party_type" onchange="__reset_party(this)"',
                        '_required' => false
                    ],
                    [
		                'field' => 'filter_party_id',
		                'label' => 'Party ID',
		                'rules' => 'trim|integer|max_length[11]',
		                '_type'     => 'hidden',
		                '_data'     => IQB_BLANK_SELECT + $dropdown_branch,
		                '_required' => false
		            ],
				],

				/**
				 * Section III: Date Filters
				 */
				'section-3' => [
					[
		                'field' => 'filter_type',
		                'label' => 'Report Type',
		                'rules' => 'trim|alpha|exact_length[1]|in_list[' . implode(',', array_keys(IQB_REPORT_TYPES)) . ']',
		                '_type'     => 'dropdown',
		                '_data'     => IQB_BLANK_SELECT + IQB_REPORT_TYPES,
		                '_required' => false
		            ],
		            [
		                'field' => 'filter_fy_quarter_month',
		                'label' => 'Quarter/Month',
		                'rules' => 'trim|integer|max_length[8]',
		                '_type'     => 'dropdown',
		                '_data'     => IQB_BLANK_SELECT,
		                '_required' => false
		            ],
		            [
			            'field' => 'filter_start_date',
			            'label' => 'From Date',
			            'rules' => 'trim|valid_date',
			            '_type'     => 'date',
			            '_default' 	=> $this->current_fiscal_year->starts_at_en,
			            '_required' => false
			        ],
			        [
			            'field' => 'filter_end_date',
			            'label' => 'To Date',
			            'rules' => 'trim|valid_date',
			            '_type'     => 'date',
			            '_default' 	=> date('Y-m-d'),
			            '_required' => false
			        ]
				]
			];

			if(!$formatted)
			{
				return $filters;
			}

			/**
			 * Formatted for Filter Processing
			 */
			$v_rules = [];
			foreach($filters as $section => $rules)
	        {
	            $v_rules = array_merge($v_rules, $rules);
	        }
			return $v_rules;
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
				$rules = $this->_get_filter_elements(TRUE);
				$this->form_validation->set_rules($rules);
				if( $this->form_validation->run() )
				{
					$data['data'] = [
						'account_id' 		=> $this->input->post('filter_account_id') ?? NULL,
						'fiscal_yr_id' 		=> $this->input->post('filter_fiscal_yr_id') ?? NULL,
						'branch_id' 		=> $this->input->post('filter_branch_id') ?? NULL,

						'party_type' 		=> $this->input->post('filter_party_type') ?? NULL,
						'party_id' 		=> $this->input->post('filter_party_id') ?? NULL,

						'fy_duration_type' 	=> $this->input->post('filter_type') ?? NULL,
						'fy_quarter_month' 	=> $this->input->post('filter_fy_quarter_month') ?? NULL,
						'start_date' 		=> $this->input->post('filter_start_date') ?? NULL,
						'end_date' 			=> $this->input->post('filter_end_date') ?? NULL
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
			else
			{
				$data = [
					'status' 	=> 'error',
					'message' 	=> 'Please supply query parameters'
				];

				$this->template->json($data, 403);
			}
			return $data;
		}


	// --------------------------------------------------------------------



	// --------------------------------------------------------------------
	//  DETAILS
	// --------------------------------------------------------------------


    	private function _party_name($party_type=NULL, $party_id=NULL)
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















