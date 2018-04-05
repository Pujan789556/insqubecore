<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Policy: Details - Policy Premium Overview Card - MISCELLANEOUS - CASH IN SAFE
*/
$cost_calculation_table = $txn_record->cost_calculation_table ? json_decode($txn_record->cost_calculation_table) : NULL;
$property_table = NULL;
$risk_table     = NULL;
if($cost_calculation_table)
{
    $property_table = $cost_calculation_table->property_table;
    $risk_table     = $cost_calculation_table->risk_table;
}
?>
<table class="table no-margin table-bordered">
    <tbody>
        <?php if($cost_calculation_table):?>
            <?php if($risk_table): ?>
                <tr>
                   <td class="no-padding">
                       <table class="table no-margin table-bordered">
                           <thead>
                               <tr>
                                   <td>रक्षावरण गरिएका जोेखिमहरु</td>
                                   <td>दर (रु प्रति हजारमा) </td>
                                   <td class="text-right">बीमाशुल्क (रु.)</td>
                               </tr>
                           </thead>
                           <tbody>
                               <?php foreach($risk_table as $dt): ?>
                                    <tr>
                                        <td><?php echo $dt[0] ?></td>
                                        <td class="text-right"><?php echo number_format((float)$dt[1], 2, '.', '');?></td>
                                        <td class="text-right"><?php echo number_format((float)$dt[2], 2, '.', '');?></td>
                                    </tr>
                                <?php endforeach ?>
                           </tbody>
                       </table>
                   </td>
                </tr>
            <?php endif ?>
            <tr>
                <td class="no-padding">
                    <table class="table no-margin table-bordered table-condensed">
                        <tr>
                            <td width="80%" class="text-right"><strong>जम्मा</strong></td>
                            <td class="text-right"><strong><?php echo number_format((float)$txn_record->amt_total_premium, 2, '.', '')?></strong></td>
                        </tr>
                        <tr>
                            <td class="text-right"><strong>टिकट दस्तुर</strong></td>
                            <td class="text-right"><strong><?php echo number_format((float)$txn_record->amt_stamp_duty, 2, '.', '');?></strong></td>
                        </tr>
                        <tr>
                            <td class="text-right"><strong>मु. अ. क. (VAT)</strong></td>
                            <td class="text-right"><strong><?php echo number_format( (float)$txn_record->amt_vat, 2, '.', '');?></strong></td>
                        </tr>
                        <tr>
                            <td class="text-right"><strong>मु. अ. क.(VAT) सहित जम्मा दस्तुर</strong></td>
                            <td class="text-right"><strong><?php echo number_format( (float)( $txn_record->amt_stamp_duty + $txn_record->amt_total_premium + $txn_record->amt_vat ) , 2, '.', '');?></strong></td>
                        </tr>
                    </table>
                </td>
            </tr>
        <?php else:?>
            <tr><td class="text-muted text-center">No Premium Information Found!</td></tr>
        <?php endif?>
    </tbody>
</table>