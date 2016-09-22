<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Districts: Index View
*/
// echo '<pre>'; print_r($records);exit;
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
			<div class="box-body table-responsive no-padding">
				<table class="table table-hover" id="live-searchable">
					<tr>
						<th>ID</th>
						<th>Name (EN)</th>
						<th>Name(NP)</th>
						<th>Actions</th>
					</tr>					
					<?php
					/**
					 * Load Rows from View
					 */ 
					foreach($records as $record)
					{
						$this->load->view('setup/districts/_single_row', compact('record'));
					}
					?>				
				</table>
			</div>
			<!-- /.box-body -->
		</div>
		<!-- /.box -->
	</div>
</div>