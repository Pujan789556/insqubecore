<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Account - Voucher Type - Index View
*/
?>
<div class="row">
	<div class="col-xs-12">
		<div class="box">
			<div class="box-header gray">
				<div class="row">
					<div class="col-xs-12">
						<?php
						/**
						 * Load Live Search UI
						 */
						$this->load->view('templates/_common/_live_search');
						?>
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