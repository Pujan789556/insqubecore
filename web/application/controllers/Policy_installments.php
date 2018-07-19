<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Policy Installment Controller
 *
 * We use this controller to work with the policy premium.
 *
 * This controller falls under "Policy" category.
 *
 * @category 	Policy
 */

// --------------------------------------------------------------------

class Policy_installments extends MY_Controller
{
	/**
	 * Files Upload Path
	 */
	public static $upload_path = INSQUBE_MEDIA_PATH . 'policy_installments/';

	// --------------------------------------------------------------------

	function __construct()
	{
		parent::__construct();

		// Form Validation
		$this->load->library('Form_validation');

		// Set Template for this controller
        $this->template->set_template('dashboard');

        // Basic Data
        $this->data['site_title'] = 'Policy Installment';

        // Setup Navigation
		$this->active_nav_primary([
			'level_0' => 'policies',
		]);

		// Load Models
		$this->load->model('policy_model');
		$this->load->model('policy_installment_model');
		$this->load->model('portfolio_setting_model');
		$this->load->model('object_model');

		// Policy Helpers
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
	 * List all Policy Installment Records for supplied Policy
	 *
	 * @return JSON
	 */
	public function index($policy_id, $data_only = FALSE)
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('policy_installments', 'explore.installment') )
		{
			$this->dx_auth->deny_access();
		}

		$policy_id 		= (int)$policy_id;
		$policy_record 	= $this->policy_model->get($policy_id);
		if(!$policy_record)
		{
			$this->template->render_404();
		}

		$records 	= $this->policy_installment_model->get_many_by_policy($policy_id);

		// $endorsement_record = $this->endorsement_model->get($endorsement_record->id);


