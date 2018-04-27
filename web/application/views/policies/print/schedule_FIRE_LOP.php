<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Schedule Print : FIRE - LOSS OF PROFIT
 */
$this->load->helper('ph_fire_lop');

$object_attributes      = json_decode($record->object_attributes);
$schedule_table_title   = 'LOSS OF PROFIT(FIRE) SCHEDULE';

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

    /**
     * Header & Footer
     */
    $creator_text = ( $record->created_by_profile_name ?? $record->created_by_username ) . ' - ' . $record->created_by_code;

    $verifier_text = $record->verified_by_code
                        ? ( $record->verified_by_profile_name ?? $record->verified_by_username ) . ' - ' . $record->verified_by_code
                        : '';

    $branch_contact_prefix = $this->settings->orgn_name_en . ', ' . $record->branch_name;

    $header_footer = '<htmlpagefooter name="myfooter">
                        <table class="table table-footer no-border">
                            <tr>
                                <td align="left"> Created By: ' . $creator_text . ' </td>
                                <td align="right"> Verified By: ' . $verifier_text . ' </td>
                            </tr>
                            <tr>
                                <td colspan="2" class="border-t">'. get_contact_widget_two_lines($record->branch_contact, $branch_contact_prefix) .'</td>
                            </tr>
                        </table>
                    </htmlpagefooter>
                    <sethtmlpagefooter name="myfooter" value="on" />';
    ?>
    </head>
    <body>
        <!--mpdf
            <?php echo $header_footer?>
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
                    $agent_text = implode(', ', array_filter([$record->agent_name, $record->agent_ud_code]));
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
                            echo '<strong>INS.: ' . $this->security->xss_clean($record->creditor_name) . ', ' . $this->security->xss_clean($record->creditor_branch_name) . '</strong><br/>';
                            echo '<br/>' . get_contact_widget($record->creditor_branch_contact, true, true) . '<br/>';

                            // Care of
                            echo '<strong>A/C.: ' . $this->security->xss_clean($record->customer_name) . '<br/></strong>';
                            echo '<br/>' . get_contact_widget($record->customer_contact, true, true);

                            echo  $record->care_of ? '<br/>C/O.: ' . $this->security->xss_clean($record->care_of) : '';
                        }
                        else
                        {
                            echo $this->security->xss_clean($record->customer_name) . '<br/>';
                            echo '<br/>' . get_contact_widget($record->customer_contact, true, true);
                        }
                        ?>
                    </td>
                    <td>
                        <strong>Sum Insured/Annual Gross Profit(Rs.):</strong><br>
                        <?php echo number_format($record->object_amt_sum_insured, 2) ?><br><br>

                        <strong>Location of Risk:</strong><br>
                        <?php echo nl2br(htmlspecialchars($object_attributes->risk_locaiton)) ?><br><br>

                        <strong>Maximum Indemnity Period:</strong><br>
                        <?php echo nl2br(htmlspecialchars($object_attributes->max_indemnity_period)) ?><br><br>

                        <strong>Policy Issued Place &nbsp; Date:</strong><br>
                        <?php echo $this->dx_auth->get_branch_code()?>, <?php echo $record->issued_date?>
                    </td>
                </tr>

                <tr>
                    <td>
                        <strong>Period of Insurance:</strong><br>
                        From: : <?php echo $record->start_date ?><br>
                        To: : <?php echo $record->end_date ?>
                    </td>
                    <td>

                        <?php $cost_calculation_table = json_decode($endorsement_record->cost_calculation_table ?? NULL); ?>
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

                        <table class="table table-condensed no-border">
                            <tr>
                                <td align="right"><strong>Premium</strong></td>
                                <td class="text-right"><?php echo number_format($total_premium, 2)?></td>
                            </tr>
                            <tr>
                                <td align="right"><strong>Stamp Duty</strong></td>
                                <td class="text-right"><?php echo number_format((float)$endorsement_record->amt_stamp_duty, 2)?></td>
                            </tr>
                            <tr>
                                <td align="right"><strong>VAT</strong></td>
                                <td class="text-right"><?php echo number_format((float)$endorsement_record->amt_vat, 2)?></td>
                            </tr>
                            <tr><td colspan="2"><hr/></td></tr>
                            <tr>
                                <td class="border-t" align="right"><strong>TOTAL (NRs.)</strong></td>
                                <td class="text-right border-t"><strong><?php echo number_format($grand_total, 2);?></strong></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo nl2br(htmlspecialchars($endorsement_record->txn_details)); ?></td>
                </tr>
            </tbody>
        </table>
        <table class="table no-border" width="100%">
            <tr>
                <td width="50%">Office Seal:</td>
                <td>
                    Signed for and on behalf of the <br><strong><?php echo $this->settings->orgn_name_en?></strong>
                    <br><br><br>
                    Authorized Signature
                    <br>
                    Name:<br>
                    Designation:
                </td>
            </tr>
        </table>
    </body>
</html>