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

// echo '<pre>'; print_r($risk_table);exit;
?>
<table class="table no-margin table-bordered">
    <tbody>
        <?php if($cost_calculation_table):?>
            <tr>
                <td class="no-padding">
                    <?php if($summary_table): ?>
                        <table class="table no-margin table-bordered">
                            <thead>
                              <tr>
                                <td colspan="2"><h4>बीमा शुल्क गणना</h4></td>
                              </tr>
                            </thead>
                            <?php foreach($summary_table as $row):
                                $amount = (float)$row->value;
                                if($amount == 0) continue;
                              ?>
                                <tr>
                                    <td class="text-left"><?php echo $row->label ?></td>
                                    <td class="text-right"><?php echo number_format( $amount, 2);?></td>
                                </tr>
                            <?php endforeach ?>
                        </table>
                    <?php endif ?>
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