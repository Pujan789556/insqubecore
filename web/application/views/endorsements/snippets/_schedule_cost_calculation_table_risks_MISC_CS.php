<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Policy: Details - Policy Premium Overview Card - MISCELLANEOUS - CASH IN SAFE
*/
$cost_calculation_table = $endorsement_record->cost_calculation_table ? json_decode($endorsement_record->cost_calculation_table) : NULL;
$risk_table     = NULL;
if($cost_calculation_table)
{
    $risk_table     = $cost_calculation_table->risk_table ?? [];
    $cost_table     = $cost_calculation_table->cost_table ?? [];
}
$total_premium          = (float)$endorsement_record->net_amt_basic_premium + (float)$endorsement_record->net_amt_pool_premium;
$grand_total            = $total_premium + $endorsement_record->net_amt_stamp_duty + $endorsement_record->net_amt_vat;

if($cost_calculation_table):?>
    <?php
    /**
     * NOTE: RISK Table is NOT Necessary
     */
    /*
    if($risk_table): ?>
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
                          <td class="text-right"><?php echo number_format((float)$dt[1], 2);?></td>
                          <td class="text-right"><?php echo number_format((float)$dt[2], 2);?></td>
                      </tr>
                  <?php endforeach ?>
             </tbody>
         </table>
         <br>
     <?php endif */?>
     <?php
      if($cost_table): ?>
         <table class="table no-margin table-bordered">
            <thead>
                <tr>
                    <td colspan="2"><strong>बीमाशुल्क गणना तालिका</strong></td>
                </tr>
            </thead>
             <tbody>
                <?php foreach($cost_table as $row):?>
                  <tr>
                    <td class="text-left"><?php echo $row->label ?></td>
                    <td class="text-right"><?php echo number_format( (float)$row->value, 2);?></td>
                  </tr>
                <?php endforeach ?>

             </tbody>
         </table>
         <br>
     <?php endif ?>
     <?php
      /**
       * Load Cost Calculation Table
       */
      $this->load->view('endorsements/snippets/premium/_summary_table',
          ['lang' => 'np', 'endorsement_record' => $endorsement_record]
      );
      ?>

<?php else:?>
    <span class="text-muted text-center">No Premium Information Found!</span>
<?php endif?>