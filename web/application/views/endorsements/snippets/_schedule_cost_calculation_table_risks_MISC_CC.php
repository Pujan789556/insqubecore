<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Policy: Details - Policy Premium Overview Card - MISCELLANEOUS - CASH IN COUNTER
*/
$cost_calculation_table = $endorsement_record->cost_calculation_table ? json_decode($endorsement_record->cost_calculation_table) : NULL;
$cost_table     = [];
$risk_table     = [];
if($cost_calculation_table)
{
    $cost_table     = $cost_calculation_table->cost_table ?? [];
    $risk_table     = $cost_calculation_table->risk_table ?? [];
}
$total_premium          = (float)$endorsement_record->amt_basic_premium + (float)$endorsement_record->amt_pool_premium;
$grand_total            = $total_premium + $endorsement_record->amt_stamp_duty + $endorsement_record->amt_vat;
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
                                        <td class="text-right"><?php echo number_format((float)$dt[1], 3);?></td>
                                        <td class="text-right"><?php echo number_format((float)$dt[2], 2);?></td>
                                    </tr>
                                <?php endforeach ?>

                                <?php foreach($cost_table as $row):?>
                                    <tr>
                                        <th colspan="2" class="text-left"><?php echo $row->label ?></th>
                                        <td class="text-right"><?php echo number_format( (float)$row->value, 2);?></td>
                                    </tr>
                                <?php endforeach ?>
                           </tbody>
                       </table>
                   </td>
                </tr>
            <?php endif ?>
            <tr>
                <td class="no-padding">
                    <?php
                    /**
                     * Load Cost Calculation Table
                     */
                    $this->load->view('endorsements/snippets/premium/_summary_table',
                        ['lang' => 'np', 'endorsement_record' => $endorsement_record]
                    );
                    ?>
                </td>
            </tr>
        <?php else:?>
            <tr><td class="text-muted text-center">No Premium Information Found!</td></tr>
        <?php endif?>
    </tbody>
</table>