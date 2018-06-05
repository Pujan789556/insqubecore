<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Schedule Print : MARINE
 */

$this->load->helper('forex');
$this->load->helper('ph_marine');

$object_attributes      = json_decode($record->object_attributes);
$schedule_table_title   = 'Marine Insurance Policy (Schedule)';

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
                    <td><strong>Date:</strong> <?php echo $record->start_date?></td>
                    <td><strong>Date of Questionnaire:</strong> <?php echo $object_attributes->date_qn ?></td>
                </tr>
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
                        <strong>Name and address of Insured</strong><br/>
                        <?php
                        /**
                         * If Policy Object is Financed or on Loan, The financial Institute will be "Insured Party"
                         * and the customer will be "Account Party"
                         */
                        if($record->flag_on_credit === 'Y')
                        {
                            echo '<strong>INS.: ' . htmlspecialchars($record->creditor_name) . ', ' . htmlspecialchars($record->creditor_branch_name) . '</strong><br/>';
                            echo '<br/>' . get_contact_widget($record->creditor_branch_contact, true, true) . '<br/>';

                            // Care of
                            echo '<strong>A/C.: ' . htmlspecialchars($record->customer_name) . '<br/></strong>';
                            echo '<br/>' . get_contact_widget($record->customer_contact, true, true);

                            echo  $record->care_of ? '<br/>C/O.: ' . htmlspecialchars($record->care_of) : '';
                        }
                        else
                        {
                            echo htmlspecialchars($record->customer_name) . '<br/>';
                            echo '<br/>' . get_contact_widget($record->customer_contact, true, true);
                        }
                        ?>
                    </td>
                    <td>
                        <strong>Bill No.:</strong> <br/>
                        <strong>Receipt No.:</strong><br>
                        <strong>Issued at:</strong> <?php echo htmlspecialchars($record->branch_name_en) ?>
                    </td>
                </tr>

                <tr>
                    <td>
                        <?php
                        $si_components = $object_attributes->sum_insured;
                        ?>
                        <table class="table table-condensed no-border">
                            <tr><td colspan="2"><strong>Sum Insured</strong></td></tr>
                            <tr>
                                <td>Invoice Value:</td>
                                <td><?php echo $si_components->currency . ' ' . $si_components->invoice_value ?></td>
                            </tr>
                            <tr>
                                <td>Incidental Cost:</td>
                                <td><?php echo $si_components->incremental_cost ?>% INCREMENTAL COST of ( INVOICE VALUE + ( <?php echo $si_components->tolerance_limit ?> % TOLERANCE LIMIT of INVOICE VALUE ) )</td>
                            </tr>
                            <tr>
                                <td>Duty:</td>
                                <td><?php echo $si_components->duty ?> %</td>
                            </tr>
                            <tr>
                                <td>Total (NPR):</td>
                                <td>
                                    <?php
                                    $forex = get_forex_rate_by_base_currency($record->start_date, $si_components->currency);
                                    echo number_format((float)$record->object_amt_sum_insured, 2),
                                         " ({$forex->BaseCurrency} {$forex->BaseValue} = {$forex->TargetCurrency} {$forex->TargetSell})";

                                    ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td>
                        <table class="table table-condensed no-border">
                            <tr>
                                <td><strong>Premium</strong></td>
                                <td class="text-right"><?php echo number_format($total_premium, 2)?></td>
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
                                <td class="text-right border-t"><strong><?php echo number_format($grand_total , 2);?></strong></td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td colspan="2">
                        <strong>Subject matter insured/Interest</strong><br/>
                        <?php echo nl2br( htmlspecialchars($object_attributes->description) ); ?>
                        <?php echo nl2br( htmlspecialchars($object_attributes->packing) ); ?>
                    </td>
                </tr>

                <tr>
                    <td><strong>Marks &amp; Numbers</strong></td>
                    <td> <?php echo htmlspecialchars($object_attributes->marks_numbers) ?></td>
                </tr>

                <tr>
                    <td>
                        <strong>Voyage From</strong>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($object_attributes->transit->from) ?><br/>
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Voyage To</strong>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($object_attributes->transit->to) ?>
                    </td>
                </tr>

                <tr>
                    <td><strong>Invoice No. &amp; Date</strong></td>
                    <td>
                        <?php echo htmlspecialchars($object_attributes->transit->invoice_no), ', ', $object_attributes->transit->invoice_date ?>
                    </td>
                </tr>

                <tr>
                    <td><strong>LC No. &amp; Date</strong></td>
                    <td>
                        <?php echo htmlspecialchars($object_attributes->transit->lc_no), ', ', $object_attributes->transit->lc_date ?>
                    </td>
                </tr>

                <tr>
                    <td><strong>B/L No./C/N No./AW/B No./R/R No. &amp; Date</strong></td>
                    <td>
                        <?php echo htmlspecialchars($object_attributes->transit->bl_no), ', ', $object_attributes->transit->bl_date ?>
                    </td>
                </tr>
                <tr>
                    <td><strong>Vessel and / or Conveyance</strong></td>
                    <td>
                        <?php echo _OBJ_MARINE_mode_of_transit_dropdown()[$object_attributes->transit->mode]; ?>
                    </td>
                </tr>
                <tr>
                    <td><strong>Estimated Date of Departure</strong></td>
                    <td>
                        <?php echo $object_attributes->date_dept ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <small>Terms of Insurance: Subject to the following clauses listed and attached hereto and printed warranties below;</small><br/>
                        <strong>Clauses:</strong><br/>
                        <?php
                        $clauses_list = [];
                        $i = 1;
                        foreach($object_attributes->risk->clauses as $cls )
                        {
                            $clauses_list[] = $i . '. ' . _OBJ_MARINE_clauses_list(FALSE)[$cls];
                            $i++;
                        }
                        // echo implode('<br/>', $clauses_list);
                        $clause_count   = count($clauses_list);
                        $firsthalf      = array_slice($clauses_list, 0, $clause_count / 2);
                        $secondhalf     = array_slice($clauses_list, $clause_count / 2);
                        ?>
                        <table class="table no-border">
                            <tr>
                                <td><?php echo implode('<br/>', $firsthalf); ?></td>
                                <td><?php echo implode('<br/>', $secondhalf); ?></td>
                            </tr>
                        </table>
                        <br/>
                        <strong>Warranties:</strong><br/>
                        <?php echo htmlspecialchars($object_attributes->risk->warranties); ?>
                    </td>
                </tr>
                <tr>
                    <td><strong>Deductible Excess</strong></td>
                    <td>
                        <?php echo _OBJ_MARINE_deductible_excess_dropdown(FALSE)[$object_attributes->risk->deductible_excess] ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Surveyor Name</strong> <br>
                        <strong>Contact Person</strong> <br>
                        <strong>Address</strong>
                    </td>
                    <td>
                        <?php
                        echo htmlspecialchars(
                            $object_attributes->surveyor->name . '<br>' .
                            $object_attributes->surveyor->contact_person . '<br>' .
                            $object_attributes->surveyor->address
                         );
                        ?>
                    </td>
                </tr>
                <tr>
                    <td><strong>Claims payable at</strong></td>
                    <td>
                        <?php echo htmlspecialchars( $object_attributes->claim_payable_at ) ?>
                    </td>
                </tr>
            </tbody>
        </table><br>
        <?php
        /**
         * Load Footer
         */
        $this->load->view('policies/print/_schedule_footer_en');
        ?>

        <?php $cost_calculation_table = json_decode($endorsement_record->cost_calculation_table ?? NULL);
        if($cost_calculation_table):?>
            <pagebreak>
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
    </body>
</html>