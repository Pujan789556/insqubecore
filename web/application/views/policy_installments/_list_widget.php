<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Policy Installments: List View
*/
?>
<div class="row" id="list-widget-policy_installments">
	<div class="col-xs-12">
		<div class="box">
			<div class="box-header with-border gray bg-gray">
				<div class="row page-header">
					<div class="col-sm-6">
						<h3 class="no-margin-t no-margin-b">Manage Installments / Refunds</h3>
					</div>
					<div class="col-sm-6 master-actions text-right">
						<a href="#"
							title="Flush Cache"
							data-confirm="false"
							data-url="<?php echo site_url( 'policy_installments/flush/' . $policy_record->id );?>"
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
						$this->load->view('templates/_common/_live_search',['options' => json_encode(['rows'=>'#search-result-policy_installments tr.searchable'])]);
						?>
					</div>

				</div>
			</div>
			<!-- /.box-header -->
			<div class="box-body table-responsive no-padding" id="_iqb-data-list-box-policy_installments">
				<?php
				/**
				 * Load Rows from View
				 */
				$this->load->view('policy_installments/_list');
				?>
			</div>
			<!-- /.box-body -->
		</div>
		<!-- /.box -->
	</div>
</div>