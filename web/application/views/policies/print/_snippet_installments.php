<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Schedule Component - Installments
 */

$installments = _POLICY_INSTALLMENT_list_by_transaction($endorsement_record->id);

$inst_count = count($installments);
if( $inst_count > 1 ):
 ?>
    <strong>DETAILS OF INSTALLMENTS</strong><br>
    <table class="table table-condensed">
        <thead>
            <tr>
                <td>S.N.</td>
                <td>Installment</td>
                <td>Percent(%)</td>
                <td>Date</td>
                <td>Premium (Rs.)</td>
                <td width="15%">Stamp Duty (Rs.)</td>
                <td>VAT (Rs.)</td>
                <td>Total (Rs.)</td>
            </tr>
        </thead>
        <tbody>
            <?php
            $i = 1;
            $installment_total = 0;
            foreach($installments as $single):
                $row_total = $single->amt_basic_premium + $single->amt_pool_premium + $single->amt_stamp_duty + $single->amt_vat;

                $installment_total += $row_total;
                ?>
                <tr>
                    <td><?php echo $i; ?></td>
                    <td><?php echo ordinal($i++); ?></td>
                    <td><?php echo $single->percent; ?></td>
                    <td><?php echo $single->installment_date; ?></td>
                    <td class="text-right"><?php echo number_format($single->amt_basic_premium + $single->amt_pool_premium, 2); ?></td>
                    <td class="text-right"><?php echo number_format($single->amt_stamp_duty, 2); ?></td>
                    <td class="text-right"><?php echo number_format($single->amt_vat, 2); ?></td>
                    <td class="text-right"><?php echo number_format($row_total, 2); ?></td>
                </tr>
            <?php endforeach ?>
            <tr>
                <td colspan="7" align="right"><strong>TOTAL</strong></td>
                <td class="text-right"><strong><?php echo number_format( (float)$installment_total , 2);?></strong></td>
            </tr>
        </tbody>
    </table>
<?php endif; ?>