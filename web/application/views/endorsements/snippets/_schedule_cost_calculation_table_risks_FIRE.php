<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Policy: Details - Policy Premium Overview Card - FIRE
*/
$cost_calculation_table = $endorsement_record->cost_calculation_table ? json_decode($endorsement_record->cost_calculation_table) : NULL;
$risk_table     = NULL;
if($cost_calculation_table)
{
    $risk_table       = $cost_calculation_table->risk_table;
    $summary_table    = $cost_calculation_table->summary_table;
}
$total_premium          = (float)$endorsement_record->amt_basic_premium + (float)$endorsement_record->amt_pool_premium;
$grand_total            = $total_premium + $endorsement_record->amt_stamp_duty + $endorsement_record->amt_vat;

// echo '<pre>'; print_r($risk_table);exit;
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
                                   <td class="text-right">दर (रु प्रति हजार)</td>
                                   <td class="text-right">बीमाशुल्क (रु.)</td>
                               </tr>
                           </thead>
                           <tbody>
                               <?php foreach($risk_table as $dt): ?>
                                    <tr>
                                        <td><?php echo $dt[0] ?></td>
                                        <td class="text-right"><?php echo number_format((float)$dt[1], 2);?></td>
                                        <td class="text-right"><?php echo number_format((float)$dt[2], 2);?></td>
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
                              <th colspan="2"><h4>बीमा शुल्क गणना</h4></th>
                            </tr>
                          </thead>
                          <?php foreach($summary_table as $row):?>
                              <tr>
                                  <td class="text-left"><?php echo $row->label ?></td>
                                  <td class="text-right"><?php echo number_format( (float)$row->value, 2);?></td>
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
                            <td class="text-right"><strong><?php echo number_format($total_premium, 2)?></strong></td>
                        </tr>
                        <tr>
                            <td class="text-right"><strong>टिकट दस्तुर</strong></td>
                            <td class="text-right"><strong><?php echo number_format($endorsement_record->amt_stamp_duty, 2);?></strong></td>
                        </tr>
                        <tr>
                            <td class="text-right"><strong>मु. अ. क. (VAT)</strong></td>
                            <td class="text-right"><strong><?php echo number_format($endorsement_record->amt_vat, 2);?></strong></td>
                        </tr>
                        <tr>
                            <td class="text-right"><strong>मु. अ. क.(VAT) सहित जम्मा दस्तुर</strong></td>
                            <td class="text-right"><strong><?php echo number_format( $grand_total, 2);?></strong></td>
                        </tr>
                    </table>
                </td>
            </tr>
        <?php else:?>
            <tr><td class="text-muted text-center">No Premium Information Found!</td></tr>
        <?php endif?>
    </tbody>
</table>