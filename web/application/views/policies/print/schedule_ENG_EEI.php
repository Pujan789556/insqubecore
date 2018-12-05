<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Schedule Print : ENGINEERING - ELECTRONIC EQUIPMENT INSURANCE
 */
$this->load->helper('ph_eng_eei');

$object_attributes      = json_decode($record->object_attributes);
$schedule_table_title   = 'Electronic Equipment Insurance (Schedule)';
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
                    <td class="no-padding">
                        <table>
                            <tr>
                                <td>
                                    <?php
                                    /**
                                     * Insured Party, Financer, Other Financer, Careof
                                     */
                                    $this->load->view('policies/print/_snippet_insured_party', ['lang' => 'en']);
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <?php
                                    /**
                                     * Invoice, Receipt Info
                                     */
                                    $this->load->view('policies/print/_snippet_invoice_info', ['lang' => 'en']);
                                    ?>
                                </td>
                            </tr>
                        </table>
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
                        (<?php echo _POLICY_duration_formatted($record->start_date, $record->end_date, 'en'); ?>)
                    </td>
                    <td>
                        <?php
                        /**
                         * Load Cost Calculation Table
                         */
                        $this->load->view('endorsements/snippets/premium/_index',
                            ['lang' => 'en', 'endorsement_record' => $endorsement_record]
                        );
                        ?>
                    </td>
                </tr>




                <?php
                $form_elements = _OBJ_ENG_EEI_validation_rules($record->portfolio_id);

                /**
                 * Item List
                 */
                $section_elements   = $form_elements['items'];
                $items              = $object_attributes->items ?? NULL;
                $item_count         = count( $items ?? [] );
                ?>
                <tr>
                    <td colspan="2">
                        <strong>SECTION 1 - MATERIAL DAMAGE</strong><br>
                        <table class="table table-condensed">
                            <thead>
                                <tr>
                                    <td width="5%">S.N.</td>
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
                                        <td><?php echo $i++; ?></td>
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
                                    <td colspan="5" class="text-bold">Total Sum Insured Amount(Rs.)</td>
                                    <td class="text-bold text-right"><?php echo number_format($record->object_amt_sum_insured, 2) ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="small">
                        <strong>Excess/Deductible:</strong><br>
                        <?php echo nl2br(htmlspecialchars($object_attributes->deductible)); ?></td>
                </tr>
                <tr>
                    <td colspan="2" class="small"><?php echo nl2br(htmlspecialchars($endorsement_record->txn_details)); ?></td>
                </tr>
            </tbody>
        </table>

        <pagebreak>
        <h3>SECTION 2 - EXTERNAL DATA MEDIA</h3>
        <table>
            <thead>
                <tr>
                    <td><strong>Insured Item</strong></td>
                    <td><strong>Sum Insured (Rs.)</strong></td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Data media (type and quantity)</td>
                    <td>NIL</td>
                </tr>
                <tr>
                    <td>Expenses for reconstruction and re-recording of information</td>
                    <td>NIL</td>
                </tr>
                <tr>
                    <td class="text-right"><strong>Total Sum Insured</strong></td>
                    <td><strong>NIL</strong></td>
                </tr>
            </tbody>
        </table><br>
        <table>
            <thead>
                <tr>
                    <td><strong>Deductible (% of loss amount)</strong></td>
                    <td><strong>Minimum deductible</strong></td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            </tbody>
        </table>

        <h3>SECTION 3 - INCREASED COST OF WORKING</h3>
        <table>
            <thead>
                <tr>
                    <td rowspan="2"><strong>Insured Item</strong></td>
                    <td colspan="2"><strong>Limit of indemnity</strong></td>
                    <td rowspan="2"><strong>Sum Insured (Rs.)</strong></td>
                </tr>
                <tr>
                    <td><strong>per day</strong></td>
                    <td><strong>per month</strong></td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Rental of substitute electronic data processing equipment</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>NIL</td>
                </tr>
                <tr>
                    <td>Personal expense</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>NIL</td>
                </tr>
                <tr>
                    <td>Expense of transport of materials</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>NIL</td>
                </tr>
                <tr>
                    <td class="text-right" colspan="3"><strong>Total Sum Insured</strong></td>
                    <td><strong>NIL</strong></td>
                </tr>
            </tbody>
        </table><br>
        <table>
            <thead>
                <tr>
                    <td><strong>Indemnity period (months)</strong></td>
                    <td><strong>Time excess (days) </strong></td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            </tbody>
        </table>

        <p class="small">
            In witness whereof the undersigned acting on behalf and under the Authority of the Company that hereunder set his hand at <span style="text-decoration: underline; font-weight: bold"><?php echo htmlspecialchars($record->branch_name_en); ?></span> on this <span style="text-decoration: underline; font-weight: bold"><?php echo date('jS', strtotime($record->issued_date) ); ?></span> day of <span style="text-decoration: underline; font-weight: bold"><?php echo date('F, Y', strtotime($record->issued_date) ); ?></span>.
        </p><br>

        <?php
        /**
         * Load Footer
         */
        $this->load->view('policies/print/_snippet_footer', ['lang' => 'en']);
        ?>
    </body>
</html>