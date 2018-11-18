<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Policy List: List View For Customer Tab
*/
?>
<div class="row" id="list-widget-objects">
	<div class="col-xs-12">
		<div class="box no-border">
			<div class="box-header gray bg-gray">
				<div class="row page-header">
					<div class="col-sm-6">
						<h3 class="no-margin-t no-margin-b">Customer Objects</h3>
					</div>
					<div class="col-sm-6 master-actions text-right">
						<?php if( $this->dx_auth->is_authorized('objects', 'add.object') ): ?>
							<a href="#"
								title="Add New Object"
								data-toggle="tooltip"
								class="btn btn-success btn-round trg-dialog-edit"
								data-box-size="full-width"
								data-title='<i class="fa fa-pencil-square-o"></i> Add New Object'
								data-url="<?php echo site_url($add_url);?>"
								data-form="#_form-object"
							><i class="ion-plus-circled"></i> Add</a>
						<?php endif?>

						<a  href="#"
							class="btn btn-warning btn-round insqube-load"
							data-url="<?php echo site_url( 'objects/by_customer/' . $customer_id . '/1' );?>"
							data-load-method="get"
							data-box="#tab-objects"
							data-method="html"
							role="button"><i class="fa fa-trash-o"></i> Flush Cache</a>
					</div>
				</div>

				<div class="row">
					<div class="col-sm-6">
						<?php
						/**
						 * Load Live Search UI
						 */
						$this->load->view('templates/_common/_live_search',['options' => json_encode(['rows'=>'#search-result-object tr.searchable'])]);
						?>
					</div>
				</div>
			</div>
			<!-- /.box-header -->
			<div class="box-body table-responsive no-padding">
				<?php
				/**
				 * Load Rows from View
				 */
				$this->load->view('objects/_list');
				?>
			</div>
		</div>
	</div>
</div>