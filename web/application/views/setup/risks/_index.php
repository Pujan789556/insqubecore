<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Risk: Index View
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
					<div class="col-sm-6 master-actions">
						<a href="#" title="Add new risk"
							class="btn btn-success btn-round pull-right trg-dialog-edit" data-title='<i class="fa fa-pencil-square-o"></i> Add New Risk' data-url="<?php echo site_url('risks/add/');?>" data-form=".form-iqb-general"
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
				$this->load->view('setup/risks/_list');
				?>
			</div>
			<!-- /.box-body -->
		</div>
		<!-- /.box -->
	</div>
</div>