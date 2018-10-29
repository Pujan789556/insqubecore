<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Ledgers:  Data List
*/
$balance = $bf_record->dr - $bf_record->cr;
$dr_total = 0.00;
$cr_total = 0.00;
$mode = $mode ?? 'list';
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
			<td width="10%">Voucher Date</td>
			<td width="20%">Voucher No</td>
			<td>Narration</td>
			<td class="text-right">DR</td>
			<td class="text-right">CR</td>
			<td class="text-right">Balance (DR/CR)</td>
		</tr>
	</thead>
	<tbody id="search-result-voucher" class="ledger-data">
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
				<td><?php echo $mode == 'list' ? anchor( 'ac_vouchers/details/' . $single->id, $single->voucher_code, ['target' => '_blank']) : $single->voucher_code; ?></td>
				<td><?php echo $single->narration ?></td>
				<td class="text-right"><?php echo number_format($dr, 2); ?></td>
				<td class="text-right"><?php echo number_format($cr, 2); ?></td>
				<td class="text-right"><?php echo number_format($balance, 2); ?></td>
			</tr>
		<?php endforeach ?>
	</tbody>
	<tfoot class="text-bold">
		<tr>
			<td colspan="3" class="text-right">Total</td>
			<td class="text-right"><?php echo number_format($dr_total); ?></td>
			<td class="text-right"><?php echo number_format($cr_total); ?></td>
			<td class="text-right"><?php echo number_format($balance); ?></td>
		</tr>
	</tfoot>
</table>