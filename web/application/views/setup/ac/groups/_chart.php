<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Account Group: Index View
*/
?>
<div class="row">
	<div class="col-xs-12">
		<div class="box">
			<div class="box-header with-border gray">
				<div class="row">
					<div class="col-sm-12 master-actions text-right">
						<a href="<?php echo site_url('ac_account_groups/chart/print/');?>"
							title="Print Chart of Accounts"
							data-toggle="tooltip"
							class="btn bg-navy btn-round"
							target="_blank"
							download="chart-of-accounts.pdf"
						><i class="fa fa-print"></i> Print</a>
						<a href="<?php echo site_url('ac_account_groups');?>"
							title="Back to Account Groups"
							data-toggle="tooltip"
							class="btn btn-primary btn-round"
						><i class="fa fa-angle-double-left"></i> Back</a>
					</div>
				</div>
			</div>
			<!-- /.box-header -->
			<div class="box-body table-responsive no-padding" id="iqb-data-list">
				<?php
				/**
				 * Load Rows from View
				 */
				$this->load->view('setup/ac/groups/_chart_data');
				?>
			</div>
			<!-- /.box-body -->
		</div>
		<!-- /.box -->
	</div>
</div>