<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Policy Vouchers: List View
*/
?>
<div class="row">
	<div class="col-xs-12">
		<div class="box no-border">
			<div class="box-header gray">
				<h2 class="page-header">Policy Vouchers</h2>
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