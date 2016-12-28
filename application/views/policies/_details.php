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
				<div class="active tab-pane" id="tab-overview">
					<div class="row">
						<div class="col-sm-6">
							<div class="box box-default">
								<div class="box-header with-border">
					              	<h3 class="box-title">Customer Details</h3>
					            </div>
					            <div class="box-body">
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
					            </div>
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