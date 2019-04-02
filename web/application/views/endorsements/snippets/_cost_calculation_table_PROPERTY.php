<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Policy: Details - Policy Premium Overview Card - FIRE - FIRE
*/
$cost_calculation_table = json_decode($endorsement_record->cost_calculation_table ?? NULL);

$cc_table_main  = NULL;
$cc_table_other = NULL;
$summary_table = NULL;
if($cost_calculation_table)
{
    $cc_table_main  = $cost_calculation_table->cc_table_main ?? NULL;
    $cc_table_other = $cost_calculation_table->cc_table_other ?? NULL;
}

$total_premium          = _ENDORSEMENT__total_premium($endorsement_record);
$grand_total            = _ENDORSEMENT__grand_total($endorsement_record);
?>
<div class="box-body">
    <table class="table no-margin table-bordered">
        <tbody id="_premium-details">
            <?php if($cost_calculation_table):?>
                <?php if($cc_table_main): ?>
                    <tr>
                       <td class="no-padding">
                           <table class="table table-condensed no-margin table-bordered">
                               <thead>
                                    <tr>
                                        <td colspan="7"><h4>कूल बीमाशुल्क गणना तालिका </h4></td>
                                    </tr>
                                    <tr>
                                        <td>क्र. सं.</td>
                                        <td>विवरण</td>
                                        <td>दर संकेत</td>
                                        <td>जोखिम संकेत</td>
                                        <td class="text-right">बीमाङ्क रकम</td>
                                        <td class="text-right">बीमादर (प्रति हजारमा)</td>
                                        <td class="text-right">बीमाशुल्क</td>
                                    </tr>
                               </thead>
                               <tbody>
                                   <?php foreach($cc_table_main as $dt): ?>
                                        <tr>
                                            <td><?php echo $dt->sn ?></td>
                                            <td><?php echo $dt->description ?></td>
                                            <td><?php echo $dt->risk_category_code ?></td>
                                            <td><?php echo $dt->risk_code ?></td>
                                            <td class="text-right"><?php echo number_format($dt->si_per_property, 2) ?></td>
                                            <td class="text-right"><?php echo number_format($dt->applied_rate_basic, 2) ?></td>
                                            <td class="text-right"><?php echo number_format($dt->premium_per_property, 2) ?></td>
                                        </tr>
                                    <?php endforeach ?>
                               </tbody>
                               <?php if($cc_table_other): ?>
                                <tfoot>
                                    <?php foreach ($cc_table_other as $row):?>
                                        <tr>
                                            <td colspan="6"><?php echo $row->label; ?></td>
                                            <td class="text-right"><?php echo number_format( $row->value, 2); ?></td>
                                        </tr>
                                    <?php endforeach ?>
                                </tfoot>
                               <?php endif ?>
                           </table>
                       </td>
                    </tr>
                <?php endif ?>

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
</div>