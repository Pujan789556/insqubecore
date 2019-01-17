<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Department: Index View
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
						<a href="#" title="Add new department"
							class="btn btn-success btn-round trg-dialog-edit" data-title='<i class="fa fa-pencil-square-o"></i> Add New Department' data-url="<?php echo site_url( $this->data['_url_base'] . '/add/');?>" data-form=".form-iqb-general"
						><i class="ion-plus-circled"></i> Add</a>
						<a href="<?php echo site_url( $this->data['_url_base'] . '/flush/' );?>" title="Flush Cache"
							class="btn btn-warning btn-round"
							data-toggle="tooltip"
						><i class="fa fa-trash-o"></i> Flush Cache</a>
					</div>
				</div>
			</div>
			<!-- /.box-header -->
			<div class="box-body table-responsive data-rows" id="iqb-data-list">
				<?php
				/**
				 * Load Rows from View
				 */
				$this->load->view('setup/departments/_list');
				?>
			</div>
			<!-- /.box-body -->
		</div>
		<!-- /.box -->
	</div>
</div>