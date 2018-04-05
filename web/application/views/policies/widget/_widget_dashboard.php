<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Policy:  Dashboard Widget
*/
?>
<div class="box box-info">
	<div class="box-header with-border">
		<h3 class="box-title">Latest Policies</h3>
		<div class="box-tools pull-right">
			<button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
			</button>
			<!-- <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button> -->
		</div>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<div class="table-responsive">
			<table class="table no-margin">
				<thead>
					<tr>
						<th>Policy Code</th>
						<th>Customer</th>
						<th>Portfolio</th>
						<th>Dates</th>
						<th>Status</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($records as $record): ?>
						<tr class="searchable">
							<td>
								<a href="<?php echo site_url('policies/details/' . $record->id);?>"
												title="View policy details.">
												<?php echo $record->code;?></a>
							</td>
							<td><?php echo $record->customer_name;?></td>
							<td><?php echo $record->portfolio_name;?></td>

							<!-- <td><?php echo $record->type == 'N' ? 'Fresh' : 'Renewal';?></td> -->
							<td><?php echo $record->start_date . ' - ' . $record->end_date;?></td>
							<td><?php echo _POLICY_status_text($record->status);?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<!-- /.table-responsive -->
	</div>
	<!-- /.box-body -->
	<div class="box-footer clearfix">
		<a href="<?php echo site_url('policies') ?>" class="btn btn-sm btn-default btn-flat pull-right">View All Policies</a>
	</div>
	<!-- /.box-footer -->
</div>