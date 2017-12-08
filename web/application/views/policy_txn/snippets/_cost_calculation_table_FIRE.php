<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Policy: Details - Policy Premium Overview Card - FIRE
*/
$cost_calculation_table = $txn_record->cost_calculation_table ? json_decode($txn_record->cost_calculation_table) : NULL;

$property_table = NULL;
$risk_table     = NULL;
$summary_table = NULL;
if($cost_calculation_table)
{
    $property_table   = $cost_calculation_table->property_table;
    $risk_table       = $cost_calculation_table->risk_table;
    $summary_table    = $cost_calculation_table->summary_table;
}
?>
<div class="box-body">
    <table class="table no-margin table-bordered">
        <tbody id="_premium-details">
            <?php if($cost_calculation_table):?>


                <?php if($property_table): ?>
                    <tr>
                       <td class="no-padding">
                           <table class="table no-margin table-bordered">
                               <thead>
                                    <tr>
                                        <th colspan="3"><h4>Item/Property-wise Premium Distribution</h4></th>
                                    </tr>
                                    <tr>
                                        <th>Property</th>
                                        <th class="text-right">Sum Insured (Rs.)</th>
                                        <th class="text-right">Premium (Rs.)</th>
                                    </tr>
                               </thead>
                               <tbody>
                                   <?php foreach($property_table as $dt): ?>
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

                <?php if($risk_table): ?>
                    <tr>
                       <td class="no-padding">
                           <table class="table no-margin table-bordered">
                                <thead>
                                    <tr>
                                        <th colspan="2"><h4>Risk-wise Premium Distribution</h4></th>
                                    </tr>
                                    <tr>
                                       <th>Risk</th>
                                       <th class="text-right">Premium (Rs.)</th>
                                   </tr>
                                </thead>
                               <tbody>
                                   <?php foreach($risk_table as $dt): ?>
                                        <tr>
                                            <td><?php echo $dt[0] ?></td>
                                            <td class="text-right"><?php echo $dt[1] ?></td>
                                        </tr>
                                    <?php endforeach ?>
                               </tbody>
                           </table>
                       </td>
                    </tr>
                <?php endif ?>

                <?php if($summary_table): ?>
                  <tr>
                       <td class="no-padding margin-b-10">
                          <table class="table no-margin table-bordered">
                              <thead>
                                <tr>
                                  <th colspan="2"><h4>Summary Table</h4></th>
                                </tr>
                              </thead>
                              <?php foreach($summary_table as $row):?>
                                  <tr>
                                      <td class="text-left"><?php echo $row->label ?></td>
                                      <td class="text-right"><?php echo number_format( (float)$row->value, 2, '.', '');?></td>
                                  </tr>
                              <?php endforeach ?>
                          </table>
                       </td>
                 </tr>
                <?php endif; ?>

                <tr>
                    <td class="no-padding">
                        <table class="table no-margin table-bordered table-condensed">
                            <tr>
                                <td width="80%" class="text-right"><strong>जम्मा</strong></td>
                                <td class="text-right"><strong><?php echo number_format((float)$txn_record->amt_total_premium, 2, '.', '')?></strong></td>
                            </tr>
                            <tr>
                                <td class="text-right"><strong>टिकट दस्तुर</strong></td>
                                <td class="text-right"><strong><?php echo $txn_record->amt_stamp_duty;?></strong></td>
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
</div>