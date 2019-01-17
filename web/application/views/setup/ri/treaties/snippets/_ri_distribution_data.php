<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Setup - RI - Treaty : RI Distribution - Data View
*/
?>
<table class="table table-condensed table-responsive">
	<thead>
		<tr>
			<th>SN</th>
			<th>Broker</th>
			<th>Reinsurer Company</th>
			<th class="text-right">Distribution(%)</th>
			<th>&nbsp;</th>
		</tr>
	</thead>
	<tbody>
		<?php
		$sn = 1;
		$total_percentage = 0.00;
		foreach($treaty_distribution as $distrib):?>
			<tr>
				<td><?php echo $sn++;?>.</td>
				<td>
					<?php if($distrib->broker_id): ?>
						<a href="<?php echo site_url('admin/companies/details/' . $distrib->broker_id)?>" target="_blank">
							<?php echo $distrib->broker_name?>
						</a>
					<?php else: ?>
						Direct
					<?php endif ?>
				</td>
				<td>
					<a href="<?php echo site_url('admin/companies/details/' . $distrib->company_id)?>" target="_blank">
						<?php echo $distrib->reinsurer_name?>
					</a>
				</td>

				<td class="text-right"><?php echo $distrib->distribution_percent?></td>
				<td><?php echo $distrib->flag_leader ? '<span class="text-success"><i class="fa fa-check"></i> Leader</span>' : '';?></td>
			</tr>
		<?php
			$total_percentage += $distrib->distribution_percent;
		endforeach?>
		<tr>
			<td colspan="3" class="text-bold text-right">Total(%):</td>
			<td class="text-right"><?php echo number_format($total_percentage, 2);?></td>
			<td>&nbsp;</td>
		</tr>
	</tbody>
</table>