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
						<a href="<?php echo site_url( $this->data['_url_base'] . '/agriculture/flush/');?>" title="Flush Cache"
							class="btn btn-warning btn-round"
							data-toggle="tooltip"
						><i class="fa fa-trash-o"></i> Flush Cache</a>

						<a href="#" title="Add New Agriculture Tarrif"
							class="btn btn-success btn-round trg-dialog-edit"
							data-box-size="large"
							data-toggle="tooltip"
							data-title='<i class="fa fa-pencil-square-o"></i> Add New Agriculture Tarrif'
							data-url="<?php echo site_url($this->data['_url_base'] . '/agriculture/add/');?>"
							data-form=".form-iqb-general"
						><i class="ion-plus-circled"></i> Add</a>
					</div>
				</div>
			</div>
			<!-- /.box-header -->
			<div class="box-body table-responsive data-rows" id="iqb-data-list">
				<?php
				/**
				 * Load Rows from View
				 */
				$this->load->view($this->data['_view_base'] . '/agriculture/_list');
				?>
			</div>
			<!-- /.box-body -->
		</div>
		<!-- /.box -->
	</div>
</div>