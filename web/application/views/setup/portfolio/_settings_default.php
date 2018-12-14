<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Portfolio: Settings : Index View
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
						<a href="#" title="Add New Portfolio Settings For New Fiscal Year"
							class="btn btn-success btn-round trg-dialog-edit"
							data-box-size="large"
							data-title='<i class="fa fa-pencil-square-o"></i> Add New Portfolio Settings'
							data-url="<?php echo site_url('portfolio/add_settings/');?>"
							data-form=".form-iqb-general"
						><i class="ion-plus-circled"></i> Add</a>
						<a href="<?php echo site_url( $this->router->class . '/flush_settings/' );?>" title="Flush Cache"
							class="btn btn-warning btn-round"
							data-toggle="tooltip"
						><i class="fa fa-trash-o"></i> Flush Cache</a>
					</div>
				</div>
			</div>
			<!-- /.box-header -->
			<div class="box-body table-responsive no-padding" id="iqb-data-list">
				<?php
				/**
				 * Load Rows from View
				 */
				$this->load->view('setup/portfolio/_list_settings_default');
				?>
			</div>
			<!-- /.box-body -->
		</div>
		<!-- /.box -->
	</div>
</div>