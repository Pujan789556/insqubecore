<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Policy: Details - Policy Premium Overview Card - FIRE
*/
$cost_calculation_table = $endorsement_record->cost_calculation_table ? json_decode($endorsement_record->cost_calculation_table) : NULL;

$property_table = NULL;
$risk_table     = NULL;
if($cost_calculation_table)
{
    $property_table = $cost_calculation_table->property_table;
    $risk_table     = $cost_calculation_table->risk_table;
}

$total_premium          = (float)$endorsement_record->amt_basic_premium + (float)$endorsement_record->amt_pool_premium;
$grand_total            = $total_premium + $endorsement_record->amt_stamp_duty + $endorsement_record->amt_vat;
?>
<table class="table no-margin table-bordered">
    <tbody>
        <?php if($cost_calculation_table):?>
            <?php if($property_table): ?>
                <tr>
                   <td class="no-padding">
                       <table class="table">
                           <thead>
                               <tr>
                                   <td>सम्पत्ति</td>
                                   <td class="text-right">बीमाशुल्क (रु.)</td>
                               </tr>
                           </thead>
                           <tbody>
                               <?php foreach($property_table as $dt): ?>
                                    <tr>
                                        <td><?php echo $dt[0] ?></td>
                                        <td class="text-right"><?php echo number_format((float)$dt[2], 2);?></td>
                                    </tr>
                                <?php endforeach ?>
                                <tr>
                                  <td class="text-right"><strong>कुल</strong></td>
                                  <td class="text-right"><strong><?php echo number_format((float)$total_premium, 2)?></strong></td>
                                </tr>
                           </tbody>
                       </table>
                   </td>
                </tr>
            <?php endif ?>
        <?php else:?>
            <tr><td class="text-muted text-center">No Premium Information Found!</td></tr>
        <?php endif?>
    </tbody>
</table>