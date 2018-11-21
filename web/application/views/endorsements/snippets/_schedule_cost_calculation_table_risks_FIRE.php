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
                                        <td class="text-right"><?php //echo number_format((float)$dt[1], 3);?></td>
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