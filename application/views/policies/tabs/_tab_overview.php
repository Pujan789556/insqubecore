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
		<div class="col-sm-6 col-md-4">
			<?php
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
				'contact' 			=> $record->customer_contact
			];
			/**
			* Customer Widget
			*/
			$this->load->view('customers/snippets/_widget_profile', ['record' => $customer_record]);
			?>
			<?php
			/**
			* Sales Staff Card
			*/
			$this->load->view('policies/snippets/_sales_staff_card', ['record' => $record]);
			if($record->flag_dc == 'C')
			{
				/**
				* Agent Widget
				*/
				$agent_record = (object)[
								'id' 			=> $record->agent_id,
								'name' 			=> $record->agent_name,
							'picture' 		=> $record->agent_picture,
							'ud_code' 		=> $record->agent_ud_code,
							'bs_code' 		=> $record->agent_bs_code,
								'type' 			=> $record->agent_type,
							'active' 		=> $record->agent_active,
							'contact' 		=> $record->agent_contact
				];
				$this->load->view('setup/agents/snippets/_widget_profile', ['record' => $agent_record]);
			}
			?>
		</div>
		<div class="col-sm-6 col-md-8">
			<?php
			/**
			* Policy Overvivew Card
			*/
			$this->load->view('policies/snippets/_policy_overview_card', ['record' => $record]);


			/**
			* Policy Object Card
			*/
			$__flag_object_editable = FALSE;
			if( in_array($record->status, [IQB_POLICY_STATUS_DRAFT, IQB_POLICY_STATUS_UNVERIFIED]) && $this->dx_auth->is_authorized_any('policies', ['edit.draft.policy', 'edit.unverified.policy']) )
			{
				$__flag_object_editable = TRUE;
			}
			$object_record = (object)[
				'id' 			=> $record->object_id,
	            'portfolio_id'  => $record->portfolio_id,
	            'attributes'    => $record->object_attributes
	        ];
			$this->load->view('objects/snippets/_object_card', ['record' => $object_record, '__flag_object_editable' => $__flag_object_editable]);
			?>
		</div>
	</div>
</div>