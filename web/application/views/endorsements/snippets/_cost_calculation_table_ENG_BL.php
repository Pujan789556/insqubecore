<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Policy: Details - Policy Premium Overview Card - BOILER EXPLOSION (ENG)
*/
$cost_calculation_table = json_decode($endorsement_record->cost_calculation_table ?? NULL);
$total_premium          = _ENDORSEMENT__total_premium($endorsement_record);
$grand_total            = _ENDORSEMENT__grand_total($endorsement_record);
?>
<div class="box-body">
	<table class="table no-margin table-bordered">
		<tbody id="_premium-details">
			<?php if($cost_calculation_table): ?>

				<?php foreach($cost_calculation_table as $row):?>
					<tr>
						<th><?php echo $row->label ?></th>
						<td class="text-right"><?php echo number_format( (float)$row->value, 2);?></td>
					</tr>
				<?php endforeach ?>
				<tr>
				    <td class="no-padding" colspan="2">
				        <table class="table no-margin table-bordered table-condensed">
				            <tr>
				                <td width="80%" class="text-right"><strong>Gross Premium</strong></td>
				                <td class="text-right"><strong><?php echo number_format((float)$total_premium, 2)?></strong></td>
				            </tr>
				            <tr>
				                <td class="text-right"><strong>Stamp Duty</strong></td>
				                <td class="text-right"><strong><?php echo number_format($endorsement_record->net_amt_stamp_duty, 2);?></strong></td>
				            </tr>
				            <tr>
				                <td class="text-right"><strong>VAT</strong></td>
				                <td class="text-right"><strong><?php echo number_format( (float)$endorsement_record->net_amt_vat, 2);?></strong></td>
				            </tr>
				            <tr>
				                <td class="text-right"><strong>Total Premium</strong></td>
				                <td class="text-right"><strong><?php echo number_format( (float)$grand_total , 2);?></strong></td>
				            </tr>
				        </table>
				    </td>
				</tr>
			<?php else: ?>
				<tr><td class="text-muted text-center">No Premium Information Found!</td></tr>
			<?php endif ?>
		</tbody>
	</table>
</div>