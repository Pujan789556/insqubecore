<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Schedule Print : MARINE
 */

$this->load->helper('forex');
$this->load->helper('ph_marine');

$object_attributes      = json_decode($record->object_attributes);
$schedule_table_title   = 'Marine Insurance Policy (Schedule)';
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
        <table class="table small" width="100%">
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
                        <strong>Issued at:</strong> <?php echo htmlspecialchars($record->branch_name_en) ?>

                        <br/><br/><strong>Period of Insurance:</strong><br>
                        From: : <?php echo $record->start_date ?><br>
                        To: : <?php echo $record->end_date ?>
                        (<?php echo _POLICY_duration_formatted($record->start_date, $record->end_date, 'en'); ?>)
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
                                <td><?php echo $si_components->incremental_cost ?>% INCIDENTAL COST of ( INVOICE VALUE + ( <?php echo $si_components->tolerance_limit ?> % TOLERANCE LIMIT of INVOICE VALUE ) )</td>
                            </tr>
                            <tr>
                                <td>Duty:</td>
                                <td><?php echo $si_components->duty ?> %</td>
                            </tr>
                            <tr>
                                <td>Total (NPR):</td>
                                <td>
                                    <?php
                                    $forex = get_forex_rate_by_base_currency($si_components->forex_date, $si_components->currency);
                                    echo number_format((float)$record->object_amt_sum_insured, 2);
                                    if($forex)
                                    {
                                         echo " ({$forex->BaseCurrency} {$forex->BaseValue} = {$forex->TargetCurrency} {$forex->TargetSell})";
                                    }

                                    ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td>
                        <?php
                        /**
                         * Load Cost Calculation Table - Summary
                         */
                        $this->load->view('endorsements/snippets/premium/_summary_table',
                            ['lang' => 'en', 'endorsement_record' => $endorsement_record]
                        );
                        ?>
                    </td>
                </tr>

                <tr>
                    <td colspan="2">
                        <strong>Subject matter insured/Interest</strong><br/>
                        <?php echo nl2br( htmlspecialchars($object_attributes->description) ); ?>
                        <?php
                        if($object_attributes->packing)
                        {
                            echo '<br>', nl2br( htmlspecialchars($object_attributes->packing) );
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">


                        <table class="table">
                            <tr>
                                <td width="30%"><strong>Marks &amp; Numbers</strong></td>
                                <td> <?php echo htmlspecialchars($object_attributes->marks_numbers) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Voyage From</strong></td>
                                <td><?php echo htmlspecialchars($object_attributes->transit->from) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Voyage To</strong></td>
                                <td><?php echo htmlspecialchars($object_attributes->transit->to) ?></td>
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
                                    <?php
                                    echo htmlspecialchars( implode(', ', array_unique([$object_attributes->transit->lc_no, $object_attributes->transit->lc_date]) ) );
                                    ?>
                                </td>
                            </tr>

                            <tr>
                                <td><strong>B/L No./C/N No./AW/B No./R/R No. &amp; Date</strong></td>
                                <td>
                                    <?php
                                    echo htmlspecialchars( implode(', ', array_unique([$object_attributes->transit->bl_no, $object_attributes->transit->bl_date]) ) );
                                    ?>
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
                        </table>
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
                                <td class="smaller"><?php echo implode('<br/>', $firsthalf); ?></td>
                                <td class="smaller"><?php echo implode('<br/>', $secondhalf); ?></td>
                            </tr>
                        </table>

                        <?php if($object_attributes->risk->warranties): ?>
                            <strong>Warranties:</strong><br/>
                            <?php echo nl2br(htmlspecialchars($object_attributes->risk->warranties)); ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><strong>Deductible Excess</strong>: <?php echo _OBJ_MARINE_deductible_excess_dropdown(FALSE)[$object_attributes->risk->deductible_excess] ?></td>
                </tr>
                <tr>
                    <td colspan="2">
	                    <table class="no-border">
	                    	<tr>
	                    		<td width="15%"><strong>Surveyor Name</strong></td>
	                    		<td><?php echo  htmlspecialchars($object_attributes->surveyor->name)?></td>
	                    	</tr>
	                    	<tr>
	                    		<td><strong>Contact Person</strong></td>
	                    		<td><?php echo  htmlspecialchars($object_attributes->surveyor->contact_person)?></td>
	                    	</tr>
	                    	<tr>
	                    		<td><strong>Address</strong></td>
	                    		<td><?php echo  htmlspecialchars($object_attributes->surveyor->address)?></td>
	                    	</tr>
	                    </table>
	                </td>
                </tr>
                <tr>
                    <td colspan="2"><strong>Claims payable at</strong>: <?php echo htmlspecialchars( $object_attributes->claim_payable_at ) ?></td>
                </tr>
            </tbody>
        </table><br>
        <?php
        /**
         * Load Footer
         */
        $this->load->view('policies/print/_snippet_footer', ['lang' => 'en']);
        ?>

        <?php $cost_calculation_table = json_decode($endorsement_record->cost_calculation_table ?? NULL);
        if($cost_calculation_table):?>
            <pagebreak>
            <?php
            /**
             * Load Cost Calculation Table - Summary
             */
            $this->load->view('endorsements/snippets/premium/_calculation_table',
                ['lang' => 'en', 'endorsement_record' => $endorsement_record]
            );
            ?><br>
        <?php endif ?>
    </body>
</html>