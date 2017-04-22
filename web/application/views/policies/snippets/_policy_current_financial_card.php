<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Policy: Details - Policy Current Financial Card
 */
?>
<div class="box box-bordered box-solid">
    <div class="box-header with-border">
        <h3 class="box-title">Current Financial Info</h3>
    </div>

    <div class="box-body">
        <table class="table table-condensed table-hover no-border">
            <tbody>
                <tr>
                    <th width="50%">Sum Insured Amount</th>
                    <td class="text-right"><?php echo $record->cur_amt_sum_insured ? number_format($record->cur_amt_sum_insured, 2, '.', '') : '-'?></td>
                </tr>
                <tr>
                    <th width="50%">Total Premium Amount</th>
                    <td class="text-right"><?php echo $record->cur_amt_total_premium ? number_format($record->cur_amt_total_premium, 2, '.', '') : '-'?></td>
                </tr>
                <tr>
                    <th width="50%">Pool Premium Amount</th>
                    <td class="text-right"><?php echo $record->cur_amt_pool_premium ? number_format($record->cur_amt_pool_premium, 2, '.', '') : '-'?></td>
                </tr>
                <tr>
                    <th width="50%">Total Commissionable Amount</th>
                    <td class="text-right"><?php echo $record->cur_amt_commissionable ? number_format($record->cur_amt_commissionable, 2, '.', '') : '-'?></td>
                </tr>
                <tr>
                    <th width="50%">Agent Commission Amount</th>
                    <td class="text-right"><?php echo $record->cur_amt_agent_commission ? number_format($record->cur_amt_agent_commission, 2, '.', '') : '-'?></td>
                </tr>
                <tr>
                    <th width="50%">Stamp Duty Amount</th>
                    <td class="text-right"><?php echo $record->cur_amt_stamp_duty ? number_format($record->cur_amt_stamp_duty, 2, '.', '') : '-'?></td>
                </tr>
                <tr>
                    <th width="50%">VAT Amount</th>
                    <td class="text-right"><?php echo $record->cur_amt_vat ? number_format($record->cur_amt_vat, 2, '.', '') : '-'?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>