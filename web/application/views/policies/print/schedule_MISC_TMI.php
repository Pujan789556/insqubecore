<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Schedule Print : MISCELLANEOUS - TRAVEL MEDICAL INSURANCE(TMI)
 */
$this->load->helper('ph_misc_tmi');

$object_attributes      = json_decode($record->object_attributes);
$schedule_table_title   = 'Travel Medical Insurance (Schedule)';
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
            <thead><tr><td colspan="2" align="center"><h3><?php echo $schedule_table_title?></h3></td></tr></thead>
            <tbody>
                <tr><td colspan="2"><?php echo _POLICY_schedule_title_prefix($record->status, 'en')?>: <?php echo $record->code;?></td></tr>
                <tr>
                    <td width="50%" class="no-padding">
                        <table class="table" width="100%">
                            <tr>
                                <td>
                                    <strong>Insured Information</strong><br/>
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
                            </tr>
                            <tr>
                                <td>
                                    <?php $age  = date_difference($object_attributes->dob, date('Y-m-d'), 'y'); ?>
                                    <strong>Profession:</strong> <?php echo $record->customer_profession;?><br>
                                    <strong>Date of Birth:</strong> <?php echo date('j M Y', strtotime($object_attributes->dob));?> (<?php echo $age ?> years)<br>
                                    <strong>Passport No.:</strong> <?php echo $object_attributes->passport_no; ?>
                                </td>
                            </tr>
                        </table>
                    </td>

                    <td width="50%" class="no-padding">
                        <table class="table">
                            <tr>
                                <td><strong>Portfolio</strong>: <?php echo htmlspecialchars($record->portfolio_name); ?></td>
                            </tr>

                            <tr>
                                <td><strong>Proposed By</strong>: <?php echo nl2br(htmlspecialchars($record->proposer));?></td>
                            </tr>

                            <tr>
                                <td><strong>Proposed Date</strong>: <?php echo $record->proposed_date?></td>
                            </tr>

                            <tr>
                                <td><strong>Issued Date</strong>: <?php echo $record->issued_date?></td>
                            </tr>
                            <tr>
                                <td><strong>Issued At</strong>: <?php echo $this->dx_auth->get_branch_code()?></td>
                            </tr>

                            <tr>
                                <td>
                                    <strong>Policy Start Date</strong>: <?php echo $record->start_date?><br>
                                    <strong>Policy End Date</strong>: <?php echo $record->end_date?><br>
                                    <strong>No. of Days</strong>: <?php echo date_difference($record->start_date, $record->end_date, 'd') + 1 ?> days
                                </td>
                            </tr>

                            <tr>
                                <?php
                                /**
                                 * Agent Details
                                 */
                                $agent_text = implode(' ', array_filter([$record->agent_bs_code, $record->agent_ud_code]));
                                ?>
                                <td><strong>Agent</strong>: <?php echo $agent_text;?></td>
                            </tr>

                            <tr>
                                <td>
                                    <strong class="border-b">Premium Information</strong><br><br>
                                    <?php
                                    /**
                                     * Policy Premium Card
                                     */
                                    $cost_calculation_table_view = _POLICY__partial_view__cost_calculation_table($record->portfolio_id);
                                    $this->load->view($cost_calculation_table_view, ['endorsement_record' => $endorsement_record, 'policy_record' => $record]);
                                    ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td colspan="2">
                        <strong>SCHEDULE OF BENEFITS</strong><br>
                        <?php
                        $benefits_json = _OBJ_MISC_TMI_tariff_benefits($object_attributes->plan_id);
                        $benefits = json_decode($benefits_json);
                        if($benefits):
                         ?>
                         <table class="table">
                            <thead>
                                <tr>
                                    <td><strong>Section</strong></td>
                                    <td><strong>Benefits</strong></td>
                                    <td><strong>Max. Sum Insured</strong></td>
                                    <td><strong>Excess</strong></td>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($benefits as $single): ?>
                                    <tr>
                                        <td><?php echo $single->section ?></td>
                                        <td><?php echo nl2br( htmlspecialchars($single->benefit)) ?></td>
                                        <td><?php echo $single->max_sum_insured ?></td>
                                        <td><?php echo htmlspecialchars($single->excess) ?></td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                         </table>
                        <?php endif; ?>
                    </td>
                </tr>

                <tr>
                    <td colspan="2"><?php echo nl2br(htmlspecialchars($endorsement_record->txn_details)) ?></td>
                </tr>
            </tbody>
        </table><br>
        <?php
        /**
         * Load Footer
         */
        $this->load->view('policies/print/_schedule_footer_np');
        ?>
    </body>
</html>