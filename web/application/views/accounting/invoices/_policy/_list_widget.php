<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Policy Invoices: List View
*/
?>
<div class="row" id="list-widget-policy-invoices">
	<div class="col-xs-12">
		<div class="box no-border">
			<div class="box-header gray">
				<h2 class="page-header">Policy Invoices</h2>
				<div class="row">
					<div class="col-sm-6">
						<?php
						/**
						 * Load Live Search UI
						 */
						$this->load->view('templates/_common/_live_search',['options' => json_encode(['rows'=>'#search-result-invoice tr.searchable'])]);
						?>
					</div>
					<div class="col-sm-6 master-actions text-right">
						<a href="#"
							title="Flush Cache"
							data-confirm="false"
							data-url="<?php echo site_url( 'ac_invoices/flush_by_policy/' . $policy_id );?>"
							class="btn btn-warning btn-round trg-dialog-action"
							data-toggle="tooltip"
						><i class="fa fa-trash-o"></i> Flush Cache</a>
					</div>
				</div>
			</div>
			<!-- /.box-header -->
			<div class="box-body table-responsive data-rows">
				<?php
				/**
				 * Load Rows from View
				 */
				$this->load->view('accounting/invoices/_list');
				?>
			</div>
		</div>
	</div>
</div>