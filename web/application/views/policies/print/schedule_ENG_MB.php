<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Schedule Print : ENGINEERING - MACHINE BREAKDOWN
 */
$this->load->helper('ph_eng_mb');

$object_attributes      = json_decode($record->object_attributes);
$schedule_table_title   = 'Machine Breakdown (Schedule)';
$total_premium  = (float)$endorsement_record->amt_basic_premium + (float)$endorsement_record->amt_pool_premium;
$grand_total    = $total_premium + $endorsement_record->amt_stamp_duty + $endorsement_record->amt_vat;
?>

<!DOCTYPE html>
<html>
    <head>
    <meta charset="utf-8">
    <?php
    /**
     * Load Styles (inline)
     */
    $this->load->view('print/style/schedule');
    ?>
    </head>
    <body>
        <?php
        /**
         * Header & Footer
         */
        ?>
        <!--mpdf
            <?php echo _POLICY_schedule_header_footer($record);?>
        mpdf-->
        <?php
        /**
         * Policy Schedule
         */
        ?>
        <table class="table" width="100%">
            <thead><tr><td colspan="3" align="center"><h3><?php echo $schedule_table_title?></h3></td></tr></thead>
            <tbody>
                <tr>
                    <td><strong><?php echo _POLICY_schedule_title_prefix($record->status, 'en')?>:</strong> <?php echo $record->code;?></td>

                    <?php
                    /**
                     * Agent Details
                     */
                    $agent_text = implode(' ', array_filter([$record->agent_bs_code, $record->agent_ud_code]));
                    ?>
                    <td><strong>Agent:</strong> <?php echo $agent_text;?> </td>
                </tr>
                <tr>
                    <td>
                        <?php
                        /**
                         * Insured Party, Financer, Other Financer, Careof
                         */
                        $this->load->view('policies/print/_schedule_insured_party', ['lang' => 'en']);
                        ?>
                    </td>
                    <td>
                        <strong>Location of Risk:</strong><br>
                        <?php echo nl2br(htmlspecialchars($object_attributes->risk_locaiton)) ?>
                    </td>
                </tr>

                <tr>
                    <td>
                        <strong>Period of Insurance:</strong><br>
                        From: : <?php echo $record->start_date ?><br>
                        To: : <?php echo $record->end_date ?>
                    </td>
                    <td>
                        <?php $cost_calculation_table = json_decode($endorsement_record->cost_calculation_table ?? NULL);
                        if($cost_calculation_table):?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <td colspan="2"><strong>COST CALCULATION TABLE</strong></td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($cost_calculation_table as $row):?>
                                        <tr>
                                            <td><?php echo $row->label ?></td>
                                            <td class="text-right"><?php echo number_format( (float)$row->value, 2);?></td>
                                        </tr>
                                    <?php endforeach ?>
                                </tbody>
                            </table><br>
                        <?php endif ?>
                        <table class="table table-condensed no-border">
                            <tr>
                                <td><strong>Premium</strong></td>
                                <td class="text-right"><?php echo number_format((float)$total_premium, 2)?></td>
                            </tr>
                            <tr>
                                <td>Stamp Duty</td>
                                <td class="text-right"><?php echo number_format((float)$endorsement_record->amt_stamp_duty, 2)?></td>
                            </tr>
                            <tr>
                                <td>13% VAT</td>
                                <td class="text-right"><?php echo number_format((float)$endorsement_record->amt_vat, 2)?></td>
                            </tr>
                            <tr><td colspan="2"><hr/></td></tr>
                            <tr>
                                <td class="border-t"><strong>TOTAL (NRs.)</strong></td>
                                <td class="text-right border-t"><strong><?php echo number_format( (float)$grand_total , 2);?></strong></td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <?php
                $form_elements = _OBJ_ENG_MB_validation_rules($record->portfolio_id);

                /**
                 * Item List
                 */
                $section_elements   = $form_elements['items'];
                $items              = $object_attributes->items ?? NULL;
                $item_count         = count( $items ?? [] );
                ?>
                <tr>
                    <td colspan="2">
                        <strong>SPECIFICATION OF MACHINERY AND PLANT INSURED</strong><br>
                        <table class="table table-condensed">
                            <thead>
                                <tr>
                                    <td width="5%">SN</td>
                                    <?php foreach($section_elements as $elem): ?>
                                        <td><?php echo $elem['label'] ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>

                            <tbody>
                                <?php
                                $i = 1;
                                foreach($items as $item_record): ?>
                                    <tr>
                                        <td align="center"><?php echo $i++; ?></td>
                                        <?php foreach($section_elements as $elem):
                                            $key =  $elem['_key'];
                                            $value = $item_record->{$key};
                                        ?>

                                            <td <?php echo $key == 'sum_insured' ? 'class="text-right"' : '' ?>>
                                                <?php echo $key == 'sum_insured' ? number_format($value, 2) : htmlspecialchars($value);?>
                                            </td>
                                        <?php endforeach ?>
                                    </tr>
                                <?php endforeach ?>
                                <tr>
                                    <td colspan="2" class="text-bold">Total Sum Insured Amount(Rs.)</td>
                                    <td class="text-bold text-right"><?php echo number_format($record->object_amt_sum_insured, 2) ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="small"><?php echo nl2br(htmlspecialchars($endorsement_record->txn_details)); ?></td>
                </tr>
                <tr>
                    <td colspan="2" class="small">
                        In witness whereof the undersigned acting on behalf and under the Authority of the Company that hereunder set his hand at <span style="text-decoration: underline; font-weight: bold"><?php echo htmlspecialchars($record->branch_name_en); ?></span> on this <span style="text-decoration: underline; font-weight: bold"><?php echo date('jS', strtotime($record->issued_date) ); ?></span> day of <span style="text-decoration: underline; font-weight: bold"><?php echo date('F, Y', strtotime($record->issued_date) ); ?></span>.
                    </td>
                </tr>
            </tbody>
        </table><br/>
        <?php
        /**
         * Load Footer
         */
        $this->load->view('policies/print/_schedule_footer', ['lang' => 'en']);
        ?>
    </body>
</html>