<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Policy Vouchers: List View
*/
?>
<div class="row" id="list-widget-policy-vouchers">
	<div class="col-xs-12">
		<div class="box no-border">
			<div class="box-header gray bg-gray">
				<div class="row page-header">
					<div class="col-sm-6">
						<h3 class="no-margin-t no-margin-b">Manage Vouchers</h3>
					</div>
					<div class="col-sm-6 master-actions text-right">
						<a href="#"
							title="Flush Cache"
							data-confirm="false"
							data-url="<?php echo site_url( 'ac_vouchers/flush_by_policy/' . $policy_id );?>"
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
						$this->load->view('templates/_common/_live_search',['options' => json_encode(['rows'=>'#search-result-voucher tr.searchable'])]);
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
				$this->load->view('accounting/vouchers/_list');
				?>
			</div>
		</div>
	</div>
</div>