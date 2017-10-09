<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Policy: Details View
*/
?>
<div id="tab-policy-overview-inner">
	<div class="iqb-row-action">
		<div class="row">
			<div class="col-sm-6">
				<?php
				/**
				* Actions
				*/
				$this->load->view('policies/snippets/_status_warning');
				?>
			</div>
			<div class="col-sm-6 text-right">
				<?php
				/**
				* Actions
				*/
				$this->load->view('policies/snippets/_actions');
				?>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6 col-md-7">
			<?php
			/**
			 * Policy Overvivew Card
			 */
			$this->load->view('policies/snippets/_policy_overview_card', ['record' => $record]);


			/**
			 * Render Cost Calculation Table
			 */
			$this->load->view('policy_txn/_cost_calculation_table', ['txn_record' => $txn_record, 'policy_record' => $record]);
			?>
			<h3 class="text-red">@TODO: Current RI Distribution Table</h3>
			<h3 class="text-red">@TODO: Fresh Policy सम्पुष्टि विवरण</h3>
		</div>
		<div class="col-sm-6 col-md-5 no-padding-l-col-sm no-padding-l-col-md">
			<?php

			/**
			* Policy Object Card
			*/
			$__flag_object_editable = is_policy_editable($record->status, FALSE);
			$object_record = (object)[
				'id' 				=> $record->object_id,
	            'portfolio_id'  	=> $record->portfolio_id,
	            'portfolio_name' 	=> $record->portfolio_name,
	            'customer_name' 	=> $record->customer_name,
	            'amt_sum_insured' 	=> $record->object_amt_sum_insured,
	            'attributes'    	=> $record->object_attributes,
	            'flag_locked'		=> $record->object_flag_locked,
	        ];
			$this->load->view('objects/snippets/_object_card', ['record' => $object_record, '__flag_object_editable' => $__flag_object_editable]);


			/**
			* Customer Overview
			*/
			$customer_record = (object)[
				'id' 				=> $record->customer_id,
				'full_name' 		=> $record->customer_name,
				'picture' 			=> $record->customer_picture,
				'code' 				=> $record->customer_code,
				'type' 				=> $record->customer_type,
				'company_reg_no' 	=> $record->company_reg_no,
				'citizenship_no' 	=> $record->citizenship_no,
				'passport_no' 		=> $record->passport_no,
				'pan' 				=> $record->customer_pan,
				'profession' 		=> $record->customer_profession,
				'contact' 			=> $record->customer_contact,
				'flag_locked'		=> $record->customer_flag_locked
			];
			/**
			* Customer Widget
			*/
			$this->load->view('customers/snippets/_widget_profile', ['record' => $customer_record]);
			?>
		</div>
	</div>
</div>