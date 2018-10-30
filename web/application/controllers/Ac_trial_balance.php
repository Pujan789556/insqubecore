<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Trial Balance Controller
 *
 *
 * @category 	Accounting
 */

// --------------------------------------------------------------------

class Ac_trial_balance extends MY_Controller
{
	function __construct()
	{
		parent::__construct();

		// Form Validation
		$this->load->library('Form_validation');

		// Set Template for this controller
        $this->template->set_template('dashboard');

        // Basic Data
        $this->data['site_title'] = 'Trial Balance';

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
		$this->dx_auth->is_authorized('ac_trial_balance', 'explore.trial.balance', TRUE);

		$data = [
			'DOM_DataListBoxId'	=> '_iqb-data-list-box-ac_trial_balance', 	// List box ID
			'DOM_FilterFormId'	=> '_iqb-filter-form-ac_trial_balance', 		// Filter Form ID
			'filters' 			=> $this->_get_filter_elements(),
			'filter_url' 		=> site_url($this->router->class . '/filter/'),
			'print_url' 		=> site_url($this->router->class . '/filter/1/')
		];

		$this->template
					->set_layout('layout-advanced-filters')
					->partial(
						'content_header',
						'accounting/trial_balance/_index_header',
						['content_header' => 'Manage Trial Balance'] + $data)
					->partial('content', 'accounting/trial_balance/_index', $data)
					->partial('dynamic_js', 'accounting/trial_balance/_js')
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
		$this->dx_auth->is_authorized('ac_trial_balance', 'explore.trial.balance', TRUE);


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
		 * Compute Data
		 */
		try {
			$data = $this->_trial_balance_data($params);
		} catch (Exception $e) {
			return $this->template->json([
				'status' => 'error',
				'title'  => 'Exception Occured!',
				'message' => $e->getMessage()
			], 404);
		}



		/**
		 * Print or Display Result
		 */
		if( $print == '1')
		{
			return $this->_print($data);
		}


		$view = 'accounting/trial_balance/_list';
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
		$view = 'accounting/trial_balance/print/trial_balance';
		$data['mode'] = 'print';
		$html = $this->load->view($view, $data, TRUE);

		$title = $data['trial_balance_title'];

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
        $mpdf->Output($filename, 'D');
	}

	// --------------------------------------------------------------------


		private function _trial_balance_data($params)
		{
			$fiscal_yr_id = $params['fiscal_yr_id'];

			// Fiscal Year Record
			$fy_record 	= $this->fiscal_year_model->get($fiscal_yr_id);

			if(!$fy_record)
			{
				throw new Exception("Exception [Method: _trial_balance_data()]: No Fiscal Year Record Found.<br/><br/>Please ask administrator to setup this data.");
			}

			// Get the goodies
			$goodies = $this->_filter_goodies($params);

			$query_params = implode(' AND ', $goodies['query_params']);
			$sql = "SELECT
						AC.id AS account_id, AC.name AS account_name,
						OB.ob_dr, OB.ob_cr, OB.ob_balance,
						DR.dr_txn_total, CR.cr_txn_total
						FROM ac_accounts AC
						LEFT JOIN (
							SELECT OBI.account_id, OBI.fiscal_yr_id, SUM(OBI.dr) AS ob_dr, SUM(OBI.cr) AS ob_cr, SUM(OBI.balance) AS ob_balance
							FROM ac_opening_balances OBI
							WHERE OBI.fiscal_yr_id = '{$fiscal_yr_id}'
							GROUP BY OBI.account_id, OBI.fiscal_yr_id
						) OB ON OB.account_id = AC.id AND OB.fiscal_yr_id = '{$fiscal_yr_id}'
						LEFT JOIN (
							SELECT VD.account_id, SUM(VD.amount) AS dr_txn_total
							FROM ac_voucher_details AS VD
							LEFT JOIN ac_vouchers V ON V.id = VD.voucher_id
							WHERE
								{$query_params} AND
								VD.flag_type = 'D'
							GROUP BY VD.account_id
						) DR ON DR.account_id = AC.id
						LEFT JOIN (
							SELECT VD.account_id, SUM(VD.amount) AS cr_txn_total
							FROM ac_voucher_details AS VD
							LEFT JOIN ac_vouchers V ON V.id = VD.voucher_id
							WHERE
								{$query_params} AND
								VD.flag_type = 'C'
							GROUP BY VD.account_id
						) CR ON CR.account_id = AC.id;";

			$records = $this->db->query($sql)->result();

			/**
			 * Trial Balance Title Postfix - Branch Name ??
			 */
			$trial_balance_title = ['TRIAL BALANCE'];
			// Branch Info
			$branch_id = $params['branch_id'];
			if($branch_id)
			{
				$dropdown_branch 	= $this->branch_model->dropdown('en');
				$trial_balance_title[] 	= '[' . $dropdown_branch[$branch_id] . ']';
			}
			$trial_balance_title = implode(' ', $trial_balance_title);

			return [
				'records' 				=> $records,
				'ledger_dates' 			=> $goodies['ledger_dates'],
				'trial_balance_title' 	=> $trial_balance_title
			];
		}

