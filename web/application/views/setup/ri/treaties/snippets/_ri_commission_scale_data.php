<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Setup - RI - Treaty : RI Commission Scale Data - Data View
*/
?>
<table class="table table-condensed table-responsive">
	<thead>
		<tr>
			<th>SN</th>
			<th>Title</th>
			<th>Min(%)</th>
			<th>Max(%)</th>
			<th>Rate(%)</th>
		</tr>
	</thead>
	<tbody>
		<?php
		$sn = 1;
		$commission_scales = $record->commission_scales ? json_decode($record->commission_scales) : NULL;
		if($commission_scales):
			foreach($commission_scales as $scale):?>
				<tr>
					<td><?php echo $sn++;?>.</td>
					<td><?php echo $scale->name?></td>
					<td><?php echo $scale->scale_min?></td>
					<td><?php echo $scale->scale_max?></td>
					<td><?php echo $scale->rate?></td>
				</tr>
		<?php
			endforeach;
		endif;?>
	</tbody>
</table>