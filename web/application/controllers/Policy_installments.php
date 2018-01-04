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

		// $txn_record = $this->policy_transaction_model->get($txn_record->id);


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
	//  STATUS UPGRADE/DOWNGRADE
	// --------------------------------------------------------------------

	/**
	 * Upgrade/Downgrade Status of a Policy Txn Record
	 *
	 * @param integer $id Policy ID
	 * @param char $to_status_code Status Code
	 * @param string $ref 	Where does this requrest come from?
	 * @return json
	 */
	public function status($id, $to_status_code, $ref='tab-policy-transactions')
	{
		$id = (int)$id;
		$txn_record = $this->policy_installment_model->get($id);
		if(!$txn_record)
		{
			$this->template->render_404();
		}

		// is This Current Installment?
		if( $txn_record->flag_current != IQB_FLAG_ON  )
		{
			return $this->template->json([
				'status' 	=> 'error',
				'message' 	=> 'Invalid Current Policy Installment Record!'
			], 400);
		}

		/**
		 * Check Permission
		 * -----------------
		 * You need to have permission to modify the given status.
		 */
		$this->__check_status_permission($to_status_code, $txn_record);


		/**
		 * Meet the Status Pre-Requisite ?
		 */
		$this->__status_qualifies($to_status_code, $txn_record);


		/**
		 * Let's Update the Status
		 */
		try {

			if( $this->policy_installment_model->update_status($txn_record, $to_status_code) )
			{
				/**
				 * Updated Installment & Policy Record
				 */
				$txn_record = $this->policy_installment_model->get($txn_record->id);
				$policy_record = $this->policy_model->get($txn_record->policy_id);


				/**
				 * Post Tasks on Installment Activation
				 * -------------------------------------
				 *
				 * If this is not a General Endorsement Installment, we also have to update the
				 * 	- policy (from audit_policy field if any data)
				 * 	- object (from audit_object field if any data)
				 * 	- customer (from audit_customer field if any data)
				 * 	- SEND SMS on General Installment Activation
				 */
				if( $txn_record->txn_type == IQB_POLICY_TXN_TYPE_EG && $to_status_code ==IQB_POLICY_TXN_STATUS_ACTIVE )
				{
					$this->_sms_activation($txn_record, $policy_record);
				}


				/**
				 * Load Portfolio Specific Helper File
				 */
				try { load_portfolio_helper($policy_record->portfolio_id);} catch (Exception $e) {
					return $this->template->json([ 'status' => 'error', 'message' => $e->getMessage()], 404);
				}

				/**
				 * What to reload/render after success?
				 * -----------------------------------
				 * 	1. RI Approval Triggered from Policy Overview Tab
				 */
				if( $ref == 'tab-policy-overview' )
				{
					/**
					 * Update View
					 */
					$view = 'policies/tabs/_tab_overview';
					$html = $this->load->view($view, ['record' => $policy_record, 'txn_record' => $txn_record], TRUE);

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
								'html' 		=> $policy_record->code
							]
						]
					];
					return $this->template->json($ajax_data);
				}
				else
				{
					// Replace the Row
					$html = $this->load->view('policy_installments/_single_row', ['record' => $txn_record, 'policy_record' => $policy_record], TRUE);
					return $this->template->json([
						'message' 	=> 'Successfully Updated!',
						'status'  	=> 'success',
						'multipleUpdate' => [
							[
								'box' 		=> '#_data-row-policy_installments-' . $txn_record->id,
								'method' 	=> 'replaceWith',
								'html' 		=> $html
							]
						]
					]);
				}
			}

		} catch (Exception $e) {

			return $this->template->json([
				'status' 	=> 'error',
				'title' 	=> 'Exception Occured.',
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
		 * @param bool $terminate_on_fail Terminate right here on fails
		 * @return mixed
		 */
		private function __check_status_permission($to_updown_status, $terminate_on_fail = TRUE)
		{
			/**
			 * Check Permission
			 * ------------------------------
			 *
			 */
			$status_keys = array_keys(get_policy_txn_status_dropdown(FALSE));

			// Valid Status Code?
			if( !in_array($to_updown_status, $status_keys ) )
			{
				return $this->template->json([
					'status' 	=> 'error',
					'message' 	=> 'Invalid Status Code!'
				], 403);
			}

			/**
			 * Admin?
			 */
			if($this->dx_auth->is_admin())
			{
				return TRUE;
			}


			// Valid Permission?
			$__flag_valid_permission = FALSE;
			$permission_name 	= '';
			switch ($to_updown_status)
			{
				case IQB_POLICY_TXN_STATUS_DRAFT:
					$permission_name = 'status.to.draft';
					break;

				case IQB_POLICY_TXN_STATUS_VERIFIED:
					$permission_name = 'status.to.verified';
					break;

				case IQB_POLICY_TXN_STATUS_RI_APPROVED:
					$permission_name = 'status.to.ri.approved';
					break;

				case IQB_POLICY_TXN_STATUS_VOUCHERED:
					$permission_name = 'status.to.vouchered';
					break;

				case IQB_POLICY_TXN_STATUS_INVOICED:
					$permission_name = 'status.to.invoiced';
					break;

				case IQB_POLICY_TXN_STATUS_ACTIVE:
					$permission_name = 'status.to.active';
					break;

				default:
					break;
			}
			if( $permission_name !== ''  && $this->dx_auth->is_authorized('policy_installments', $permission_name) )
			{
				$__flag_valid_permission = TRUE;
			}

			if( !$__flag_valid_permission && $terminate_on_fail )
			{
				$this->dx_auth->deny_access();
			}

			return $__flag_valid_permission;
		}

		// --------------------------------------------------------------------

		/**
		 * Status Qualifies to UP/DOWN
		 *
		 * This is very important that not all type of policy txn has manu status
		 * up/down facility. So we have to look into the type of Policy Txn Record Type and
		 * follow the logic accordingly.
		 *
		 * @param alpha $to_updown_status Status Code to UP/DOWN
		 * @param object $txn_record Policy Installment Record
		 * @param bool $terminate_on_fail Terminate right here on fails
		 * @return mixed
		 */
		private function __status_qualifies($to_updown_status, $txn_record, $terminate_on_fail = TRUE)
		{
			$__flag_passed = $this->policy_installment_model->status_qualifies($txn_record->status, $to_updown_status);

			if( $__flag_passed )
			{
				/**
				 * FRESH/RENEWAL Policy Installment
				 * 	Draft/Verified are automatically triggered from
				 * 	Policy Status Update Method
				 */
				if( $txn_record->txn_type == IQB_POLICY_TXN_TYPE_FRESH  || $txn_record->txn_type == IQB_POLICY_TXN_TYPE_RENEWAL )
				{
					$__flag_passed = !in_array($to_updown_status, [
						IQB_POLICY_TXN_STATUS_DRAFT,
						IQB_POLICY_TXN_STATUS_VERIFIED,
						IQB_POLICY_TXN_STATUS_ACTIVE
					]);
				}
			}

			/**
			 * Can not Update Transactional Status Directly using status function
			 */
			if( $__flag_passed )
			{
				$__flag_passed = !in_array($to_updown_status, [
					IQB_POLICY_TXN_STATUS_VOUCHERED,
					IQB_POLICY_TXN_STATUS_INVOICED
				]);
			}

			/**
			 * General Endorsement
			 * Activate Status
			 *
			 * !!! If RI-Approval Constraint Required, It should Come from That Status else from Verified
			 */
			if( $__flag_passed && $to_updown_status === IQB_POLICY_TXN_STATUS_ACTIVE && $txn_record->txn_type == IQB_POLICY_TXN_TYPE_EG )
			{
				if( (int)$txn_record->flag_ri_approval === IQB_FLAG_ON )
				{
					$__flag_passed = $txn_record->status === IQB_POLICY_TXN_STATUS_RI_APPROVED;
				}
				else
				{
					$__flag_passed = $txn_record->status === IQB_POLICY_TXN_STATUS_VERIFIED;
				}
			}


			if( !$__flag_passed && $terminate_on_fail )
			{
				return $this->template->json([
					'status' 	=> 'error',
					'title' 	=> 'Invalid Status Installment',
					'message' 	=> 'You can not swith to the state from this state of installment.'
				], 400);
			}

			return $__flag_passed;

		}

	// --------------------- END: STATUS UPGRADE/DOWNGRADE --------------------


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
		$transaction_record = $this->policy_transaction_model->get( $installment_record->policy_transaction_id );
		if(!$transaction_record)
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
		$this->load->model('rel_policy_installment_voucher_model');

		/**
		 * Check if Voucher already generated for this Installment
		 */
		if( $this->rel_policy_installment_voucher_model->voucher_exists($installment_record->id))
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
		 */
		$narration = 'POLICY VOUCHER - POLICY CODE : ';

		// policy code if not second installment
		if($installment_record->flag_first == IQB_FLAG_OFF)
		{
			$narration .= $policy_record->code;
		}

		$voucher_data = [
            'voucher_date'      => date('Y-m-d'),
            'voucher_type_id'   => IQB_AC_VOUCHER_TYPE_PRI,
            'narration'         => $narration,
            'flag_internal'     => IQB_FLAG_ON
        ];

		// --------------------------------------------------------------------


        /**
         * Voucher Amount Computation
         */
        $gross_premium_amount 		= $installment_record->amt_total_premium;
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
			return $this->template->json([
				'title' 	=> 'Direct Premium Income Account Missing!',
				'status' 	=> 'error',
				'message' 	=> "Please add portfolio 'Direct Premium Income Account' for portfolio ({$portfolio_record->name_en}) from Master Setup > Portfolio"
			], 404);
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
            // $this->db->trans_start();
            $this->db->trans_begin();


                // --------------------------------------------------------------------

            	/**
				 * Task 2: Add Voucher-Policy Installment Relation
				 */

				try {

					$relation_data = [
						'policy_installment_id' => $installment_record->id,
						'voucher_id' 			=> $voucher_id
					];
					$this->rel_policy_installment_voucher_model->add($relation_data);

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
							$this->policy_transaction_model->update_status($transaction_record, IQB_POLICY_TXN_STATUS_VOUCHERED);
						}

						// Update installment status
						$this->policy_installment_model->update_status($installment_record, IQB_POLICY_INSTALLMENT_STATUS_VOUCHERED);

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
						in_array($transaction_record->txn_type, [IQB_POLICY_TXN_TYPE_FRESH, IQB_POLICY_TXN_TYPE_RENEWAL])
					)
				{
					try{

						$policy_code = $this->policy_model->generate_policy_number( $policy_record );
						if($policy_code)
						{
							$policy_record->code = $policy_code;

							// Update Voucher Narration
							$narration .= $policy_code;
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
		$installment_record->status 					= IQB_POLICY_INSTALLMENT_STATUS_VOUCHERED;
		$installment_record->policy_transaction_status 	= IQB_POLICY_TXN_STATUS_VOUCHERED;
		$transaction_record->status 					= IQB_POLICY_TXN_STATUS_VOUCHERED;

		$html_tab_ovrview = $this->load->view('policies/tabs/_tab_overview', ['record' => $policy_record, 'txn_record' => $transaction_record], TRUE);
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
		$txn_record = $this->policy_installment_model->get( $id );
		if(!$txn_record)
		{
			$this->template->render_404();
		}

		// --------------------------------------------------------------------

		/**
		 * Get Voucher Record By Policy Installment Relation
		 */
		$this->load->model('ac_voucher_model');
		$voucher_record = $this->ac_voucher_model->get_voucher_by_policy_txn_relation($txn_record->id, $voucher_id);
		if(!$voucher_record)
		{
			$this->template->render_404();
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
		$policy_record = $this->policy_model->get($txn_record->policy_id);
		if(!$policy_record)
		{
			$this->template->render_404();
		}

		// --------------------------------------------------------------------

		/**
		 * Record Status Authorized to Generate Voucher?
		 */
		if($txn_record->status !== IQB_POLICY_TXN_STATUS_VOUCHERED || $voucher_record->flag_invoiced != IQB_FLAG_OFF )
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

		/**
         * Voucher Amount Computation
         */
        $gross_premium_amount 		= $txn_record->amt_total_premium;
        $stamp_income_amount 		= $txn_record->amt_stamp_duty;
        $vat_payable_amount 		= $txn_record->amt_vat;

        $total_to_receive_from_insured_party_amount = $gross_premium_amount + $stamp_income_amount + $vat_payable_amount;

		$invoice_data = [
			'customer_id' 		=> $policy_record->customer_id,
            'invoice_date'      => date('Y-m-d'),
            'voucher_id'   		=> $voucher_id,
            'amount' 			=> $total_to_receive_from_insured_party_amount
        ];

		// --------------------------------------------------------------------

        /**
         * Invoice Details
         */
        $invoice_details_data = [
        	[
	        	'description' 	=> "Policy Premium Amount (Policy Code - {$policy_record->code})",
	        	'amount'		=> $gross_premium_amount
	        ],
	        [
	        	'description' 	=> "Stamp Duty",
	        	'amount'		=> $stamp_income_amount
	        ],
	        [
	        	'description' 	=> "VAT",
	        	'amount'		=> $vat_payable_amount
	        ]
        ];


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
				 * Task 2: Update Installment Status to "Invoiced", Clean Cache
				 */
				try{

					$this->policy_installment_model->update_status($txn_record, IQB_POLICY_TXN_STATUS_INVOICED);

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
					$this->load->model('rel_policy_installment_voucher_model');
					$rel_base_where = [
						'policy_txn_id' => $txn_record->id,
						'voucher_id' 	=> $voucher_id
					];
	                $this->rel_policy_installment_voucher_model->update_by($rel_base_where, [
	                	'flag_invoiced' => IQB_FLAG_ON
	            	]);

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
					_INVOICE__pdf($invoice_data, 'save');

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
		$txn_record->status = IQB_POLICY_TXN_STATUS_INVOICED;
		$html_tab_ovrview 	= $this->load->view('policies/tabs/_tab_overview', ['record' => $policy_record, 'txn_record' => $txn_record], TRUE);

		$voucher_record->flag_invoiced = IQB_FLAG_ON;
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
         * Get the Policy Fresh/Renewal Txn Record
         */
        $id         = (int)$id;
        $invoice_id = (int)$invoice_id;
        $txn_record = $this->policy_installment_model->get( $id );
        if(!$txn_record)
        {
            $this->template->render_404();
        }

        // --------------------------------------------------------------------

        /**
         * Policy Record
         */
        $policy_record = $this->policy_model->get($txn_record->policy_id);
        if(!$policy_record)
        {
            $this->template->render_404();
        }

        // --------------------------------------------------------------------

        /**
         * Record Status Authorized to Make Payment?
         */
        if($txn_record->status !== IQB_POLICY_TXN_STATUS_INVOICED)
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
        $this->load->model('rel_policy_installment_voucher_model');

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
                $policy_record->customer_id
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
                        'policy_txn_id' => $txn_record->id,
                        'voucher_id'    => $voucher_id,
                        'flag_invoiced' => IQB_FLAG_NOT_REQUIRED
                    ];
                    $this->rel_policy_installment_voucher_model->add($relation_data);

                } catch (Exception $e) {

                    $flag_exception = TRUE;
                    $message = $e->getMessage();
                }

                // --------------------------------------------------------------------

                /**
                 * Task 4:
                 * 		Update Invoice Paid Flat to "ON"
                 *      Update Policy Status to "Active" (if Fresh or Renewal )
                 *      Update Installment Status to "Active", Clean Cache, (Commit endorsement if ET or EG)
                 */
                if( !$flag_exception )
                {
                    try{

                    	$this->ac_invoice_model->update_flag($invoice_record->id, 'flag_paid', IQB_FLAG_ON);
                        $this->policy_installment_model->update_status($txn_record, IQB_POLICY_TXN_STATUS_ACTIVE);

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

							_RECEIPT__pdf($receipt_data, 'save');
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
                     * 1. Clear Cache
                     * 2. Send SMS
                     */
                    $cache_var = 'ac_invoice_list_by_policy_'.$policy_record->id;
                    $this->ac_invoice_model->clear_cache($cache_var);

                    // Send SMS
                    $this->_sms_activation($txn_record, $policy_record, $invoice_record);
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
        $txn_record->status         = IQB_POLICY_TXN_STATUS_ACTIVE;
        $policy_record->status      = IQB_POLICY_STATUS_ACTIVE;
        $invoice_record->flag_paid  = IQB_FLAG_ON;
        $html_tab_ovrview           = $this->load->view('policies/tabs/_tab_overview', ['record' => $policy_record, 'txn_record' => $txn_record], TRUE);
        $html_invoice_row       	= $this->load->view('accounting/invoices/_single_row', ['record' => $invoice_record], TRUE);
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

    	/**
         * Send Activation SMS
         * ---------------------
         * Case 1: Fresh/Renewal/Transactional - After making payment, it gets activated automatically
         * Case 2: General Endorsement - After activating
         *
         * @param object $txn_record
         * @param object $policy_record
         * @return bool
         */
    	private function _sms_activation($txn_record, $policy_record, $invoice_record = NULL)
    	{
    		$customer_name 		= $policy_record->customer_name;
    		$customer_contact 	= $policy_record->customer_contact ? json_decode($policy_record->customer_contact) : NULL;
    		$mobile 			= $customer_contact->mobile ? $customer_contact->mobile : NULL;

    		if( !$mobile )
    		{
    			return FALSE;
    		}

    		$message = "Dear {$customer_name}," . PHP_EOL;

    		if( in_array($txn_record->txn_type, [IQB_POLICY_TXN_TYPE_FRESH, IQB_POLICY_TXN_TYPE_RENEWAL]) )
        	{
        		$message .= "Your Policy has been issued." . PHP_EOL .
        					"Policy No: " . $policy_record->code . PHP_EOL .
        					"Premium Paid(Rs): " . $invoice_record->amount . PHP_EOL .
        					"Expires on : " . $policy_record->end_date . PHP_EOL;
        	}
        	else if( $txn_record->txn_type == IQB_POLICY_TXN_TYPE_ET )
        	{
        		$message .= "Your Policy Endorsement has been issued." . PHP_EOL .
        					"Policy No: " . $policy_record->code . PHP_EOL .
        					"Amount Paid(Rs): " . $invoice_record->amount . PHP_EOL;
        	}
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
}