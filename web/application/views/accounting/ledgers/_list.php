<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Ledgers:  Data List
*/
$balance = $bf_record->dr - $bf_record->cr;
$dr_total = 0.00;
$cr_total = 0.00;
?>
<table class="table table-hover table-condensed table-bordered">
	<thead>

		<tr>
			<th class="text-center" colspan="6">
				Ledger
				<h3 class="no-margin"><?php echo implode( '', ['[', $record->id, '] ', $record->name, $party_name ? ' - ' . $party_name : '']); ?></h3>
				FROM <?php echo $ledger_dates['from'] ?> TO <?php echo $ledger_dates['to'] ?>
			</th>
		</tr>
		<tr>
			<th width="10%">Voucher Date</th>
			<th width="20%">Voucher No</th>
			<th>Narration</th>
			<th class="text-right">DR</th>
			<th class="text-right">CR</th>
			<th class="text-right">Balance (DR/CR)</th>
		</tr>
	</thead>
	<tbody id="search-result-voucher">
		<tr>
			<td></td>
			<td></td>
			<td>B/F</td>
			<td></td>
			<td></td>
			<td class="text-right"><?php echo number_format($balance, 2); ?></td>
		</tr>
		<?php foreach($records as $single):
			$dr = 0.00;
			$cr = 0.00;
			if( $single->flag_type == IQB_AC_FLAG_DEBIT )
			{
				$dr 		= $single->amount;
				$balance 	= $balance + $dr;

				$dr_total += $dr;
			}
			else
			{
				$cr 		= $single->amount;
				$balance 	= $balance - $cr;

				$cr_total += $cr;
			}
			?>
			<tr>
				<td><?php echo $single->voucher_date ?></td>
				<td><?php echo anchor( 'ac_vouchers/details/' . $single->id, $single->voucher_code, ['target' => '_blank']) ?></td>
				<td><?php echo $single->narration ?></td>
				<td class="text-right"><?php echo number_format($dr, 2); ?></td>
				<td class="text-right"><?php echo number_format($cr, 2); ?></td>
				<td class="text-right"><?php echo number_format($balance, 2); ?></td>
			</tr>
		<?php endforeach ?>
	</tbody>
	<tfoot>
		<tr>
			<th colspan="3" class="text-right">Total</th>
			<th class="text-right"><?php echo number_format($dr_total); ?></th>
			<th class="text-right"><?php echo number_format($cr_total); ?></th>
			<th class="text-right"><?php echo number_format($balance); ?></th>
		</tr>
	</tfoot>
</table>