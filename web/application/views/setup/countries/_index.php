<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Countries: Index View
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
				</div>
			</div>
			<!-- /.box-header -->
			<div class="box-body table-responsive data-rows">
				<table class="table table-hover" id="live-searchable">
					<tr>
						<th>Name</th>
						<th>Alpha 2</th>
						<th>Alpha 3</th>
						<th>Dial Code</th>
						<th>Currency Code</th>
						<th>Currency Name</th>
						<th>Actions</th>
					</tr>
					<?php
					/**
					 * Load Rows from View
					 */
					foreach($records as $record)
					{
						$this->load->view('setup/countries/_single_row', compact('record'));
					}
					?>
				</table>
			</div>
			<!-- /.box-body -->
		</div>
		<!-- /.box -->
	</div>
</div>