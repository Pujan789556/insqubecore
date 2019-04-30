<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Tariff List By Fiscal Year : Agriculture
*/
?>
<div class="row">
	<div class="col-xs-12">
		<div class="box">
			<div class="box-header gray">
				<div class="row">
					<div class="col-xs-12 col-sm-6">
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
				$this->load->view($this->data['_view_base'] . '/agriculture/_list_by_fiscal_year');
				?>
			</div>
			<!-- /.box-body -->
		</div>
		<!-- /.box -->
	</div>
</div>