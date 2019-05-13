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
		<div class="col-md-6">
			<?php
			/**
			 * Policy Overvivew Card
			 */
			$this->load->view('policies/snippets/_policy_overview_card', ['record' => $record]);


			/**
			 * Render Cost Calculation Table
			 */
			$this->load->view('endorsements/_cost_calculation_table', ['endorsement_record' => $endorsement_record, 'policy_record' => $record]);

			/**
			 * Beema Samit Report Information
			 *
			 * !!! NOTE !!!
			 *
			 * Agriculture Portfolios - NOT Required
			 */
			if( !in_array( $record->portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__AGR) ) )
			{
				$this->load->view('policies/snippets/_policy_bsrs_headings');
			}
			?>
		</div>
		<div class="col-md-6 no-padding-l-col-md">
			<?php

			/**
			* Policy Object Card
			*/
			$__flag_object_editable = _POLICY_is_editable($record->status, FALSE);
			$object_record = (object)[
				'id' 				=> $record->object_id,
	            'portfolio_id'  	=> $record->portfolio_id,
	            'portfolio_name_en' => $record->portfolio_name_en,
	            'customer_name_en' 	=> $record->customer_name_en,
	            'amt_sum_insured' 	=> $record->object_amt_sum_insured,
	            'amt_max_liability' 		=> $record->object_amt_max_liability,
	            'amt_third_party_liability' => $record->object_amt_third_party_liability,
	            'attributes'    			=> $record->object_attributes,
	            'flag_locked'				=> $record->object_flag_locked,
	        ];
			$this->load->view('objects/snippets/_object_card', ['record' => $object_record, '__flag_object_editable' => $__flag_object_editable]);


			/**
			* Customer Overview
			*/
			$customer_record = (object)[
				'id' 				=> $record->customer_id,
				'full_name_en' 		=> $record->customer_name_en,
				'full_name_np' 		=> $record->customer_name_np,
				'grandfather_name' 	=> $record->customer_grandfather_name,
				'father_name'		=> $record->customer_father_name,
				'mother_name'		=> $record->customer_mother_name,
				'spouse_name'		=> $record->customer_spouse_name,
				'picture' 			=> $record->customer_picture,
				'code' 				=> $record->customer_code,
				'type' 				=> $record->customer_type,
				'company_reg_no' 	=> $record->company_reg_no,
				'identification_no' => $record->identification_no,
				'dob' 				=> $record->dob,
				'pan' 				=> $record->customer_pan,
				'profession' 		=> $record->customer_profession,
				'flag_locked'		=> $record->customer_flag_locked
			];

			$customer_address_record = parse_address_record($record, 'addr_customer_');
			/**
			* Customer Widget
			*/
			$this->load->view('customers/snippets/_widget_profile', ['record' => $customer_record, 'address_record' => $customer_address_record]);
			?>
		</div>
	</div>
</div>