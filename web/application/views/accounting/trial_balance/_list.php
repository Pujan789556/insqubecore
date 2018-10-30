<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Trial Balance:  Data List
*/
$mode = $mode ?? 'list';
?>
<table class="table table-hover table-condensed table-bordered">
	<thead>
		<tr>
			<th class="text-center" colspan="7">
				<h3 class="no-margin"><?php echo $trial_balance_title; ?></h3>
				FROM <?php echo $ledger_dates['from'] ?> TO <?php echo $ledger_dates['to'] ?>
			</th>
		</tr>
		<tr>

			<td <?php echo ($mode == 'list') ? 'rowspan="2"':'style="border-bottom: 0px solid #ffffff !important;"'?> >Description</td>
			<td colspan="2">Opening Balance</td>
			<td colspan="2">Transaction</td>
			<td colspan="2">Closing Balance</td>
		</tr>
		<tr>
			<?php if($mode == 'print'): ?>
				<td style="border-top: 0px solid #ffffff !important;">&nbsp;</td>
			<?php endif ?>
			<td>Debit(DR)</td>
			<td>Credit(CR)</td>
			<td>Debit(DR)</td>
			<td>Credit(CR)</td>
			<td>Debit(DR)</td>
			<td>Credit(CR)</td>
		</tr>
	</thead>
	<tbody id="search-result-voucher" class="ledger-data">

		<?php

		// Grand Total - Opening Balance
		$grand_total_ob_dr = 0.00;
		$grand_total_ob_cr = 0.00;

		// Grand Total - Transaction Balance
		$grand_total_txn_dr = 0.00;
		$grand_total_txn_cr = 0.00;

		// Grand Total - Closing Balance
		$grand_total_cb_dr = 0.00;
		$grand_total_cb_cr = 0.00;

		foreach($records as $single):

			// OB
			$ob_dr = $single->ob_dr ?? 0.00;
			$ob_cr = $single->ob_cr ?? 0.00;

			// Grand total dr
			$grand_total_ob_dr += $ob_dr;
			$grand_total_ob_cr += $ob_cr;

			// TXN
			$dr_txn_total = $single->dr_txn_total ?? 0.00;
			$cr_txn_total = $single->cr_txn_total ?? 0.00;

			// Grand total cr
			$grand_total_txn_dr += $dr_txn_total;
			$grand_total_txn_cr += $cr_txn_total;

			// Closing Balance
			$total_per_dr 	= $ob_dr + $dr_txn_total;
			$total_per_cr 	= $ob_cr + $cr_txn_total;
			$cb_balance 	= $total_per_dr - $total_per_cr;
			if($cb_balance > 0.00)
			{
				$cb_dr = $cb_balance;
				$cb_cr = 0.00;
			}
			else
			{
				$cb_dr = 0.00;
				$cb_cr = abs($cb_balance);
			}

			// Grand total CB
			$grand_total_cb_dr += $cb_dr;
			$grand_total_cb_cr += $cb_cr;


			/**
			 * Escape if All Value Zeros
			 */
			if(
				// Opening Balance
				$ob_dr == 0.00 && $ob_cr == 0.00

			 	&&

			 	// Transaction Balance
			 	$dr_txn_total == 0.00 && $cr_txn_total == 0.00
			)
			{
				continue;
			}

			?>
			<tr>
				<td><?php echo $single->account_id, ' - ', $single->account_name  ?></td>

				<td class="text-right"><?php echo number_format($ob_dr, 2); ?></td>
				<td class="text-right"><?php echo number_format($ob_cr, 2); ?></td>

				<td class="text-right"><?php echo number_format($dr_txn_total, 2); ?></td>
				<td class="text-right"><?php echo number_format($cr_txn_total, 2); ?></td>

				<td class="text-right"><?php echo number_format($cb_dr, 2); ?></td>
				<td class="text-right"><?php echo number_format($cb_cr, 2); ?></td>
			</tr>
		<?php endforeach ?>
	</tbody>
	<tfoot class="text-bold">
		<tr>
			<td>Grand Total</td>
			<td class="text-right"><?php echo number_format($grand_total_ob_dr, 2); ?></td>
			<td class="text-right"><?php echo number_format($grand_total_ob_cr, 2); ?></td>

			<td class="text-right"><?php echo number_format($grand_total_txn_dr, 2); ?></td>
			<td class="text-right"><?php echo number_format($grand_total_txn_cr, 2); ?></td>

			<td class="text-right"><?php echo number_format($grand_total_cb_dr, 2); ?></td>
			<td class="text-right"><?php echo number_format($grand_total_cb_cr, 2); ?></td>
		</tr>
	</tfoot>
</table>