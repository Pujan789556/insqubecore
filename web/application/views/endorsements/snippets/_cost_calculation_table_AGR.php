<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Policy: Details - Policy Premium Overview Card - AGRICULTURE (All sub-portfolios)
*/
$cost_calculation_table = $txn_record->cost_calculation_table ? json_decode($txn_record->cost_calculation_table) : NULL;
?>
<div class="box-body">
	<table class="table no-margin table-bordered">
		<tbody id="_premium-details">
			<?php if($cost_calculation_table): ?>

				<?php foreach($cost_calculation_table as $row):?>
					<tr>
						<th class="text-left"><?php echo $row->label ?></th>
						<td class="text-right"><?php echo number_format( (float)$row->value, 2, '.', '');?></td>
					</tr>
				<?php endforeach ?>
				<tr>
				    <td class="no-padding" colspan="2">
				        <table class="table no-margin table-bordered table-condensed">
				            <tr>
				                <td width="80%" class="text-right"><strong>बीमा शुल्क</strong></td>
				                <td class="text-right"><strong><?php echo number_format((float)$txn_record->amt_total_premium, 2, '.', '')?></strong></td>
				            </tr>
				            <tr>
				                <td class="text-right"><strong>टिकट दस्तुर</strong></td>
				                <td class="text-right"><strong><?php echo number_format( (float)$txn_record->amt_stamp_duty, 2, '.', '')?></strong></td>
				            </tr>
				            <tr>
				                <td class="text-right"><strong>टिकटको मु. अ. क. (VAT)</strong></td>
				                <td class="text-right"><strong><?php echo number_format( (float)$txn_record->amt_vat, 2, '.', '');?></strong></td>
				            </tr>
				            <tr>
				                <td class="text-right"><strong>मु. अ. क.(VAT) सहित जम्मा दस्तुर</strong></td>
				                <td class="text-right"><strong><?php echo number_format( (float)( $txn_record->amt_stamp_duty + $txn_record->amt_total_premium + $txn_record->amt_vat ) , 2, '.', '');?></strong></td>
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