		// echo $this->db->last_query();exit;
		$data = [
			'records' 					=> $records,
			'policy_record' 			=> $policy_record
		];
		$html = $this->load->view('policy_installments/_list_widget', $data, TRUE);
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
	public function flush($policy_id=NULL)
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('policy_installments', 'explore.installment') )
		{
			$this->dx_auth->deny_access();
		}

		$policy_id = $policy_id ? (int)$policy_id : NULL;
		$cache_var = $policy_id ? 'ptxi_bypolicy_' . $policy_id : NULL;
		$this->policy_installment_model->clear_cache($cache_var);

		if($policy_id)
		{
			$ajax_data = $this->index($policy_id, TRUE);
			$json_data = [
				'status' => 'success',
				'message' 	=> 'Successfully flushed the cache.',
				'reloadRow' => true,
				'rowId' 	=> '#list-widget-policy_installments',
				'row' 		=> $ajax_data['html']
			];

			return $this->template->json($json_data);
		}

		// Reload Index
		return $this->template->json([
			'status' 	=> 'success',
			'message' 	=> 'Successfully flushed the cache.'
		]);
	}

	// --------------------------------------------------------------------


	/**
	 * Delete a Policy Installment Draft (Non Fresh/Renewal)
	 *
	 * Only Draft Version of a Policy can be deleted.
	 *
	 * @param integer $id
	 * @return json
	 */
	public function delete($id)
	{
		return $this->template->json([
			'status' 	=> 'error',
			'message' 	=> 'You can not delete an installment record.'
		], 403);
	}


	// --------------------------------------------------------------------
	//  POLICY Voucher & Invoice
	// --------------------------------------------------------------------

	/**
	 * Generate Voucher
	 *
	 * @param integer $id Policy TXN ID
	 * @return mixed
	 */
	public function voucher($id)
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('policy_installments', 'generate.policy.voucher') )
		{
			$this->dx_auth->deny_access();
		}

		/**
		 * Get the Policy Fresh/Renewal Txn Record
		 */
		$id 				= (int)$id;
		$installment_record = $this->policy_installment_model->get( $id );
		if(!$installment_record)
		{
			$this->template->render_404();
		}


		/**
		 * Get the transaction record
		 */
		$endorsement_record = $this->endorsement_model->get( $installment_record->endorsement_id );
		if(!$endorsement_record)
		{
			$this->template->render_404();
		}

		// --------------------------------------------------------------------

		/**
		 * Policy Record
		 */
		$policy_record = $this->policy_model->get($installment_record->policy_id);
		if(!$policy_record)
		{
			$this->template->render_404();
		}

		// --------------------------------------------------------------------

		/**
		 * Record Status Authorized to Generate Voucher?
		 */

		$authorized_status = _POLICY_INSTALLMENT__voucher_constraint($installment_record);
		if( !$authorized_status )
		{
			return $this->template->json([
				'title' 	=> 'Unauthorized Installment Status!',
				'status' 	=> 'error',
				'message' 	=> 'This installment does not have authorized status to perform this action.'
			], 404);
		}


		// --------------------------------------------------------------------

		/**
		 * Load voucher models
		 */
		$this->load->model('ac_voucher_model');
		$this->load->model('rel_policy_voucher_model');

		/**
		 * Check if Voucher already generated for this Installment
		 */
		$where = [
			'REL.policy_id' => $installment_record->policy_id,
			'REL.ref' 		=> IQB_REL_POLICY_VOUCHER_REF_PI,
			'REL.ref_id' 	=> $installment_record->id
		];
		if( $this->rel_policy_voucher_model->voucher_exists($where))
		{
			return $this->template->json([
				'title' 	=> 'OOPS!',
				'status' 	=> 'error',
				'message' 	=> 'Voucher already exists for this Installment/Endorsement.'
			], 404);
		}



		// --------------------------------------------------------------------

		/**
		 * Fiscal Year Record
		 */
		$fy_record = $this->fiscal_year_model->get($policy_record->fiscal_yr_id);

		// --------------------------------------------------------------------

		/**
		 * Portfoliio Record
		 */
		$this->load->model('portfolio_model');
		$this->load->model('portfolio_setting_model');
		$portfolio_record = $this->portfolio_model->find($policy_record->portfolio_id);

		// --------------------------------------------------------------------

		/**
		 * Portfolio Setting Record
		 */
		$pfs_record = $this->portfolio_setting_model->get_by_fiscal_yr_portfolio($policy_record->fiscal_yr_id, $policy_record->portfolio_id);
		if( !$pfs_record )
		{
			return $this->template->json([
				'title' 	=> 'Portfolio Setting Missing!',
				'status' 	=> 'error',
				'message' 	=> "Please add portfolio settings for fiscal year({$fy_record->code_np}) for portfolio ({$portfolio_record->name_en})"
			], 404);
		}

		// --------------------------------------------------------------------


		/**
		 * Let's Build Policy Voucher
		 * Voucher Master Data
		 */
		try{
			$voucher_data = $this->_data_voucher_master($policy_record->code, $endorsement_record->txn_type);
		}
		catch (Exception $e) {
			return $this->template->json([ 'status' => 'error', 'title' => 'Exception Occured.','message' => $e->getMessage()], 400);
		}

		// --------------------------------------------------------------------

        /**
         * Build Voucher Details Data
         */
        try{
			$voucher_rows = $this->_data_voucher_details( $installment_record, $endorsement_record, $policy_record, $pfs_record, $portfolio_record );
		}
		catch (Exception $e) {
			return $this->template->json([ 'status' => 'error', 'title' => 'Exception Occured.','message' => $e->getMessage()], 400);
		}

		// echo '<pre>'; print_r($voucher_data); print_r($voucher_rows);exit;

		$dr_rows = $voucher_rows['dr_rows'];
		$cr_rows = $voucher_rows['cr_rows'];

		// --------------------------------------------------------------------


        /**
         * Format Data
         */
        $voucher_data['account_id']['dr'] 	= $dr_rows['accounts'];
        $voucher_data['party_type']['dr'] 	= $dr_rows['party_types'];
        $voucher_data['party_id']['dr'] 	= $dr_rows['parties'];
        $voucher_data['amount']['dr'] 		= $dr_rows['amounts'];

        $voucher_data['account_id']['cr'] 	= $cr_rows['accounts'];
        $voucher_data['party_type']['cr'] 	= $cr_rows['party_types'];
        $voucher_data['party_id']['cr'] 	= $cr_rows['parties'];
        $voucher_data['amount']['cr'] 		= $cr_rows['amounts'];


		// --------------------------------------------------------------------

        /**
		 * Save Voucher and Its Relation with Policy
		 */

		try {

			/**
			 * Task 1: Save Voucher and Generate Voucher Code
			 */
			$voucher_id = $this->ac_voucher_model->add($voucher_data, $policy_record->id);

		} catch (Exception $e) {

			return $this->template->json([
				'title' 	=> 'Exception Occured!',
				'status' 	=> 'error',
				'message' 	=> $e->getMessage()
			]);
		}

		$flag_exception = FALSE;
		$message = '';


		/**
		 * --------------------------------------------------------------------
		 * Post Voucher Add Tasks
		 *
		 * NOTE
		 * 		We perform post voucher add tasks which are mainly to insert
		 * 		voucher internal relation with policy txn record and  update
		 * 		policy status.
		 *
		 * 		Please note that, if any of installment fails or exception
		 * 		happens, we rollback and disable voucher. (We can not delete
		 * 		voucher as we need to maintain sequential order for audit trail)
		 * --------------------------------------------------------------------
		 */


		/**
         * ==================== MANUAL TRANSACTIONS BEGIN =========================
         */


            /**
             * Disable DB Debugging
             */
            $this->db->db_debug = FALSE;
            $this->db->trans_begin();


                // --------------------------------------------------------------------

            	/**
				 * Task 2: Add Voucher-Policy Installment Relation
				 */

				try {

					$relation_data = [
						'policy_id' 	=> $installment_record->policy_id,
						'voucher_id' 	=> $voucher_id,
						'ref' 			=> IQB_REL_POLICY_VOUCHER_REF_PI,
						'ref_id' 		=> $installment_record->id,
						'flag_invoiced' => IQB_FLAG_INVOICED__NO
					];
					$this->rel_policy_voucher_model->add($relation_data);

				} catch (Exception $e) {

					$flag_exception = TRUE;
					$message = $e->getMessage();
				}

                // --------------------------------------------------------------------

				/**
				 * Task 4: Update Installment Status to "Vouchered", Clean Cache
				 */
				if( !$flag_exception )
				{
					try{

						/**
						 * If first installment of this transaction
						 */
						if($installment_record->flag_first == IQB_FLAG_ON)
						{
							$this->endorsement_model->update_status($endorsement_record, IQB_POLICY_ENDORSEMENT_STATUS_VOUCHERED);
						}

						// Update installment status
						$this->policy_installment_model->to_vouchered($installment_record);

					} catch (Exception $e) {

						$flag_exception = TRUE;
						$message = $e->getMessage();
					}
				}

                // --------------------------------------------------------------------

				/**
				 * Task 5: Generate Policy Number
				 *
				 * NOTE: Policy TXN must be fresh or Renewal & First Installment
				 */
				if(
						$flag_exception == FALSE
							&&
						$installment_record->flag_first == IQB_FLAG_ON
							&&
						in_array($endorsement_record->txn_type, [IQB_POLICY_ENDORSEMENT_TYPE_FRESH, IQB_POLICY_ENDORSEMENT_TYPE_RENEWAL])
					)
				{
					try{
						$policy_code = $this->policy_model->generate_policy_number( $policy_record );
						if($policy_code)
						{
							$policy_record->code = $policy_code;

							// Update Voucher Narration
							$narration = 'POLICY VOUCHER - POLICY CODE : ' . $policy_record->code;
							$this->ac_voucher_model->update($voucher_id, ['narration' => $narration], TRUE);
						}
					} catch (Exception $e) {
						$flag_exception = TRUE;
						$message = $e->getMessage();
					}
				}

                // --------------------------------------------------------------------

			/**
             * Complete transactions or Rollback
             */
			if ($flag_exception === TRUE || $this->db->trans_status() === FALSE)
			{
		        $this->db->trans_rollback();

		        /**
            	 * Set Voucher Flag Complete to OFF
            	 */
            	$this->ac_voucher_model->disable_voucher($voucher_id);

            	return $this->template->json([
					'title' 	=> 'Something went wrong!',
					'status' 	=> 'error',
					'message' 	=> $message ? $message : 'Could not perform save voucher relation or update policy status'
				]);
			}
			else
			{
			        $this->db->trans_commit();
			}

            /**
             * Restore DB Debug Configuration
             */
            $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;

        /**
         * ==================== MANUAL TRANSACTIONS END =========================
         */


		// --------------------------------------------------------------------

        /**
		 * Load Portfolio Specific Helper File
		 */
        try { load_portfolio_helper($policy_record->portfolio_id);} catch (Exception $e) {
			return $this->template->json([ 'status' => 'error', 'message' => $e->getMessage()], 404);
		}

		/**
		 * Reload the Policy Overview Tab, Update Installment Row (Replace)
		 */
		$installment_record->status 				= IQB_POLICY_INSTALLMENT_STATUS_VOUCHERED;
		$installment_record->endorsement_status 	= IQB_POLICY_ENDORSEMENT_STATUS_VOUCHERED;
		$endorsement_record->status 				= IQB_POLICY_ENDORSEMENT_STATUS_VOUCHERED;

		$this->load->model('rel_policy_bsrs_heading_model');
		$html_tab_ovrview = $this->load->view('policies/tabs/_tab_overview', [
			'record' => $policy_record,
			'endorsement_record' => $endorsement_record,
			'bsrs_headings_policy' => $this->rel_policy_bsrs_heading_model->by_policy($policy_record->id)
		], TRUE);

		$html_txn_row 	  = $this->load->view('policy_installments/_single_row', ['policy_record' => $policy_record, 'record' => $installment_record], TRUE);

		$ajax_data = [
			'message' 	=> 'Successfully Updated!',
			'status'  	=> 'success',
			'multipleUpdate' => [
				[
					'box' 		=> '#tab-policy-overview-inner',
					'method' 	=> 'replaceWith',
					'html' 		=> $html_tab_ovrview
				],
				[
					'box' 		=> '#_data-row-policy_installments-' . $installment_record->id,
					'method' 	=> 'replaceWith',
					'html' 		=> $html_txn_row
				]
			]
		];
		return $this->template->json($ajax_data);
	}

	// --------------------------------------------------------------------
	// VOUCHER helper function
	// --------------------------------------------------------------------

		private function _data_voucher_master($policy_code, $txn_type)
		{
			/**
			 * Let's Build Policy Voucher
			 */
			$narration = 'POLICY VOUCHER - POLICY CODE : ' . $policy_code;

			$voucher_data = [
	            'voucher_date'      => date('Y-m-d'),
	            'voucher_type_id'   => $this->_voucher_type_by_txn_type($txn_type),
	            'narration'         => $narration,
	            'flag_internal'     => IQB_FLAG_ON
	        ];

	        return $voucher_data;
		}

		private function _data_voucher_details($installment_record, $endorsement_record, $policy_record, $pfs_record, $portfolio_record)
		{
			$txn_type 	= (int)$installment_record->txn_type;
			$data 		= NULL;

			switch ($txn_type)
			{
				case IQB_POLICY_ENDORSEMENT_TYPE_FRESH:
				case IQB_POLICY_ENDORSEMENT_TYPE_RENEWAL:
				case IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_UPGRADE:
					$data = $this->_data_voucher_details_for_premium_voucher($installment_record, $endorsement_record, $policy_record, $pfs_record, $portfolio_record);
					break;

				case IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_REFUND:
					$data = $this->_data_voucher_details_for_credit_voucher($installment_record, $endorsement_record, $policy_record, $pfs_record, $portfolio_record);
					break;

				case IQB_POLICY_ENDORSEMENT_TYPE_OWNERSHIP_TRANSFER:
					$data = $this->_data_voucher_details_for_transfer_voucher($installment_record, $endorsement_record, $policy_record, $pfs_record, $portfolio_record);
					break;


				default:
					# code...
					break;
			}

			if( !$data )
			{
				throw new Exception("Exception [Controller:Policy_installments][Method: _data_voucher_details()]: No voucher details found for supplied 'Endorsement Type'.");
			}

			return $data;
		}

		private function _data_voucher_details_for_premium_voucher($installment_record, $endorsement_record, $policy_record, $pfs_record, $portfolio_record)
		{
			// --------------------------------------------------------------------

	        /**
	         * Voucher Amount Computation
	         */
	        $gross_premium_amount 		= (float)$installment_record->amt_basic_premium + (float)$installment_record->amt_pool_premium;
	        $stamp_income_amount 		= floatval($installment_record->amt_stamp_duty);
	        $vat_payable_amount 		= $installment_record->amt_vat;

	        $beema_samiti_service_charge_amount 		= ($gross_premium_amount * $pfs_record->bs_service_charge) / 100.00;
	        $total_to_receive_from_insured_party_amount = $gross_premium_amount + $stamp_income_amount + $vat_payable_amount;
	        $agent_commission_amount 					= $installment_record->amt_agent_commission ?? NULL;

			// --------------------------------------------------------------------

	        /**
	         * Debit Rows
	         */
	        $dr_rows = [

	        	/**
	        	 * Accounts
	        	 */
	        	 'accounts' => 	[
		        	// Insured Party
		        	IQB_AC_ACCOUNT_ID_INSURED_PARTY,

		        	// Expense - Beema Samiti Service Charge
		        	IQB_AC_ACCOUNT_ID_EXPENSE_BS_SERVICE_CHARGE
		        ],

		        /**
		         * Party Types
		         */
		        'party_types' => [
		        	// Insured Party -- Customer
		        	IQB_AC_PARTY_TYPE_CUSTOMER,

		        	// Beema Samiti Service Charge -- Company
		        	IQB_AC_PARTY_TYPE_COMPANY,
		        ],

		        /**
		         * Party IDs
		         */
		        'parties' => [

		        	// Insured Party -- Customr ID
		        	$policy_record->customer_id,

		        	// Beema Samiti Service Charge -- Beema Samiti ID
		        	IQB_COMPANY_ID_BEEMA_SAMITI
		        ],

		        /**
		         * Amounts
		         */
		        'amounts' => [

		        	// Insured Party -- Amount Received From Insured Party
		        	$total_to_receive_from_insured_party_amount,

		        	// Beema Samiti Service Charge -- Beema Samiti Service Charge
		        	$beema_samiti_service_charge_amount
		        ]

	        ];

			// --------------------------------------------------------------------

	        /**
	         * Check if portfolio has "Direct Premium Income Account ID"
	         */
	        if( !$portfolio_record->account_id_dpi )
	        {
	        	throw new Exception("Exception [Controller:Policy_installments][Method: _data_voucher_details_for_premium_voucher()]: No 'Direct Premium Income Account' for this Portfolio (({$portfolio_record->name_en})).<br/>Please add portfolio 'Direct Premium Income Account' for portfolio ({$portfolio_record->name_en}) from Master Setup > Portfolio.");
	        }

	        /**
	         * Credit Rows
	         */
	        $cr_rows = [

	        	/**
	        	 * Accounts
	        	 */
	        	 'accounts' => 	[
		        	// Vat Payable
		        	IQB_AC_ACCOUNT_ID_VAT_PAYABLE,

		        	// Stamp Income
		        	IQB_AC_ACCOUNT_ID_STAMP_INCOME,

		        	// Direct Premium Income Portfolio Wise
		        	$portfolio_record->account_id_dpi,

		        	// Liability - Service fee Beema Samiti
		        	IQB_AC_ACCOUNT_ID_LIABILITY_BS_SERVICE_CHARGE
		        ],

		        /**
		         * Party Types
		         */
		        'party_types' => [
		        	// Vat Payable -- NULL
		        	NULL,

		        	// Stamp Income -- NULL
		        	NULL,

		        	// Direct Premium Income -- NULL
		        	NULL,

		        	// Beema Samiti Service Charge -- Company
		        	IQB_AC_PARTY_TYPE_COMPANY
		        ],

		        /**
		         * Party IDs
		         */
		        'parties' => [

		        	// Vat Payable -- NULL
		        	NULL,

		        	// Stamp Income -- NULL
		        	NULL,

		        	// Direct Premium Income -- NULL
		        	NULL,

		        	// Beema Samiti Service Charge -- Beema Samiti ID
		        	IQB_COMPANY_ID_BEEMA_SAMITI
		        ],

		        /**
		         * Amounts
		         */
		        'amounts' => [

		        	// Vat Payable -- Vat Amount
		        	$vat_payable_amount,

		        	// Stamp Income -- Stamp Income Amount
		        	$stamp_income_amount,

		        	// Direct Premium Income -- Gross Premium Amount
		        	$gross_premium_amount,

		        	// Beema Samiti Service Charge -- Beema Samiti Service Charge
		        	$beema_samiti_service_charge_amount
		        ]

	        ];

	        // --------------------------------------------------------------------

	        /**
	         * Additional Debit/Credit Rows if Agent Commission Apply?
	         *
	         * NOTE: You must have $agent_commission_amount (NOT NULL or Non Zero Value)
	         */
	        if( $agent_commission_amount &&  $policy_record->flag_dc === IQB_POLICY_FLAG_DC_AGENT_COMMISSION && $policy_record->agent_id )
	        {
	        	// Agency Commission
	        	$dr_rows['accounts'][] = IQB_AC_ACCOUNT_ID_AGENCY_COMMISSION;

	        	// Agency Commission -- Agent
	        	$dr_rows['party_types'][] = IQB_AC_PARTY_TYPE_AGENT;

	        	// Agency Commission -- Agent ID
	        	$dr_rows['parties'][] = $policy_record->agent_id;

	        	// Agency Commission -- Agent Commission Amount
	        	$dr_rows['amounts'][] = $agent_commission_amount;



	        	// Agent TDS, Agent Commission Payable
	        	$cr_rows['accounts'][] = IQB_AC_ACCOUNT_ID_TDS_AGENCY_COMMISSION;
	        	$cr_rows['accounts'][] = IQB_AC_ACCOUNT_ID_AGENCY_COMMISSION_PAYABLE;

	        	// Agent TDS -- Agent, Agent Commission Payable -- Agent
	        	$cr_rows['party_types'][] = IQB_AC_PARTY_TYPE_AGENT;
	        	$cr_rows['party_types'][] = IQB_AC_PARTY_TYPE_AGENT;

	        	// Agent TDS -- Agent ID, Agent Commission Payable -- Agent ID
	        	$cr_rows['parties'][] = $policy_record->agent_id;
	        	$cr_rows['parties'][] = $policy_record->agent_id;

	        	// Agent TDS -- TDS Amount, Agent Commission Payable -- Agent Payable Amount
	        	$this->load->model('ac_duties_and_tax_model');
	        	$agent_tds_amount = $this->ac_duties_and_tax_model->compute_tax(IQB_AC_DNT_ID_TDS_ON_AC, $agent_commission_amount);
	        	$agent_commission_payable_amount = $agent_commission_amount - $agent_tds_amount;
	        	$cr_rows['amounts'][] = $agent_tds_amount;
	        	$cr_rows['amounts'][] = $agent_commission_payable_amount;
	        }

	        // --------------------------------------------------------------------


	        return [
	        	'dr_rows' => $dr_rows,
	        	'cr_rows' => $cr_rows
	        ];
		}

		private function _data_voucher_details_for_credit_voucher($installment_record, $endorsement_record, $policy_record, $pfs_record, $portfolio_record)
		{
			// --------------------------------------------------------------------

	        /**
	         * Voucher Amount Computation
	         */
	        $gross_premium_amount 		= (float)$installment_record->amt_basic_premium + (float)$installment_record->amt_pool_premium;
	        $stamp_income_amount 		= floatval($installment_record->amt_stamp_duty);
	        $vat_payable_amount 		= $installment_record->amt_vat;
	        $amt_cancellation_fee 		= floatval($installment_record->amt_cancellation_fee);

	        $beema_samiti_service_charge_amount 	= ($gross_premium_amount * $pfs_record->bs_service_charge) / 100.00;
	        $total_refund_to_insured_party_amount 	= $gross_premium_amount + $stamp_income_amount + $vat_payable_amount + $amt_cancellation_fee;
	        $agent_commission_amount 				= $installment_record->amt_agent_commission ?? NULL;

			// --------------------------------------------------------------------

	        /**
	         * Credit Rows
	         */
	        $cr_rows = [

	        	/**
	        	 * Accounts
	        	 */
	        	 'accounts' => 	[
		        	// Insured Party
		        	IQB_AC_ACCOUNT_ID_INSURED_PARTY,

		        	// Expense - Beema Samiti Service Charge
		        	IQB_AC_ACCOUNT_ID_EXPENSE_BS_SERVICE_CHARGE
		        ],

		        /**
		         * Party Types
		         */
		        'party_types' => [
		        	// Insured Party -- Customer
		        	IQB_AC_PARTY_TYPE_CUSTOMER,

		        	// Beema Samiti Service Charge -- Company
		        	IQB_AC_PARTY_TYPE_COMPANY,
		        ],

		        /**
		         * Party IDs
		         */
		        'parties' => [

		        	// Insured Party -- Customr ID
		        	$policy_record->customer_id,

		        	// Beema Samiti Service Charge -- Beema Samiti ID
		        	IQB_COMPANY_ID_BEEMA_SAMITI
		        ],

		        /**
		         * Amounts
		         */
		        'amounts' => [

		        	// Insured Party -- Amount Refund to Insured Party
		        	abs($total_refund_to_insured_party_amount),

		        	// Beema Samiti Service Charge -- Beema Samiti Service Charge
		        	abs($beema_samiti_service_charge_amount)
		        ]

	        ];

	        /**
	         * !!! STAMP INCOME !!!
	         *
	         * Stamp Income is Credit if any
	         */
	        if( $stamp_income_amount )
	        {
	        	$cr_rows['accounts'][] 		= IQB_AC_ACCOUNT_ID_STAMP_INCOME;
	        	$cr_rows['party_types'][] 	= NULL;
	        	$cr_rows['parties'][] 		= NULL;
	        	$cr_rows['amounts'][] 		= $stamp_income_amount;
	        }

	        /**
	         *  !!! CANCELLATION FEE !!!
	         *
	         * Cancellation Fee is Credit if any
	         */
	        if( $amt_cancellation_fee )
	        {
	        	$cr_rows['accounts'][] 		= IQB_AC_ACCOUNT_ID_SERVICE_CHARGE_RECOVERY;
	        	$cr_rows['party_types'][] 	= NULL;
	        	$cr_rows['parties'][] 		= NULL;
	        	$cr_rows['amounts'][] 		= $amt_cancellation_fee;
	        }


			// --------------------------------------------------------------------

	        /**
	         * Check if portfolio has "Direct Premium Income Account ID"
	         */
	        if( !$portfolio_record->account_id_dpi )
	        {
	        	throw new Exception("Exception [Controller:Policy_installments][Method: _data_voucher_details_for_premium_voucher()]: No 'Direct Premium Income Account' for this Portfolio (({$portfolio_record->name_en})).<br/>Please add portfolio 'Direct Premium Income Account' for portfolio ({$portfolio_record->name_en}) from Master Setup > Portfolio.");
	        }

	        /**
	         * Credit Rows
	         */
	        $dr_rows = [

	        	/**
	        	 * Accounts
	        	 */
	        	 'accounts' => 	[
		        	// Direct Premium Income Portfolio Wise
		        	$portfolio_record->account_id_dpi,

		        	// Liability - Service fee Beema Samiti
		        	IQB_AC_ACCOUNT_ID_LIABILITY_BS_SERVICE_CHARGE
		        ],

		        /**
		         * Party Types
		         */
		        'party_types' => [
		        	// Direct Premium Income -- NULL
		        	NULL,

		        	// Beema Samiti Service Charge -- Company
		        	IQB_AC_PARTY_TYPE_COMPANY
		        ],

		        /**
		         * Party IDs
		         */
		        'parties' => [

		        	// Direct Premium Income -- NULL
		        	NULL,

		        	// Beema Samiti Service Charge -- Beema Samiti ID
		        	IQB_COMPANY_ID_BEEMA_SAMITI
		        ],

		        /**
		         * Amounts
		         */
		        'amounts' => [

		        	// Direct Premium Income -- Gross Premium Amount
		        	abs($gross_premium_amount),

		        	// Beema Samiti Service Charge -- Beema Samiti Service Charge
		        	abs($beema_samiti_service_charge_amount)
		        ]

	        ];

	        // --------------------------------------------------------------------


	        /**
	         * !!! VAT !!!

	         * 		case a. Positive VAT - Goes Credit
	         * 		case b. Negative VAT - Goes Debit
	         */
	        if( $vat_payable_amount > 0 )
	        {
	        	$cr_rows['accounts'][] 		= IQB_AC_ACCOUNT_ID_VAT_PAYABLE;
	        	$cr_rows['party_types'][] 	= NULL;
	        	$cr_rows['parties'][] 		= NULL;
	        	$cr_rows['amounts'][] 		= $vat_payable_amount;
	        }
	        else
	        {
	        	$dr_rows['accounts'][] 		= IQB_AC_ACCOUNT_ID_VAT_PAYABLE;
	        	$dr_rows['party_types'][] 	= NULL;
	        	$dr_rows['parties'][] 		= NULL;
	        	$dr_rows['amounts'][] 		= abs($vat_payable_amount);
	        }

	        // --------------------------------------------------------------------

	        /**
	         * Additional Debit/Credit Rows if Agent Commission Apply?
	         *
	         * NOTE: You must have $agent_commission_amount (NOT NULL or Non Zero Value)
	         */
	        if( $agent_commission_amount &&  $policy_record->flag_dc === IQB_POLICY_FLAG_DC_AGENT_COMMISSION && $policy_record->agent_id )
	        {
	        	// Agency Commission
	        	$cr_rows['accounts'][] 		= IQB_AC_ACCOUNT_ID_AGENCY_COMMISSION;
	        	$cr_rows['party_types'][] 	= IQB_AC_PARTY_TYPE_AGENT;
	        	$cr_rows['parties'][] 		= $policy_record->agent_id;
	        	$cr_rows['amounts'][] 		= abs($agent_commission_amount);





	        	// Agent TDS, Agent Commission Payable
	        	$dr_rows['accounts'][] = IQB_AC_ACCOUNT_ID_TDS_AGENCY_COMMISSION;
	        	$dr_rows['accounts'][] = IQB_AC_ACCOUNT_ID_AGENCY_COMMISSION_PAYABLE;

	        	// Agent TDS -- Agent, Agent Commission Payable -- Agent
	        	$dr_rows['party_types'][] = IQB_AC_PARTY_TYPE_AGENT;
	        	$dr_rows['party_types'][] = IQB_AC_PARTY_TYPE_AGENT;

	        	// Agent TDS -- Agent ID, Agent Commission Payable -- Agent ID
	        	$dr_rows['parties'][] = $policy_record->agent_id;
	        	$dr_rows['parties'][] = $policy_record->agent_id;

	        	// Agent TDS -- TDS Amount, Agent Commission Payable -- Agent Payable Amount
	        	$this->load->model('ac_duties_and_tax_model');
	        	$agent_tds_amount = $this->ac_duties_and_tax_model->compute_tax(IQB_AC_DNT_ID_TDS_ON_AC, $agent_commission_amount);
	        	$agent_commission_payable_amount = $agent_commission_amount - $agent_tds_amount;
	        	$dr_rows['amounts'][] = abs($agent_tds_amount);
	        	$dr_rows['amounts'][] = abs($agent_commission_payable_amount);
	        }

	        // --------------------------------------------------------------------


	        return [
	        	'dr_rows' => $dr_rows,
	        	'cr_rows' => $cr_rows
	        ];
		}

		private function _data_voucher_details_for_transfer_voucher($installment_record, $endorsement_record, $policy_record, $pfs_record, $portfolio_record)
		{
			// --------------------------------------------------------------------

	        /**
	         * Voucher Amount Computation
	         */
	        $vat_payable_amount 		= floatval($installment_record->amt_vat);
	        $stamp_income_amount 		= floatval($installment_record->amt_stamp_duty);
	        $ownership_transfer_charge 	= floatval($installment_record->amt_transfer_fee) +
				        			  	  floatval($installment_record->amt_transfer_ncd);

	        $total_amount = $ownership_transfer_charge +
							$stamp_income_amount +
							$vat_payable_amount;

			// --------------------------------------------------------------------

	        /**
	         * Debit Rows
	         */
	        $dr_rows = [

	        	/**
	        	 * Accounts
	        	 */
	        	 'accounts' => 	[
		        	// Insured Party
		        	IQB_AC_ACCOUNT_ID_INSURED_PARTY
		        ],

		        /**
		         * Party Types
		         */
		        'party_types' => [
		        	// Insured Party -- Customer
		        	IQB_AC_PARTY_TYPE_CUSTOMER
		        ],

		        /**
		         * Party IDs
		         */
		        'parties' => [

		        	// Insured Party -- New Owner (Customr ID)
		        	$endorsement_record->transfer_customer_id
		        ],

		        /**
		         * Amounts
		         */
		        'amounts' => [

		        	// Insured Party -- Amount Received From Insured Party
		        	$total_amount
		        ]

	        ];

			// --------------------------------------------------------------------


	        /**
	         * Credit Rows
	         */
	        $cr_rows = [

	        	/**
	        	 * Accounts
	        	 */
	        	 'accounts' => 	[
		        	// Vat Payable
		        	IQB_AC_ACCOUNT_ID_VAT_PAYABLE,

		        	// Stamp Income
		        	IQB_AC_ACCOUNT_ID_STAMP_INCOME,

		        	// Ownership Transfer Charge
		        	IQB_AC_ACCOUNT_ID_OWNERSHIP_TRANSFER_CHARGE
		        ],

		        /**
		         * Party Types
		         */
		        'party_types' => [
		        	// Vat Payable -- NULL
		        	NULL,

		        	// Stamp Income -- NULL
		        	NULL,

		        	// Ownership Transfer Charge -- NULL
		        	NULL
		        ],

		        /**
		         * Party IDs
		         */
		        'parties' => [

		        	// Vat Payable -- NULL
		        	NULL,

		        	// Stamp Income -- NULL
		        	NULL,

		        	// Ownership Transfer Charge -- NULL
		        	NULL
		        ],

		        /**
		         * Amounts
		         */
		        'amounts' => [

		        	// Vat Payable -- Vat Amount
		        	$vat_payable_amount,

		        	// Stamp Income -- Stamp Income Amount
		        	$stamp_income_amount,

		        	// Ownership Transfer Charge
		        	$ownership_transfer_charge
		        ]

	        ];

	        // --------------------------------------------------------------------


	        return [
	        	'dr_rows' => $dr_rows,
	        	'cr_rows' => $cr_rows
	        ];
		}

		private function _voucher_type_by_txn_type($txn_type)
		{
			$txn_type = (int)$txn_type;
			$voucher_type_id = NULL;


			switch ($txn_type)
			{
				case IQB_POLICY_ENDORSEMENT_TYPE_FRESH:
				case IQB_POLICY_ENDORSEMENT_TYPE_RENEWAL:
				case IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_UPGRADE:
					$voucher_type_id = IQB_AC_VOUCHER_TYPE_PRI; // Premium Voucher
					break;

				case IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_REFUND:
					$voucher_type_id = IQB_AC_VOUCHER_TYPE_CRDN; // Credit Voucher
					break;

				case IQB_POLICY_ENDORSEMENT_TYPE_OWNERSHIP_TRANSFER:
					$voucher_type_id = IQB_AC_VOUCHER_TYPE_GINV; // General Invoice
					break;


				default:
					# code...
					break;
			}

			if( !$voucher_type_id )
			{
				throw new Exception("Exception [Controller:Policy_installments][Method: _voucher_type_by_txn_type()]: No voucher type found for supplied 'Endorsement Type'.");
			}

			return $voucher_type_id;
		}

	// --------------------------------------------------------------------

	/**
	 * Generate Invoice
	 *
	 * @param integer $id Policy TXN ID
	 * @param integer $voucher_id Voucher ID
	 * @return mixed
	 */
	public function invoice($id, $voucher_id)
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('policy_installments', 'generate.policy.invoice') )
		{
			$this->dx_auth->deny_access();
		}

		// --------------------------------------------------------------------

		/**
		 * Get the Policy Fresh/Renewal Txn Record
		 */
		$id 		= (int)$id;
		$voucher_id = (int)$voucher_id;
		$installment_record = $this->policy_installment_model->get( $id );
		if(!$installment_record)
		{
			$this->template->render_404();
		}

		/**
		 * Get the transaction record. Valid? Type must be invoicable
		 */
		$endorsement_record = $this->endorsement_model->get( $installment_record->endorsement_id );
		if( !$endorsement_record || !_ENDORSEMENT_is_invoicable_by_type($endorsement_record->txn_type) )
		{
			return $this->template->json([
				'title' 	=> 'Invalid Action!',
				'status' 	=> 'error',
				'message' 	=> 'Invalid Endorsement Record and/or Type.'
			], 400);
		}

		// --------------------------------------------------------------------

		/**
		 * Get Voucher Record By Policy Installment Relation
		 */
		$this->load->model('ac_voucher_model');
		$voucher_record = $this->ac_voucher_model->get($voucher_id, TRUE);
		if(
			!$voucher_record
				||
			$voucher_record->ref !== IQB_REL_POLICY_VOUCHER_REF_PI
				||
			$voucher_record->ref_id != $installment_record->id
		){
			return $this->template->json([
				'title' 	=> 'NOT FOUND!',
				'status' 	=> 'error',
				'message' 	=> 'Voucher not found for given installment/endorsement record.'
			], 400);
		}

		// --------------------------------------------------------------------

		/**
		 * Check if Invoice already generated for this Voucher
		 */
		$this->load->model('ac_invoice_model');
		if( $this->ac_invoice_model->invoice_exists($voucher_id))
		{
			return $this->template->json([
				'title' 	=> 'OOPS!',
				'status' 	=> 'error',
				'message' 	=> 'Invoice already exists for this Voucher.'
			], 404);
		}

		// --------------------------------------------------------------------

		/**
		 * Policy Record
		 */
		$policy_record = $this->policy_model->get($installment_record->policy_id);
		if(!$policy_record)
		{
			return $this->template->json([
				'title' 	=> 'NOT FOUND!',
				'status' 	=> 'error',
				'message' 	=> 'Policy record not found.'
			], 404);
		}

		// --------------------------------------------------------------------

		/**
		 * Record Status Authorized to Generate Voucher?
		 */
		if($installment_record->status !== IQB_POLICY_INSTALLMENT_STATUS_VOUCHERED || $voucher_record->flag_invoiced != IQB_FLAG_INVOICED__NO )
		{
			return $this->template->json([
				'title' 	=> 'OOPS!',
				'status' 	=> 'error',
				'message' 	=> 'You can not perform this action.'
			], 404);
		}

		// --------------------------------------------------------------------

		/**
		 * Let's Build Policy Invoice
		 */
		try{
			$invoice_data = $this->_data_invoice_master($installment_record, $endorsement_record, $voucher_id);
		}
		catch (Exception $e) {
			return $this->template->json([ 'status' => 'error', 'title' => 'Exception Occured.','message' => $e->getMessage()], 400);
		}

		// --------------------------------------------------------------------

        /**
         * Build Invoice Details Data
         */
        try{
			$invoice_details_data = $this->_data_invoice_details( $installment_record, $endorsement_record->txn_type );
		}
		catch (Exception $e) {
			return $this->template->json([ 'status' => 'error', 'title' => 'Exception Occured.','message' => $e->getMessage()], 400);
		}


		// --------------------------------------------------------------------

		/**
		 * Save Invoice
		 */
		try {

			/**
			 * Task 1: Save Invoice and Generate Invoice Code
			 */
			$invoice_id = $this->ac_invoice_model->add($invoice_data, $invoice_details_data, $policy_record->id);

		} catch (Exception $e) {

			return $this->template->json([
				'title' 	=> 'Exception Occured!',
				'status' 	=> 'error',
				'message' 	=> $e->getMessage()
			]);
		}

		$flag_exception = FALSE;
		$message = '';


		/**
		 * --------------------------------------------------------------------
		 * Post Invoice Add Tasks
		 *
		 * NOTE
		 * 		We perform post invoice add tasks which are mainly to update
		 * 		voucher internal relation's invoiced flag and  update
		 * 		policy installment status.
		 *
		 * 		Please note that, if any of installment fails or exception
		 * 		happens, we rollback and disable invoice. (We can not delete
		 * 		voucher as we need to maintain sequential order for audit trail)
		 * --------------------------------------------------------------------
		 */


		/**
         * ==================== MANUAL TRANSACTIONS BEGIN =========================
         */


            /**
             * Disable DB Debugging
             */
            $this->db->db_debug = FALSE;
            // $this->db->trans_start();
            $this->db->trans_begin();


                // --------------------------------------------------------------------

				/**
				 * Task 2: Update Installment Status to "Invoiced", Clean Cache
				 */
				try{

					/**
					 * If first installment of this transaction
					 */
					if($installment_record->flag_first == IQB_FLAG_ON)
					{
						$this->endorsement_model->update_status($endorsement_record, IQB_POLICY_ENDORSEMENT_STATUS_INVOICED);
					}
					$this->policy_installment_model->to_invoiced($installment_record);

				} catch (Exception $e) {

					$flag_exception = TRUE;
					$message = $e->getMessage();
				}

                // --------------------------------------------------------------------

                /**
                 * Task 3: Update Relation Table Flag - "flag_invoiced"
                 */
                if( !$flag_exception )
				{
					$this->load->model('rel_policy_voucher_model');
					$rel_base_where = [
						'voucher_id' 	=> $voucher_id,
					];
	                $this->rel_policy_voucher_model->flag_invoiced($rel_base_where, IQB_FLAG_INVOICED__YES);

	            	// Clear Voucher Cache for This Policy
	            	$cache_var = 'ac_voucher_list_by_policy_' . $policy_record->id;
					$this->ac_voucher_model->clear_cache($cache_var);
            	}

                // --------------------------------------------------------------------


				/**
				 * Task 4: Save Invoice PDF (Original)
				 */
				try{

					$invoice_data = [
						'record' 	=> $this->ac_invoice_model->get($invoice_id),
						'rows' 		=> $this->ac_invoice_detail_model->rows_by_invoice($invoice_id)
					];
					// _INVOICE__pdf($invoice_data, 'save');

				} catch (Exception $e) {

					$flag_exception = TRUE;
					$message = $e->getMessage();
				}

			/**
             * Complete transactions or Rollback
             */
			if ($flag_exception === TRUE || $this->db->trans_status() === FALSE)
			{
		        $this->db->trans_rollback();

		        /**
            	 * Set Invoice Flag Complete to OFF
            	 */
            	$this->ac_invoice_model->disable_invoice($invoice_id);

            	return $this->template->json([
					'title' 	=> 'Something went wrong!',
					'status' 	=> 'error',
					'message' 	=> $message ? $message : 'Could not update policy installment status or voucher relation flag'
				]);
			}
			else
			{
			        $this->db->trans_commit();
			}

            /**
             * Restore DB Debug Configuration
             */
            $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;

        /**
         * ==================== MANUAL TRANSACTIONS END =========================
         */


		// --------------------------------------------------------------------

        /**
		 * Load Portfolio Specific Helper File
		 */
        try { load_portfolio_helper($policy_record->portfolio_id);} catch (Exception $e) {
			return $this->template->json([ 'status' => 'error', 'message' => $e->getMessage()], 404);
		}


		/**
		 * Reload the Policy Overview Tab, Update Installment Row (Replace)
		 */
		$installment_record->status 			= IQB_POLICY_INSTALLMENT_STATUS_INVOICED;
		$installment_record->endorsement_status = IQB_POLICY_ENDORSEMENT_STATUS_INVOICED;
		$endorsement_record->status 			= IQB_POLICY_ENDORSEMENT_STATUS_INVOICED;

		$this->load->model('rel_policy_bsrs_heading_model');
		$html_tab_ovrview = $this->load->view('policies/tabs/_tab_overview', [
			'record' => $policy_record,
			'endorsement_record' => $endorsement_record,
			'bsrs_headings_policy' => $this->rel_policy_bsrs_heading_model->by_policy($policy_record->id)
		], TRUE);


		$voucher_record->flag_invoiced = IQB_FLAG_INVOICED__YES;
		$html_voucher_row 	= $this->load->view('accounting/vouchers/_single_row', ['record' => $voucher_record], TRUE);

		$ajax_data = [
			'message' 	=> 'Successfully Updated!',
			'status'  	=> 'success',
			'multipleUpdate' => [
				[
					'box' 		=> '#tab-policy-overview-inner',
					'method' 	=> 'replaceWith',
					'html' 		=> $html_tab_ovrview
				],
				[
					'box' 		=> '#_data-row-voucher-' . $voucher_id,
					'method' 	=> 'replaceWith',
					'html' 		=> $html_voucher_row
				]
			]
		];
		return $this->template->json($ajax_data);
	}

		private function _data_invoice_master($installment_record, $endorsement_record, $voucher_id)
		{
			$invoice_data = [
	            'invoice_date'      => date('Y-m-d'),
	            'voucher_id'   		=> $voucher_id
	        ];

			/**
	         * Amount Computation
	         */
			$amount 		= 0.00;
			$customer_id 	= NULL;
	        $txn_type 		= (int)$endorsement_record->txn_type;
			switch ($txn_type)
			{
				case IQB_POLICY_ENDORSEMENT_TYPE_FRESH:
				case IQB_POLICY_ENDORSEMENT_TYPE_RENEWAL:
				case IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_UPGRADE:
					$amount = floatval($installment_record->amt_basic_premium) +
								floatval($installment_record->amt_pool_premium) +
								floatval($installment_record->amt_stamp_duty) +
								floatval($installment_record->amt_vat);

					// Regular Policy Customer ID
					$customer_id  = $endorsement_record->customer_id;
					break;


				case IQB_POLICY_ENDORSEMENT_TYPE_OWNERSHIP_TRANSFER:
					$amount = floatval($installment_record->amt_transfer_fee) +
								floatval($installment_record->amt_transfer_ncd) +
								floatval($installment_record->amt_stamp_duty) +
								floatval($installment_record->amt_vat);

					// Customer ID to be Transferred
					$customer_id  = $endorsement_record->transfer_customer_id;
					break;


				default:
					# code...
					break;
			}

			if( !$amount || !$customer_id )
			{
				throw new Exception("Exception [Controller:Policy_installments][Method: _data_invoice_master()]: Could not compute invoice data for given 'Endorsement Type'.");
			}

			// Update invoice data
			$invoice_data['amount'] 		= $amount;
			$invoice_data['customer_id'] 	= $customer_id;

			return $invoice_data;
		}

		private function _data_invoice_details($installment_record, $txn_type)
		{
			/**
	         * Amount Computation
	         */
			$amount 	= NULL;
			$description = '';
	        $txn_type 	= (int)$installment_record->txn_type;
			switch ($txn_type)
			{
				case IQB_POLICY_ENDORSEMENT_TYPE_FRESH:
				case IQB_POLICY_ENDORSEMENT_TYPE_RENEWAL:
				case IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_UPGRADE:
					$amount = floatval($installment_record->amt_basic_premium) +
								floatval($installment_record->amt_pool_premium) ;
					$description = "Policy Premium Amount (Policy Code - {$installment_record->policy_code})";
					break;


				case IQB_POLICY_ENDORSEMENT_TYPE_OWNERSHIP_TRANSFER:
					$amount 	= floatval($installment_record->amt_transfer_fee) +
								floatval($installment_record->amt_transfer_ncd);
					$description = "Policy Ownership Transfer Amount (Policy Code - {$installment_record->policy_code})";
					break;


				default:
					# code...
					break;
			}

			if( !$amount || !$description )
			{
				throw new Exception("Exception [Controller:Policy_installments][Method: _data_invoice_details()]: Could not compute invoice details data for given 'Endorsement Type'.");
			}

			/**
			 * Add Primary Amount
			 */
			$invoice_details_data = [
				[
					'description' 	=> $description,
					'amount' 		=> $amount
				]
			];

			/**
			 * Add Stamp Duty Amount
			 */
			if($installment_record->amt_stamp_duty)
	        {
	        	$invoice_details_data[] = [
		        	'description' 	=> "Stamp Duty",
		        	'amount'		=> $installment_record->amt_stamp_duty
		        ];
	        }

	        /**
			 * Add VAT Amount
			 */
	        $invoice_details_data[] = [
	        	'description' 	=> "VAT",
	        	'amount'		=> $installment_record->amt_vat
	        ];


			return $invoice_details_data;
		}

	// --------------------------------------------------------------------

	/**
	 * Generate Credit Note
	 *
	 * @param integer $id Policy Installment ID
	 * @param integer $voucher_id Voucher ID
	 * @return mixed
	 */
	public function credit_note($id, $voucher_id)
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('policy_installments', 'generate.policy.credit_note') )
		{
			$this->dx_auth->deny_access();
		}

		// --------------------------------------------------------------------

		/**
		 * Get the Policy Fresh/Renewal Txn Record
		 */
		$id 		= (int)$id;
		$voucher_id = (int)$voucher_id;
		$installment_record = $this->policy_installment_model->get( $id );
		if(!$installment_record)
		{
			return $this->template->json([
				'title' 	=> 'OOPS!',
				'status' 	=> 'error',
				'message' 	=> 'No Installment/Refund Record Found.'
			], 404);
		}

		/**
		 * Get the endorsement record, Valid Type?
		 */
		$endorsement_record = $this->endorsement_model->get( $installment_record->endorsement_id );
		if(!$endorsement_record || $endorsement_record->txn_type != IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_REFUND )
		{
			return $this->template->json([
				'title' 	=> 'Invalid Action!',
				'status' 	=> 'error',
				'message' 	=> 'Invalid Endorsement Record and/or Type.'
			], 400);
		}

		// --------------------------------------------------------------------

		/**
		 * Get Voucher Record By Policy Installment Relation
		 */
		$this->load->model('ac_voucher_model');
		$voucher_record = $this->ac_voucher_model->get($voucher_id, TRUE);
		if(
			!$voucher_record
				||
			$voucher_record->ref !== IQB_REL_POLICY_VOUCHER_REF_PI
				||
			$voucher_record->ref_id != $installment_record->id
		){

			return $this->template->json([
				'title' 	=> 'NOT FOUND!',
				'status' 	=> 'error',
				'message' 	=> 'Voucher not found for given refund record.'
			], 400);
		}

		// --------------------------------------------------------------------

		/**
		 * Check if Credit Note already generated for this Voucher
		 */
		$this->load->model('ac_credit_note_model');
		if( $this->ac_credit_note_model->credit_note_exists($voucher_id))
		{
			return $this->template->json([
				'title' 	=> 'Invalid Action!',
				'status' 	=> 'error',
				'message' 	=> 'Credit Note already exists for this Voucher.'
			], 400);
		}

		// --------------------------------------------------------------------

		/**
		 * Policy Record
		 */
		$policy_record = $this->policy_model->get($installment_record->policy_id);
		if(!$policy_record)
		{
			return $this->template->json([
				'title' 	=> 'Invalid Action!',
				'status' 	=> 'error',
				'message' 	=> 'Policy record not found!'
			], 400);
		}

		// --------------------------------------------------------------------

		/**
		 * Record Status Authorized to Generate Voucher?
		 */
		if($installment_record->status !== IQB_POLICY_INSTALLMENT_STATUS_VOUCHERED || $voucher_record->flag_invoiced != IQB_FLAG_INVOICED__NO )
		{
			return $this->template->json([
			'title' 	=> 'Unauthorized Action!',
				'status' 	=> 'error',
				'message' 	=> 'You can not perform this action.'
			], 404);
		}

		// --------------------------------------------------------------------

		/**
		 * Let's Build Policy Credit Note
		 */

		/**
         * Voucher Amount Computation
         */
        $gross_premium_amount 		= (float)$installment_record->amt_basic_premium + (float)$installment_record->amt_pool_premium;
        $stamp_income_amount 		= floatval($installment_record->amt_stamp_duty);
        $amt_cancellation_fee 		= floatval($installment_record->amt_cancellation_fee);
        $vat_payable_amount 		= floatval($installment_record->amt_vat);
        $total_refund_amount 		= $gross_premium_amount + $stamp_income_amount + $vat_payable_amount + $amt_cancellation_fee;

		$credit_note_data = [
			'customer_id' 		=> $policy_record->customer_id,
            'credit_note_date'  => date('Y-m-d'),
            'voucher_id'   		=> $voucher_id,
            'amount' 			=> $total_refund_amount
        ];

		// --------------------------------------------------------------------

        /**
         * Credit Note Details
         */
        $credit_note_details_data = [
        	[
	        	'description' 	=> "Policy Endorsement - Premium Refund (Policy Code - {$policy_record->code})",
	        	'amount'		=> $gross_premium_amount
	        ]
        ];

        if($stamp_income_amount)
        {
        	$credit_note_details_data[] = [
	        	'description' 	=> "Stamp Duty",
	        	'amount'		=> $stamp_income_amount
	        ];
        }

        if($vat_payable_amount)
        {
        	$credit_note_details_data[] = [
	        	'description' 	=> "VAT",
	        	'amount'		=> $vat_payable_amount
	        ];
        }

        if($amt_cancellation_fee)
        {
        	$credit_note_details_data[] = [
	        	'description' 	=> "Cancellation Charge",
	        	'amount'		=> $amt_cancellation_fee
	        ];
        }




		// --------------------------------------------------------------------

		/**
		 * Save Credit Note
		 */
		try {

			/**
			 * Task 1: Save Credit Note and Generate Credit Note Code
			 */
			$credit_note_id = $this->ac_credit_note_model->add($credit_note_data, $credit_note_details_data, $policy_record->id);

		} catch (Exception $e) {

			return $this->template->json([
				'title' 	=> 'Exception Occured!',
				'status' 	=> 'error',
				'message' 	=> $e->getMessage()
			]);
		}
		$flag_exception = FALSE;
		$message = '';


		/**
		 * --------------------------------------------------------------------
		 * Post Credit Note Add Tasks
		 *
		 * NOTE
		 * 		We perform post voucher add tasks which are mainly to update
		 * 		voucher internal relation with policy txn record and  update
		 * 		policy installment status.
		 *
		 * 		Please note that, if any of installment fails or exception
		 * 		happens, we rollback and disable voucher. (We can not delete
		 * 		voucher as we need to maintain sequential order for audit trail)
		 * --------------------------------------------------------------------
		 */


		/**
         * ==================== MANUAL TRANSACTIONS BEGIN =========================
         */


            /**
             * Disable DB Debugging
             */
            $this->db->db_debug = FALSE;
            // $this->db->trans_start();
            $this->db->trans_begin();


                // --------------------------------------------------------------------

				/**
				 * Task 2: Update Installment Status to "Credit Noted", Clean Cache
				 */
				try{

					/**
					 * If first installment of this transaction
					 */
					if($installment_record->flag_first == IQB_FLAG_ON)
					{
						$this->endorsement_model->update_status($endorsement_record, IQB_POLICY_ENDORSEMENT_STATUS_INVOICED);
					}
					$this->policy_installment_model->to_invoiced($installment_record);

				} catch (Exception $e) {

					$flag_exception = TRUE;
					$message = $e->getMessage();
				}

                // --------------------------------------------------------------------

                /**
                 * Task 3: Update Relation Table Flag - "flag_invoiced"
                 */
                if( !$flag_exception )
				{
					$this->load->model('rel_policy_voucher_model');
					$rel_base_where = [
						'voucher_id' 	=> $voucher_id,
					];
	                $this->rel_policy_voucher_model->flag_invoiced($rel_base_where, IQB_FLAG_INVOICED__YES);

	            	// Clear Voucher Cache for This Policy
	            	$cache_var = 'ac_voucher_list_by_policy_' . $policy_record->id;
					$this->ac_voucher_model->clear_cache($cache_var);
            	}

                // --------------------------------------------------------------------


				/**
				 * Task 4: Save Credit Note PDF (Original)
				 */
				try{
					$credit_note_data = [
						'record' 	=> $this->ac_credit_note_model->get($credit_note_id),
						'rows' 		=> $this->ac_credit_note_detail_model->rows_by_credit_note($credit_note_id)
					];
					// _CREDIT_NOTE__pdf($credit_note_data, 'save');

				} catch (Exception $e) {

					$flag_exception = TRUE;
					$message = $e->getMessage();
				}

			/**
             * Complete transactions or Rollback
             */
			if ($flag_exception === TRUE || $this->db->trans_status() === FALSE)
			{
		        $this->db->trans_rollback();

		        /**
            	 * Set Credit Note Flag Complete to OFF
            	 */
            	$this->ac_credit_note_model->disable_invoice($credit_note_id);

            	return $this->template->json([
					'title' 	=> 'Something went wrong!',
					'status' 	=> 'error',
					'message' 	=> $message ? $message : 'Could not update policy installment status or voucher relation flag'
				]);
			}
			else
			{
			        $this->db->trans_commit();
			}

            /**
             * Restore DB Debug Configuration
             */
            $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;

        /**
         * ==================== MANUAL TRANSACTIONS END =========================
         */


		// --------------------------------------------------------------------

        /**
		 * Load Portfolio Specific Helper File
		 */
        try { load_portfolio_helper($policy_record->portfolio_id);} catch (Exception $e) {
			return $this->template->json([ 'status' => 'error', 'message' => $e->getMessage()], 404);
		}


		/**
		 * Reload the Policy Overview Tab, Update Installment Row (Replace)
		 */
		$installment_record->status 					= IQB_POLICY_INSTALLMENT_STATUS_INVOICED;
		$installment_record->endorsement_status 		= IQB_POLICY_ENDORSEMENT_STATUS_INVOICED;
		$endorsement_record->status 					= IQB_POLICY_ENDORSEMENT_STATUS_INVOICED;

		$html_tab_ovrview 	= $this->load->view('policies/tabs/_tab_overview', ['record' => $policy_record, 'endorsement_record' => $endorsement_record], TRUE);

		$voucher_record->flag_invoiced = IQB_FLAG_INVOICED__YES;
		$html_voucher_row 	= $this->load->view('accounting/vouchers/_single_row', ['record' => $voucher_record], TRUE);

		$ajax_data = [
			'message' 	=> 'Successfully Updated!',
			'status'  	=> 'success',
			'multipleUpdate' => [
				[
					'box' 		=> '#tab-policy-overview-inner',
					'method' 	=> 'replaceWith',
					'html' 		=> $html_tab_ovrview
				],
				[
					'box' 		=> '#_data-row-voucher-' . $voucher_id,
					'method' 	=> 'replaceWith',
					'html' 		=> $html_voucher_row
				]
			]
		];
		return $this->template->json($ajax_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Make Installment Payment
	 *
	 * @param int $id Installment ID
	 * @param int $invoice_id Invoice ID
	 * @return mixed
	 */
	public function payment($id, $invoice_id)
    {
        /**
         * Check Permissions
         */
        if( !$this->dx_auth->is_authorized('policy_installments', 'make.policy.payment') )
        {
            $this->dx_auth->deny_access();
        }

        /**
         * Get the Policy Installment Record
         */
        $id         = (int)$id;
        $invoice_id = (int)$invoice_id;
        $installment_record = $this->policy_installment_model->get( $id );
		if(!$installment_record)
		{
			return $this->template->json([
				'title' 	=> 'NOT FOUND!',
				'status' 	=> 'error',
				'message' 	=> 'No installment record found.'
			], 400);
		}
        /**
		 * Get the transaction record
		 */
		$endorsement_record = $this->endorsement_model->get( $installment_record->endorsement_id );
		if(!$endorsement_record || !_ENDORSEMENT_is_invoicable_by_type($endorsement_record->txn_type))
		{
			return $this->template->json([
				'title' 	=> 'Invalid Action!',
				'status' 	=> 'error',
				'message' 	=> 'Invalid Endorsement Record and/or Type.'
			], 400);
		}

        // --------------------------------------------------------------------

        /**
         * Policy Record
         */
        $policy_record = $this->policy_model->get($endorsement_record->policy_id);
        if(!$policy_record)
        {
            return $this->template->json([
				'title' 	=> 'NOT FOUND!',
				'status' 	=> 'error',
				'message' 	=> 'No policy record found.'
			], 400);
        }

        // --------------------------------------------------------------------

        /**
         * Record Status Authorized to Make Payment?
         */
        if($installment_record->status !== IQB_POLICY_INSTALLMENT_STATUS_INVOICED)
        {
            return $this->template->json([
                'title'     => 'OOPS!',
                'status'    => 'error',
                'message'   => 'You can not perform this action.'
            ], 404);
        }

        // --------------------------------------------------------------------

        /**
         * Invoice Record? Already Paid?
         */
        $this->load->model('ac_invoice_model');
        $this->load->model('ac_invoice_detail_model');
        $invoice_record = $this->ac_invoice_model->get($invoice_id);
        if(!$invoice_record || $invoice_record->flag_paid == IQB_FLAG_ON)
        {
            return $this->template->json([
                'title'     => 'OOPS!',
                'status'    => 'error',
                'message'   => 'You have already made payment for this Invoice.'
            ], 404);
        }


        // --------------------------------------------------------------------

        /**
         * Load voucher models
         */
        $this->load->model('ac_voucher_model');
        $this->load->model('rel_policy_voucher_model');

        // --------------------------------------------------------------------

        /**
         * Render Payment Form
         */
        if( !$this->input->post() )
		{
			$invoice_data = [
				'record' 	=> $invoice_record,
				'rows' 		=> $this->ac_invoice_detail_model->rows_by_invoice($invoice_record->id)
			];
			return $this->_payment_form($invoice_data);
		}
		else
		{
			$v_rules = $this->_payment_rules();
			$this->form_validation->set_rules($v_rules);
			if($this->form_validation->run() === TRUE )
        	{
        		$payment_data = $this->input->post();
        	}
        	else
        	{
        		return $this->template->json([
	                'title'     => 'Validation Failed!',
	                'status'    => 'error',
	                'message'   => validation_errors()
	            ], 404);
        	}
		}


        // --------------------------------------------------------------------


        /**
         * Let's Build Policy Voucher
         */
        $narration = "Receipt against Policy ({$policy_record->code}) Invoice ({$invoice_record->invoice_code})";
        $narration .= $payment_data['narration'] ? PHP_EOL . $payment_data['narration'] : '';

        $voucher_data = [
            'voucher_date'      => date('Y-m-d'),
            'voucher_type_id'   => IQB_AC_VOUCHER_TYPE_RCPT,
            'narration'         => $narration,
            'flag_internal'     => IQB_FLAG_ON
        ];

        // --------------------------------------------------------------------

        /**
         * Debit Rows
         */
        $dr_rows = [

            /**
             * Accounts
             */
             'accounts' =>  [
                // Collection
                IQB_AC_ACCOUNT_ID_COLLECTION
            ],

            /**
             * Party Types
             */
            'party_types' => [
                NULL
            ],

            /**
             * Party IDs
             */
            'parties' => [

                NULL
            ],

            /**
             * Amounts
             */
            'amounts' => [

                $invoice_record->amount
            ]

        ];

        // --------------------------------------------------------------------

        /**
         * Credit Rows
         */
        $cr_rows = [

            /**
             * Accounts
             */
             'accounts' =>  [
                // Insured Party
                IQB_AC_ACCOUNT_ID_INSURED_PARTY,
            ],

            /**
             * Party Types
             */
            'party_types' => [
                // Insured Party -- Customer
                IQB_AC_PARTY_TYPE_CUSTOMER
            ],

            /**
             * Party IDs
             */
            'parties' => [

                // Insured Party -- Customr ID
                $invoice_record->customer_id
            ],

            /**
             * Amounts
             */
            'amounts' => [

                $invoice_record->amount
            ]

        ];

        // --------------------------------------------------------------------

        /**
         * Format Data
         */
        $voucher_data['account_id']['dr']   = $dr_rows['accounts'];
        $voucher_data['party_type']['dr']   = $dr_rows['party_types'];
        $voucher_data['party_id']['dr']     = $dr_rows['parties'];
        $voucher_data['amount']['dr']       = $dr_rows['amounts'];

        $voucher_data['account_id']['cr']   = $cr_rows['accounts'];
        $voucher_data['party_type']['cr']   = $cr_rows['party_types'];
        $voucher_data['party_id']['cr']     = $cr_rows['parties'];
        $voucher_data['amount']['cr']       = $cr_rows['amounts'];


        // --------------------------------------------------------------------

        /**
         * Save Voucher and Its Relation with Policy
         */

        try {
        	/**
             * Task 1: Save Voucher and Generate Voucher Code
             */
            $voucher_id = $this->ac_voucher_model->add($voucher_data, $policy_record->id);

        } catch (Exception $e) {

            return $this->template->json([
                'title'     => 'Exception Occured!',
                'status'    => 'error',
                'message'   => $e->getMessage()
            ]);
        }

        $flag_exception = FALSE;
        $message = '';


        /**
         * --------------------------------------------------------------------
         * Post Voucher Add Tasks
         *
         * NOTE
         *      We perform post voucher add tasks which are mainly to insert
         *      voucher internal relation with policy txn record and  update
         *      policy status.
         *
         *      Please note that, if any of installment fails or exception
         *      happens, we rollback and disable voucher. (We can not delete
         *      voucher as we need to maintain sequential order for audit trail)
         * --------------------------------------------------------------------
         */


        /**
         * ==================== MANUAL TRANSACTIONS BEGIN =========================
         */


            /**
             * Disable DB Debugging
             */
            $this->db->db_debug = FALSE;
            $this->db->trans_begin();


                // --------------------------------------------------------------------

                /**
                 * Task 2: Add Voucher-Policy Installment Relation
                 */

                try {

                    $relation_data = [
						'policy_id' 	=> $installment_record->policy_id,
						'voucher_id' 	=> $voucher_id,
						'ref' 			=> IQB_REL_POLICY_VOUCHER_REF_PI,
						'ref_id' 		=> $installment_record->id,
						'flag_invoiced' => IQB_FLAG_INVOICED__NOT_REQUIRED
					];
                    $this->rel_policy_voucher_model->add($relation_data);

                } catch (Exception $e) {

                    $flag_exception = TRUE;
                    $message = $e->getMessage();
                }

                // --------------------------------------------------------------------

                /**
                 * Task 4:
                 * 		Update Invoice Paid Flat to "ON"
                 *      Update Policy Status to "Active" (if Fresh or Renewal )
                 *      Post Installment Paid Tasks
                 */
                if( !$flag_exception )
                {
                    try{

                    	// Update Invoice
                    	$this->ac_invoice_model->update_flag($invoice_record->id, 'flag_paid', IQB_FLAG_ON);

                    	/**
						 * If first installment of this transaction, activate the transaction
						 */
						if($installment_record->flag_first == IQB_FLAG_ON)
						{
							$this->endorsement_model->update_status($endorsement_record, IQB_POLICY_ENDORSEMENT_STATUS_ACTIVE);
						}

						// Post Paid Tasks
                        $this->policy_installment_model->post_paid_tasks($installment_record);

                    } catch (Exception $e) {

                        $flag_exception = TRUE;
                        $message = $e->getMessage();
                    }
                }

                // --------------------------------------------------------------------

                /**
                 * Task 5:
                 *      - Save Receipt
                 *      - Generate Receipt PDF (Original)
                 */
                if( !$flag_exception )
                {

                	$receipt_data = [
                		'invoice_id' 		=> $invoice_record->id,
                		'customer_id' 		=> $policy_record->customer_id,
                		'adjustment_amount' => $payment_data['adjustment_amount'] ? $payment_data['adjustment_amount'] : NULL,
                		'amount' 			=> $invoice_record->amount,
                		'received_in'		=> $payment_data['received_in'],
                		'received_in_date'	=> $payment_data['received_in_date'] ? $payment_data['received_in_date'] : NULL,
                		'received_in_ref' 	=> $payment_data['received_in_ref'] ? $payment_data['received_in_ref'] : NULL,
                	];
                	$this->load->model('ac_receipt_model');
                    try{

                        if( $this->ac_receipt_model->add($receipt_data) )
                        {
                        	// Save Receipt PDF
                        	$receipt_data = [
								'record' 			=> $this->ac_receipt_model->find_by(['invoice_id' => $invoice_record->id]),
								'invoice_record' 	=> $invoice_record
							];

							// _RECEIPT__pdf($receipt_data, 'save');
                        }

                    } catch (Exception $e) {

                        $flag_exception = TRUE;
                        $message = $e->getMessage();
                    }
                }

                // --------------------------------------------------------------------



            /**
             * Complete transactions or Rollback
             */
            if ($flag_exception === TRUE || $this->db->trans_status() === FALSE)
            {
                $this->db->trans_rollback();

                /**
                 * Set Voucher Flag Complete to OFF
                 */
                $this->ac_voucher_model->disable_voucher($voucher_id);

                return $this->template->json([
                    'title'     => 'Something went wrong!',
                    'status'    => 'error',
                    'message'   => $message ? $message : 'Could not perform save voucher relation or update policy status'
                ]);
            }
            else
            {
                    $this->db->trans_commit();


                    /**
                     * Post Commit Tasks
                     * -----------------
                     *
                     * 1. Clear Cache (Invoice, RI Distribution)
                     * 2. Send SMS
                     */
                    $cache_var = 'ac_invoice_list_by_policy_'.$policy_record->id;
                    $this->ac_invoice_model->clear_cache($cache_var);

                    $this->load->model('ri_transaction_model');
                    $cache_var = 'ri_txn_list_by_policy_'.$policy_record->id;
                    $this->ri_transaction_model->clear_cache($cache_var);

                    // Send SMS
                    // $this->_sms_activation($endorsement_record, $policy_record, $invoice_record, $installment_record);
            }

            /**
             * Restore DB Debug Configuration
             */
            $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;

        /**
         * ==================== MANUAL TRANSACTIONS END =========================
         */


        // --------------------------------------------------------------------

        /**
		 * Load Portfolio Specific Helper File
		 */
        try { load_portfolio_helper($policy_record->portfolio_id);} catch (Exception $e) {
			return $this->template->json([ 'status' => 'error', 'message' => $e->getMessage()], 404);
		}


        /**
         * Reload the Policy Overview Tab, Update Installment Row (Replace)
         */
        $installment_record->status 					= IQB_POLICY_INSTALLMENT_STATUS_PAID;
		$installment_record->endorsement_status 	= IQB_POLICY_ENDORSEMENT_STATUS_ACTIVE;
        $endorsement_record->status 					= IQB_POLICY_ENDORSEMENT_STATUS_ACTIVE;
        $policy_record->status      					= IQB_POLICY_STATUS_ACTIVE;
        $invoice_record->flag_paid  					= IQB_FLAG_ON;

        $this->load->model('rel_policy_bsrs_heading_model');
		$html_tab_ovrview = $this->load->view('policies/tabs/_tab_overview', [
			'record' => $policy_record,
			'endorsement_record' => $endorsement_record,
			'bsrs_headings_policy' => $this->rel_policy_bsrs_heading_model->by_policy($policy_record->id)
		], TRUE);


        $html_invoice_row 	= $this->load->view('accounting/invoices/_single_row', ['record' => $invoice_record], TRUE);
        $ajax_data = [
            'message'   => 'Successfully Updated!',
            'status'    => 'success',
            'hideBootbox' => true,
            'multipleUpdate' => [
                [
                    'box'       => '#tab-policy-overview-inner',
                    'method'    => 'replaceWith',
                    'html'      => $html_tab_ovrview
                ],
                [
                    'box'       => '#_data-row-invoice-' . $invoice_record->id,
                    'method'    => 'replaceWith',
                    'html'      => $html_invoice_row
                ]
            ]
        ];
        return $this->template->json($ajax_data);
    }



	    private function _payment_form( $invoice_data )
	    {
	        $form_data = [
	            'form_elements' => $this->_payment_rules(),
	            'record' 		=> NULL,
	            'invoice_data' 	=> $invoice_data
	        ];

	        /**
	         * Render The Form
	         */
	        $json_data = [
	            'form' => $this->load->view('accounting/invoices/_form_payment', $form_data, TRUE)
	        ];
	        $this->template->json($json_data);
	    }

	        private function _payment_rules()
	        {

	        	$received_in_dropdown = ac_payment_receipt_mode_dropdown(FALSE);
	            return [
	                [
	                    'field' => 'narration',
	                    'label' => 'Narration',
	                    'rules' => 'trim|max_length[255]',
	                    '_type' => 'textarea',
	                    'rows'  => '3',
	                ],
	                [
	                    'field' => 'adjustment_amount',
	                    'label' => 'Adjustment Amount',
	                    'rules' => 'trim|prep_decimal|decimal|max_length[20]',
	                    '_type' => 'text',
	                ],
	                [
	                    'field' => 'received_in',
	                    'label' => 'Received In',
	                    'rules' => 'trim|required|alpha|exact_length[1]|in_list[' . implode(',', array_keys($received_in_dropdown)) . ']',
	                    '_type'     => 'dropdown',
	                    '_data'     => IQB_BLANK_SELECT + $received_in_dropdown,
	                    '_required' => true
	                ],
	                [
	                    'field' => 'received_in_date',
	                    'label' => 'Dated (Cheque/Draft)',
	                    'rules' => 'trim|valid_date',
	                    '_type'             => 'date',
	                    '_extra_attributes' => 'data-provide="datepicker-inline"',
	                    '_required' => false
	                ],
	                [
	                    'field' => 'received_in_ref',
	                    'label' => 'Reference (Cheque No./Draft No.)',
	                    'rules' => 'trim|max_length[100]',
	                    '_type' => 'text',
	                ]

	            ];
	        }

    // --------------------------------------------------------------------

	/**
	 * Make Installment Refund Complete
	 *
	 * !!! NOTE !!!
	 * Please note that, the refund (payment) voucher must be made manually from
	 * accounting section and only after that, you can perform this action.
	 *
	 * @param int $id Installment ID
	 * @param int $credit_note_id Credit Note ID
	 * @return mixed
	 */
	public function refund($id, $credit_note_id)
    {
        /**
         * Check Permissions
         */
        if( !$this->dx_auth->is_authorized('policy_installments', 'make.policy.refund') )
        {
            $this->dx_auth->deny_access();
        }

		// --------------------------------------------------------------------

        /**
         * Get the Policy Installment Record
         */
        $id         		= (int)$id;
        $credit_note_id 	= (int)$credit_note_id;
        $installment_record = $this->policy_installment_model->get( $id );
		if(!$installment_record)
		{
			$this->template->render_404();
		}

		/**
		 * Belongs to Me? i.e. My Branch? OR Terminate
		 */
		belongs_to_me($installment_record->branch_id);


        /**
         * Record Status Authorized to Make Refund?
         */
        if($installment_record->status !== IQB_POLICY_INSTALLMENT_STATUS_INVOICED)
        {
            return $this->template->json([
                'title'     => 'OOPS!',
                'status'    => 'error',
                'message'   => 'You can not perform this action.'
            ], 404);
        }

		// --------------------------------------------------------------------

        /**
		 * Get the endorsement record, Valid Type?
		 */
		$endorsement_record = $this->endorsement_model->get( $installment_record->endorsement_id );
		if(!$endorsement_record || $endorsement_record->txn_type != IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_REFUND)
		{
			return $this->template->json([
				'title' 	=> 'Invalid Action!',
				'status' 	=> 'error',
				'message' 	=> 'Invalid Endorsement Record and/or Type.'
			], 400);
		}

        // --------------------------------------------------------------------

        /**
         * Policy Record
         */
        $policy_record = $this->policy_model->get($endorsement_record->policy_id);
        if(!$policy_record)
        {
            return $this->template->json([
				'title' 	=> 'Invalid Action!',
				'status' 	=> 'error',
				'message' 	=> 'Policy record not found!'
			], 400);
        }

        // --------------------------------------------------------------------

        /**
         * Credit Note Record? Already Paid?
         */
        $this->load->model('ac_credit_note_model');
        $this->load->model('ac_credit_note_detail_model');
        $credit_note_record = $this->ac_credit_note_model->get($credit_note_id);
        if(!$credit_note_record || $credit_note_record->flag_paid == IQB_FLAG_ON)
        {
            return $this->template->json([
                'title'     => 'OOPS!',
                'status'    => 'error',
                'message'   => 'You have already made payment for this Credit Note.'
            ], 404);
        }


        // --------------------------------------------------------------------

        /**
         * Load voucher models
         */
        $this->load->model('ac_voucher_model');
        $this->load->model('rel_policy_voucher_model');

        // --------------------------------------------------------------------

        /**
         * Render Refund Form
         */
        if( !$this->input->post() )
		{
			$credit_note_data = [
				'record' 	=> $credit_note_record,
				'rows' 		=> $this->ac_credit_note_detail_model->rows_by_credit_note($credit_note_record->id)
			];
			return $this->_refund_form($credit_note_data);
		}
		else
		{
			$v_rules = $this->_refund_rules();
			$this->form_validation->set_rules($v_rules);
			if($this->form_validation->run() === TRUE )
        	{
        		$refund_data = $this->input->post();
        	}
        	else
        	{
        		return $this->template->json([
	                'title'     => 'Validation Failed!',
	                'status'    => 'error',
	                'message'   => validation_errors()
	            ], 404);
        	}
		}

        // --------------------------------------------------------------------


		/**
		 * Let's Get the Payment Voucher Record
		 */
		$voucher_id 	= (int)$this->input->post('voucher_id');
		$voucher_record = $this->ac_voucher_model->get($voucher_id);
		if(
			// Voucher Record Exists?
			!$voucher_record
				||
			// Voucher Complete
			$voucher_record->flag_complete != IQB_FLAG_ON
				||
			// Valid Voucher Type
			$voucher_record->voucher_type_id != IQB_AC_VOUCHER_TYPE_PMNT )
        {
            return $this->template->json([
                'title'     => 'OOPS!',
                'status'    => 'error',
                'message'   => 'Invalid Voucher Record.'
            ], 404);
        }


		/**
		 * Belongs to Me? i.e. My Branch? OR Terminate
		 */
		belongs_to_me($voucher_record->branch_id, TRUE, 'Voucher does not belong to your branch.');


        $flag_exception = FALSE;
        $message = '';


        /**
         * --------------------------------------------------------------------
         * Post Voucher Validation Tasks
         *
         * NOTE
         *      We perform post voucher add tasks which are mainly to insert
         *      voucher internal relation with policy txn record and  update
         *      policy status
         * --------------------------------------------------------------------
         */


        /**
         * ==================== MANUAL TRANSACTIONS BEGIN =========================
         */


            /**
             * Disable DB Debugging
             */
            $this->db->db_debug = FALSE;
            $this->db->trans_begin();


                // --------------------------------------------------------------------

                /**
                 * Task 1: Add Voucher-Policy Installment Relation
                 */

                try {

                    $relation_data = [
						'policy_id' 	=> $installment_record->policy_id,
						'voucher_id' 	=> $voucher_id,
						'ref' 			=> IQB_REL_POLICY_VOUCHER_REF_PI,
						'ref_id' 		=> $installment_record->id,
						'flag_invoiced' => IQB_FLAG_INVOICED__NOT_REQUIRED
					];
                    $this->rel_policy_voucher_model->add($relation_data);

                } catch (Exception $e) {

                    $flag_exception = TRUE;
                    $message = $e->getMessage();
                }

                // --------------------------------------------------------------------

                /**
                 * Task 2:
                 * 		Update Credit Note Paid Flat to "ON"
                 *      Update Policy Status to "Active" (if Fresh or Renewal ) or "Cancel" if to Terminate
                 *      Post Installment Tasks
                 */
                if( !$flag_exception )
                {
                    try{

                    	$this->ac_credit_note_model->update_flag($credit_note_record->id, 'flag_paid', IQB_FLAG_ON);

                    	/**
						 * If first installment of this endorsement, activate the endorsement
						 */
                    	// Post Paid Tasks
                        $this->policy_installment_model->post_paid_tasks($installment_record);

						if($installment_record->flag_first == IQB_FLAG_ON)
						{

							/**
							 * TERMINATE POLICY?
							 *
							 * If this endorsement is "Refund & Terminate" or "Terminate"
							 * we have to terminate the policy.
							 */
							$terminate_policy = FALSE;
							if($endorsement_record->flag_terminate_on_refund == IQB_FLAG_YES )
							{
								$terminate_policy = TRUE;
							}

							// Activate endorsement and cancel policy if needed.
							$this->endorsement_model->update_status($endorsement_record, IQB_POLICY_ENDORSEMENT_STATUS_ACTIVE, $terminate_policy);
						}
                    } catch (Exception $e) {

                        $flag_exception = TRUE;
                        $message = $e->getMessage();
                    }
                }
                // --------------------------------------------------------------------

            /**
             * Complete transactions or Rollback
             */
            if ($flag_exception === TRUE || $this->db->trans_status() === FALSE)
            {
                $this->db->trans_rollback();

                /**
                 * Set Voucher Flag Complete to OFF
                 */
                $this->ac_voucher_model->disable_voucher($voucher_id);

                return $this->template->json([
                    'title'     => 'Something went wrong!',
                    'status'    => 'error',
                    'message'   => $message ? $message : 'Could not perform refund operations.'
                ]);
            }
            else
            {
                    $this->db->trans_commit();


                    /**
                     * Post Commit Tasks
                     * -----------------
                     *
                     * 1. Clear Cache (Credit Notes and Vouchers, RI Distribution)
                     * 2. Send SMS
                     */
                    $cache_var = 'ac_credit_note_list_by_policy_'.$policy_record->id;
                    $this->ac_credit_note_model->clear_cache($cache_var);

                    $cache_var = 'ac_voucher_list_by_policy_'.$policy_record->id;
                	$this->ac_voucher_model->clear_cache($cache_var);

                	$this->load->model('ri_transaction_model');
                    $cache_var = 'ri_txn_list_by_policy_'.$policy_record->id;
                    $this->ri_transaction_model->clear_cache($cache_var);

                    // Send SMS
                    $this->_sms_activation($endorsement_record, $policy_record, $credit_note_record, $installment_record);
            }

            /**
             * Restore DB Debug Configuration
             */
            $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;

        /**
         * ==================== MANUAL TRANSACTIONS END =========================
         */


        // --------------------------------------------------------------------

        /**
		 * Load Portfolio Specific Helper File
		 */
        try { load_portfolio_helper($policy_record->portfolio_id);} catch (Exception $e) {
			return $this->template->json([ 'status' => 'error', 'message' => $e->getMessage()], 404);
		}


        /**
         * Reload the Policy Overview Tab, Update Installment Row (Replace)
         */
        $installment_record->status 					= IQB_POLICY_INSTALLMENT_STATUS_PAID;
		$installment_record->endorsement_status 		= IQB_POLICY_ENDORSEMENT_STATUS_ACTIVE;
        $endorsement_record->status 					= IQB_POLICY_ENDORSEMENT_STATUS_ACTIVE;
        $policy_record->status      					= IQB_POLICY_STATUS_ACTIVE;
        $credit_note_record->flag_paid  				= IQB_FLAG_ON;

        $html_tab_ovrview   = $this->load->view('policies/tabs/_tab_overview', ['record' => $policy_record, 'endorsement_record' => $endorsement_record], TRUE);
        $html_credit_note_row 	= $this->load->view('accounting/credit_notes/_single_row', ['record' => $credit_note_record], TRUE);
        $ajax_data = [
            'message'   => 'Successfully Updated!',
            'status'    => 'success',
            'hideBootbox' => true,
            'multipleUpdate' => [
                [
                    'box'       => '#tab-policy-overview-inner',
                    'method'    => 'replaceWith',
                    'html'      => $html_tab_ovrview
                ],
                [
                    'box'       => '#_data-row-credit_note-' . $credit_note_record->id,
                    'method'    => 'replaceWith',
                    'html'      => $html_credit_note_row
                ]
            ]
        ];
        return $this->template->json($ajax_data);
    }

    	private function _refund_form( $credit_note_data )
	    {
	        $form_data = [
	            'form_elements' => $this->_refund_rules(),
	            'record' 		=> NULL,
	            'credit_note_data' 	=> $credit_note_data
	        ];

	        /**
	         * Render The Form
	         */
	        $json_data = [
	            'form' => $this->load->view('accounting/credit_notes/_form_refund', $form_data, TRUE)
	        ];
	        $this->template->json($json_data);
	    }

	        private function _refund_rules()
	        {
	            return [
	                [
	                    'field' => 'voucher_id',
	                    'label' => 'Voucher ID',
	                    'rules' => 'trim|required|integer|max_length[20]',
	                    '_type' => 'text',
	                ]

	            ];
	        }

	// --------------------------------------------------------------------

    /**
     * Send Activation SMS
     * ---------------------
     * Case 1: Fresh/Renewal/Transactional - After making payment, it gets activated automatically
     * Case 2: General Endorsement - After activating
     *
     * @param object $transaction_record
     * @param object $policy_record
     * @param object $invoice_record / $credit_note_record
     * @param object $installment_record
     * @return bool
     */
	private function _sms_activation( $endorsement_record, $policy_record, $invoice_record = NULL, $installment_record = NULL)
	{
		$customer_name 		= $policy_record->customer_name;
		$customer_contact 	= $policy_record->customer_contact ? json_decode($policy_record->customer_contact) : NULL;
		$mobile 			= $customer_contact->mobile ? $customer_contact->mobile : NULL;

		if( !$mobile )
		{
			return FALSE;
		}

		$message 	= "Dear {$customer_name}," . PHP_EOL;
		$txn_type 	= (int)$endorsement_record->txn_type;

		$amount = abs($invoice_record->amount ?? 0);

		/**
		 * Compose Message By Types
		 */
		if( _ENDORSEMENT_is_first($txn_type) )
		{
			// First Installment
    		if($installment_record->flag_first == IQB_FLAG_ON)
			{
				$message .= "Your Policy has been issued." . PHP_EOL .
        					"Policy No: " . $policy_record->code . PHP_EOL .
        					"Premium Paid(Rs): " . $amount . PHP_EOL .
        					"Expires on : " . $policy_record->end_date . PHP_EOL;
			}

			// Other Installment
			else
			{
				$message .= "Your Policy installment has been issued." . PHP_EOL .
        					"Policy No: " . $policy_record->code . PHP_EOL .
        					"Premium Paid(Rs): " . $amount . PHP_EOL;
			}
		}

		/**
		 * Premium Upgrade or Ownership Transfer ( Customer pays the Premium)
		 */
		else if( in_array($txn_type, [IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_UPGRADE, IQB_POLICY_ENDORSEMENT_TYPE_OWNERSHIP_TRANSFER]) )
		{
			$message .= "Your Policy endorsement has been issued." . PHP_EOL .
        					"Policy No: " . $policy_record->code . PHP_EOL .
        					"Premium Paid(Rs): " . $amount . PHP_EOL;
		}

		/**
		 * Premium Refund ( Customer gets the refund amount)
		 */
		else if( $txn_type == IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_REFUND )
		{

			if($endorsement_record->flag_terminate_on_refund == IQB_FLAG_YES )
			{
				$message .= "Your Policy endorsement has been issued and policy has been terminated." . PHP_EOL;
			}
			else
			{
				$message .= "Your Policy endorsement has been issued." . PHP_EOL ;
			}

			$message .= "Policy No: " . $policy_record->code . PHP_EOL .
        				"Premium Refunded(Rs): " . $amount . PHP_EOL;
		}

		/**
		 * General Endorsement
		 */
		else
		{
			$message .= "Your Policy Endorsement has been issued." . PHP_EOL .
    					"Policy No: " . $policy_record->code . PHP_EOL;
		}

    	/**
    	 * Add Signature
    	 */
    	$message .= PHP_EOL . SMS_SIGNATURE;

    	/**
    	 * Let's Fire the SMS
    	 */
    	$this->load->helper('sms');
    	$result = send_sms($mobile, $message);
	}

}