<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Branch: Index View
*/
?>
<div class="row">
	<div class="col-xs-12">
		<div class="box">
			<div class="box-header gray">
				<div class="row">
					<div class="col-sm-6">
						<?php
						/**
						 * Load Live Search UI
						 */
						$this->load->view('templates/_common/_live_search');
						?>
					</div>
					<div class="col-sm-6 master-actions text-right">
						<a href="#" title="Add new Branch Targets"
							class="btn btn-success btn-round trg-dialog-edit"
							data-box-size="large"
							data-title='<i class="fa fa-pencil-square-o"></i> Add New Branch Targets'
							data-url="<?php echo site_url('branches/add_targets/');?>"
							data-form=".form-iqb-general"
						><i class="ion-plus-circled"></i> Add</a>
					</div>
				</div>
			</div>
			<!-- /.box-header -->
			<div class="box-body table-responsive no-padding" id="iqb-data-list">
				<?php
				/**
				 * Load Rows from View
				 */
				$this->load->view('setup/branches/_list_targets');
				?>
			</div>
			<!-- /.box-body -->
		</div>
		<!-- /.box -->
	</div>
</div>