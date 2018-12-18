<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Policy: Details - Policy Premium Overview Card - AGRICULTURE (All sub-portfolios)
*/
$cost_calculation_table = $endorsement_record->cost_calculation_table ? json_decode($endorsement_record->cost_calculation_table) : NULL;
$total_premium = (float)$endorsement_record->net_amt_basic_premium + (float)$endorsement_record->net_amt_pool_premium;
$grand_total = $total_premium + $endorsement_record->net_amt_stamp_duty + $endorsement_record->net_amt_vat;
?>
<div class="box-body">
	<table class="table no-margin table-bordered">
		<tbody id="_premium-details">
			<?php if($cost_calculation_table): ?>

				<?php foreach($cost_calculation_table as $row):?>
					<tr>
						<td class="text-left"><?php echo $row->label ?></td>
						<td class="text-right"><?php echo number_format( (float)$row->value, 2);?></td>
					</tr>
				<?php endforeach ?>
			<?php else: ?>
				<tr><td class="text-muted text-center">No Premium Information Found!</td></tr>
			<?php endif ?>
		</tbody>
	</table>
</div>