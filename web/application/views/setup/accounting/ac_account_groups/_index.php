<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Account Group: Index View
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
						<a href="<?php echo site_url($this->data['_url_base'] . '/chart/');?>"
								title="View Chart of Accounts"
								data-toggle="tooltip"
								class="btn btn-primary btn-round"
							><i class="fa fa-sitemap"></i> Chart</a>
						<a href="#"
							title="Add New Account Group"
							data-toggle="tooltip"
							class="btn btn-success btn-round trg-dialog-edit"
							data-size="large"
							data-title='<i class="fa fa-pencil-square-o"></i> Add New Account Group'
							data-url="<?php echo site_url($this->data['_url_base'] . '/add/');?>"
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
				$this->load->view($this->data['_view_base'] . '/_list');
				?>
			</div>
			<!-- /.box-body -->
		</div>
		<!-- /.box -->
	</div>
</div>