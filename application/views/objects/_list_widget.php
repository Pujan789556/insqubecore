<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Customer: Subscription View
*/
?>
<div class="row">
	<div class="col-xs-12">
		<div class="box no-border">
			<div class="box-header gray">
				<div class="row">
					<div class="col-sm-6">
						<?php
						/**
						 * Load Live Search UI
						 */
						$this->load->view('templates/_common/_live_search',['options' => json_encode(['rows'=>'#search-result-object tr.searchable'])]);
						?>
					</div>
					<div class="col-sm-6 master-actions">
						<?php if( $this->dx_auth->is_authorized('objects', 'add.object') ): ?>
							<a href="#"
								title="Add New Object"
								data-toggle="tooltip"
								class="btn btn-success btn-round trg-dialog-edit pull-right"
								data-box-size="large"
								data-title='<i class="fa fa-pencil-square-o"></i> Add New Object'
								data-url="<?php echo site_url('objects/add/' . $customer_record->id);?>"
								data-form="#_form-object"
							><i class="ion-plus-circled"></i> Add</a>
						<?php endif?>

					</div>
				</div>
			</div>
			<!-- /.box-header -->
			<div class="box-body table-responsive no-padding" id="_iqb-data-list-box-object">
				<?php
				/**
				 * Load Rows from View
				 */
				$this->load->view('objects/_list');
				?>
			</div>
			<!-- /.box-body -->
		</div>
		<!-- /.box -->
	</div>
</div>