		private function _filter_goodies($params)
		{

			$fiscal_yr_id 	= $params['fiscal_yr_id'];
			$fy_record 		= $this->fiscal_year_model->get($fiscal_yr_id);


			/**
			 * Transaction Balance
			 * 		From 	= Fiscal Year Start Date
			 * 		To 		= Filter End Date | Quarter End Date | Month End Date
			 *
			 */
			$flag_on = IQB_FLAG_ON;
			$query_params = [
				"V.flag_complete = '{$flag_on}'",
				"V.fiscal_yr_id = '{$fiscal_yr_id}'",
			];


			$ledger_dates['from'] = $fy_record->starts_at_en;

			if( $params['fy_duration_type'] == IQB_REPORT_TYPE_QUARTELRY && !empty($params['fy_quarter_month']) )
			{
				// Transaction  Date Range
				$quarter = $params['fy_quarter_month'];
				$query_params[] = "V.fy_quarter = '{$quarter}'";

				// Get Quarter Record
				$this->load->model('fy_quarter_model');
				$qtr_record = $this->fy_quarter_model->get_by_fiscal_year_quarter($fiscal_yr_id, $params['fy_quarter_month']);
				if($qtr_record)
				{
					// Ledger Dates
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
					// Transaction Date Range
					$to = $fy_month_record->ends_at;
					$query_params[] = "V.voucher_date <= '{$to}'"; // till end of the month

					// Ledger Dates
					$ledger_dates['to'] 	= $fy_month_record->ends_at;
				}
				else
				{
					throw new Exception("Exception [Method: _filter_goodies()]: No Month Record Found for supplied Fiscal Year and Month.<br/><br/>Please ask administrator to setup this data.");
				}
			}
			else if( !empty($params['end_date']) )
			{
				// End Date should not exceed fiscal year end date
				if( strtotime($params['end_date']) <= strtotime($fy_record->ends_at_en))
				{
					// Transaction Date Range
					$to = $params['end_date'];
					$query_params[] = "V.voucher_date <= '{$to}'";

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
				// Transaction
				$branch_id = $params['branch_id'];
				$query_params[] = "V.branch_id = '{$branch_id}'";
			}


			return [
				'query_params' => $query_params,
				'ledger_dates' => $ledger_dates
			];
		}


		private function _get_filter_elements($formatted = FALSE)
		{
			$this->load->model('branch_model');
			$dropdown_branch 		= $this->branch_model->dropdown();

			$filters = [
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
		            '_type'     => 'text',
		            '_default' 	=> $this->current_fiscal_year->starts_at_en,
		            '_extra_attributes' => 'readonly="readonly"',
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
				$rules = $this->_get_filter_elements(TRUE);
				$this->form_validation->set_rules($rules);
				if( $this->form_validation->run() )
				{
					$data['data'] = [
						'fiscal_yr_id' 		=> $this->input->post('filter_fiscal_yr_id') ?? NULL,
						'branch_id' 		=> $this->input->post('filter_branch_id') ?? NULL,
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















