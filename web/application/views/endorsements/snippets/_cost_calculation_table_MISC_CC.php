<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Policy: Details - Policy Premium Overview Card - MISCELLANEOUS - CASH IN COUNTER
*/
$cost_calculation_table = json_decode($endorsement_record->cost_calculation_table ?? NULL);

$cost_table   = NULL;
$risk_table   = NULL;
if($cost_calculation_table)
{
    $cost_table     = $cost_calculation_table->cost_table;
    $risk_table     = $cost_calculation_table->risk_table;
}
$total_premium  = (float)$endorsement_record->amt_basic_premium + (float)$endorsement_record->amt_pool_premium;
$grand_total  = $total_premium + $endorsement_record->amt_stamp_duty + $endorsement_record->amt_vat;
?>
<div class="box-body">
    <table class="table no-margin table-bordered">
        <tbody id="_premium-details">
            <?php if($cost_calculation_table):?>
                <?php if($risk_table): ?>
                    <tr>
                       <td class="no-padding" colspan="2">
                           <table class="table no-margin table-bordered">
                               <thead>
                                   <tr>
                                       <th>Risk</th>
                                       <th class="text-right">Rate (Rs. per Thousand)</th>
                                       <th class="text-right">Premium (Rs.)</th>
                                   </tr>
                               </thead>
                               <tbody>
                                   <?php foreach($risk_table as $dt): ?>
                                        <tr>
                                            <td><?php echo $dt[0] ?></td>
                                            <td class="text-right"><?php echo $dt[1] ?></td>
                                            <td class="text-right"><?php echo $dt[2] ?></td>
                                        </tr>
                                    <?php endforeach ?>
                               </tbody>
                           </table>
                       </td>
                    </tr>
                <?php endif ?>

                <?php if($cost_table): ?>
                    <?php foreach($cost_table as $row):?>
                      <tr>
                        <th class="text-left"><?php echo $row->label ?></th>
                        <td class="text-right"><?php echo number_format( (float)$row->value, 2);?></td>
                      </tr>
                    <?php endforeach ?>
                <?php endif ?>
                <tr>
                    <td class="no-padding" colspan="2">
                        <table class="table no-margin table-bordered table-condensed">
                            <tr>
                                <td width="80%" class="text-right"><strong>जम्मा</strong></td>
                                <td class="text-right"><strong><?php echo number_format($total_premium, 2)?></strong></td>
                            </tr>
                            <tr>
                                <td class="text-right"><strong>टिकट दस्तुर</strong></td>
                                <td class="text-right"><strong><?php echo $endorsement_record->amt_stamp_duty;?></strong></td>
                            </tr>
                            <tr>
                                <td class="text-right"><strong>मु. अ. क. (VAT)</strong></td>
                                <td class="text-right"><strong><?php echo number_format( (float)$endorsement_record->amt_vat, 2);?></strong></td>
                            </tr>
                            <tr>
                                <td class="text-right"><strong>मु. अ. क.(VAT) सहित जम्मा दस्तुर</strong></td>
                                <td class="text-right"><strong><?php echo number_format( $grand_total , 2);?></strong></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            <?php else:?>
                <tr><td class="text-muted text-center">No Premium Information Found!</td></tr>
            <?php endif?>
        </tbody>
    </table>
</div>