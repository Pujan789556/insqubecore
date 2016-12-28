<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Policy: Details View
*/
?>
<div class="row">

	<div class="col-xs-12">
		<div class="nav-tabs-custom">
			<ul class="nav nav-tabs">
				<li class="active"><a href="#tab-overview" data-toggle="tab">Overview</a></li>
				<li><a href="#timeline" data-toggle="tab">Documents</a></li>
				<li><a href="#targets" data-toggle="tab">Invoices</a></li>
				<li><a href="#settings" data-toggle="tab">Documents</a></li>
				<li><a href="#settings" data-toggle="tab">Claims</a></li>
				<li><a href="#settings" data-toggle="tab">Reports</a></li>
			</ul>
			<div class="tab-content">
				<div class="active tab-pane gray" id="tab-overview">
					<div class="row">
						<div class="col-sm-6">
							<div class="box box-bordered box-info">
								<div class="box-header with-border border-dark">
					              	<h3 class="box-title">Customer Details</h3>
					            </div>
					            <?php
								/**
								 * Customer Overview
								 */
								$customer_record = (object)[
									'full_name' => $record->customer_name,
									'picture' 	=> $record->customer_picture,
									'code' 		=> $record->customer_code,
									'type' 		=> $record->customer_type,
									'company_reg_no' => $record->company_reg_no,
									'citizenship_no' => $record->citizenship_no,
									'passport_no' => $record->passport_no,
									'pan' 		=> $record->customer_pan,
									'profession' => $record->customer_profession
								];
								$this->load->view('customers/snippets/_profile_card', ['record' => $customer_record]);
								?>

								<?php
								/**
								 * Contact Widget
								 */
								echo get_contact_widget($record->customer_contact);
								?>
							</div>

							<div class="box box-bordered box-info">
								<div class="box-header with-border border-dark">
					              	<h3 class="box-title">Sales Staff</h3>
					            </div>
					            <?php
            					$sales_staff_profile = $record->sales_staff_profile ? json_decode($record->sales_staff_profile) : NULL;
            					?>
					            <table class="table no-margin no-border">
				            		<tbody>
				            			<tr>
				            				<td class="text-bold">Username</td>
				            				<td><?php echo $record->sales_staff_username?></td>
				            			</tr>
				            			<tr>
				            				<td class="text-bold">Name</td>
				            				<td><?php echo $sales_staff_profile->name ?? '';?></td>
				            			</tr>
				            			<tr>
				            				<td class="text-bold">Designation</td>
				            				<td><?php echo $sales_staff_profile->designation ?? '';?></td>
				            			</tr>
				            		</tbody>
				            	</table>
							</div>

							<?php if($record->flag_dc == 'C'):?>
								<div class="box box-bordered box-info">
									<div class="box-header with-border border-dark">
						              	<h3 class="box-title">Agent Details</h3>
						            </div>
						            <?php
									/**
									 * Customer Overview
									 */
									$agent_record = (object)[
										'name' 			=> $record->agent_name,
										'picture' 		=> $record->agent_picture,
										'ud_code' 		=> $record->agent_ud_code,
										'bs_code' 		=> $record->agent_bs_code,
										'type' 			=> $record->agent_type,
										'active' 		=> $record->agent_active
									];
									$this->load->view('setup/agents/snippets/_profile_card', ['record' => $agent_record]);
									?>

									<?php
									/**
									 * Contact Widget
									 */
									echo get_contact_widget($record->agent_contact);
									?>
								</div>
							<?php endif?>

						</div>
						<div class="col-sm-6">
							<div class="box box-bordered box-success">
								<div class="box-header with-border border-dark">
					              	<h3 class="box-title">Policy Details</h3>
					            </div>
					            <table class="table no-margin no-border">
				            		<tbody>
				            			<tr>
				            				<td class="text-bold">Policy Code</td>
				            				<td><?php echo $record->code?></td>
				            			</tr>
				            			<tr>
				            				<td class="text-bold">Portfolio</td>
				            				<td><?php echo $record->portfolio_name?></td>
				            			</tr>
				            			<tr>
				            				<td class="text-bold">Policy Package</td>
				            				<td><?php echo _PO_policy_package_dropdown($record->portfolio_id)[$record->policy_package]?></td>
				            			</tr>
				            			<tr>
				            				<td class="text-bold">Policy Issue Date</td>
				            				<td><?php echo $record->issue_date?></td>
				            			</tr>
				            			<tr>
				            				<td class="text-bold">Policy Start Date</td>
				            				<td><?php echo $record->start_date?></td>
				            			</tr>
				            			<tr>
				            				<td class="text-bold">Policy End Date</td>
				            				<td><?php echo $record->end_date?></td>
				            			</tr>
				            			<tr>
				            				<td class="text-bold">Status</td>
				            				<td><?php echo get_policy_status_text($record->status);?></td>
				            			</tr>
				            		</tbody>
				            	</table>
							</div>
							<div class="box box-bordered box-warning">
								<div class="box-header with-border border-dark">
					              	<h3 class="box-title">Policy Object Details</h3>
					            </div>
								<?php
								/**
								 * Policy Object Details
								 */
								$object_record = (object)[
									'portfolio_id' => $record->portfolio_id,
									'attributes' => $record->object_attributes
								];
								$this->load->view('objects/snippets/_popup', ['record' => $object_record]);
								?>
				            </div>
						</div>
					</div>
				</div>
				<!-- /.tab-pane -->
				<div class="tab-pane" id="timeline">

				</div>
				<!-- /.tab-pane -->
				<div class="tab-pane" id="settings">

				</div>
				<!-- /.tab-pane -->
			</div>
			<!-- /.tab-content -->
		</div>
		<!-- /.nav-tabs-custom -->
	</div>
	<!-- /.col -->
</div>