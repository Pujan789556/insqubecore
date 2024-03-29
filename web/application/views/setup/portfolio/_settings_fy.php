<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Portfolio: Settings - By Fiscal Year : Index View
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
						<a
							data-toggle="tooltip"
							href="<?php echo site_url( $this->data['_url_base'] . '/settings/');?>" title="Back to Portfolio Settings"
							class="btn btn-warning btn-round" >
							<i class="fa fa-chevron-left"></i> Back</a>
					</div>
				</div>
			</div>
			<!-- /.box-header -->
			<div class="box-body table-responsive no-padding" id="iqb-data-list">
				<?php
				/**
				 * Load Rows from View
				 */
				$this->load->view($this->data['_view_base'] . '/_list_settings_fy');
				?>
			</div>
			<!-- /.box-body -->
		</div>
		<!-- /.box -->
	</div>
</div>