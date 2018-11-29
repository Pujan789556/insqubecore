<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Policy: Details - Policy Premium Overview Card - FIRE
*/
$cost_calculation_table = $endorsement_record->cost_calculation_table ? json_decode($endorsement_record->cost_calculation_table) : NULL;
if($cost_calculation_table)
{
    $summary_table    = $cost_calculation_table->summary_table;
}
$total_premium          = (float)$endorsement_record->amt_basic_premium + (float)$endorsement_record->amt_pool_premium;
$grand_total            = $total_premium + $endorsement_record->amt_stamp_duty + $endorsement_record->amt_vat;
?>
<table class="table no-margin table-bordered">
    <tbody>
        <?php if($cost_calculation_table):?>
                <tr>
                   <td class="no-padding no-border">
                       <table class="table no-margin table-bordered">
                            <thead>
                              <tr>
                                <td colspan="2" align="left">बीमा शुल्क गणना तालिका</td>
                              </tr>
                            </thead>
                            <?php foreach($summary_table as $row):?>
                                <tr>
                                    <td class="text-left"><?php echo $row->label ?></td>
                                    <td class="text-right"><?php echo number_format( (float)$row->value, 2);?></td>
                                </tr>
                            <?php endforeach ?>
                        </table>
                        <?php
                        /**
                         * Load Cost Summary
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