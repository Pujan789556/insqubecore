<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Setup - RI - Treaty : Details View
*/
?>
<div class="row">
	<div class="col-md-4">

		<div class="box box-primary">
			<div class="box-body box-profile">

				<h3 class="profile-username text-center"><?php echo  $record->name;?></h3>

				<ul class="list-group list-group-unbordered">
					<li class="list-group-item">
						<b>Fiscal Year</b> <span class="pull-right"><?php echo $record->fy_code_np . " ({$record->fy_code_en})"?></span>
					</li>
					<li class="list-group-item">
						<b>Treaty Type</b> <span class="pull-right"><?php echo $record->treaty_type_name ;?></span>
					</li>
					<li class="list-group-item">
						<b>Contract Currency</b> <span class="pull-right"><?php echo $record->currency_contract ;?></span>
					</li>
					<li class="list-group-item">
						<b>Settelment Currency</b> <span class="pull-right"><?php echo $record->currency_settlement ;?></span>
					</li>
					<li class="list-group-item">
						<b>Estimated Premium Income</b> <span class="pull-right"><?php echo $record->estimated_premium_income ;?></span>
					</li>

				</ul>
			</div>
		</div>

		<!-- About Me Box -->
		<div class="box box-primary">
			<div class="box-header"><h3 class="box-title">Brokers</h3></div>
		</div>
		<!-- /.box -->
	</div>

	<!-- /.col -->
	<div class="col-md-8">
		<div class="nav-tabs-custom">
			<ul class="nav nav-tabs">
				<li class="active"><a href="#tab-branches" data-toggle="tab">Treaty Details</a></li>
				<li><a href="#tab-reports" data-toggle="tab">Portfolios</a></li>
				<li><a href="#tab-reports" data-toggle="tab">Distribution</a></li>
				<li><a href="#tab-reports" data-toggle="tab">Tax &amp; Commission</a></li>
			</ul>
			<div class="tab-content">
				<div class="active tab-pane" id="tab-branches">
					<?php
					/**
					 * Load Rows from View
					 */
					// $this->load->view('setup/company_branches/_index', [
					// 	'records' 			=> $branches,
					// 	'company_record' 	=> $record,
					// 	'add_url' 			=> 'companies/branch/add/' . $record->id
					// ]);
					?>
				</div>
				<!-- /.tab-pane -->
				<div class="tab-pane" id="tab-reports">

				</div>
				<!-- /.tab-pane -->
			</div>
			<!-- /.tab-content -->
		</div>
		<!-- /.nav-tabs-custom -->
	</div>
	<!-- /.col -->
</div>