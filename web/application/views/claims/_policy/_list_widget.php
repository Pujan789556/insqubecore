<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Policy Claims: List View
*/
?>
<div class="row" id="list-widget-policy-claims">
	<div class="col-xs-12">
		<div class="box no-border">
			<div class="box-header gray bg-gray">
				<div class="row page-header">
					<div class="col-sm-6">
						<h3 class="no-margin-t no-margin-b">Manage Claims</h3>
					</div>
					<div class="col-sm-6 master-actions text-right">
						<?php if( $this->dx_auth->is_authorized('claims', 'add.claim') ): ?>
							<a href="#"
								title="Add New Claim"
								data-toggle="tooltip"
								class="btn btn-success btn-round trg-dialog-edit"
								data-box-size="full-width"
								data-title='<i class="fa fa-pencil-square-o"></i> Add New Claim'
								data-url="<?php echo site_url($add_url);?>"
								data-form="#_form-claims"
							><i class="ion-plus-circled"></i> Add</a>
						<?php endif?>

						<a href="#"
							title="Flush Cache"
							data-confirm="false"
							data-url="<?php echo site_url( 'claims/flush_by_policy/' . $policy_id );?>"
							class="btn btn-warning btn-round trg-dialog-action"
							data-toggle="tooltip"
						><i class="fa fa-trash-o"></i> Flush Cache</a>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-6">
						<?php
						/**
						 * Load Live Search UI
						 */
						$this->load->view('templates/_common/_live_search',['options' => json_encode(['rows'=>'#search-result-claims tr.searchable'])]);
						?>
					</div>
				</div>
			</div>
			<!-- /.box-header -->
			<div class="box-body table-responsive data-rows">
				<?php
				/**
				 * Load Rows from View
				 */
				$this->load->view('claims/_list');
				?>
			</div>
		</div>
	</div>
</div>