<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Schedule Print : ENGINEERING - CONTRACTOR PLANT & MACHINARY
 */
$this->load->helper('ph_eng_cpm');

$object_attributes      = json_decode($record->object_attributes);
$schedule_table_title   = 'Contractor Plant & Machinary (Schedule)';

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
                    <td><strong><?php echo policy_nr_title($record->status, 'en')?>:</strong> <?php echo $record->code;?></td>

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
                        <table class="table table-condensed no-border">
                            <tr>
                                <td><strong>Premium</strong></td>
                                <td class="text-right"><?php echo number_format((float)$txn_record->amt_total_premium, 2, '.', '')?></td>
                            </tr>
                            <tr>
                                <td>Stamp Duty</td>
                                <td class="text-right"><?php echo number_format((float)$txn_record->amt_stamp_duty, 2, '.', '')?></td>
                            </tr>
                            <tr>
                                <td>13% VAT</td>
                                <td class="text-right"><?php echo number_format((float)$txn_record->amt_vat, 2, '.', '')?></td>
                            </tr>
                            <tr><td colspan="2"><hr/></td></tr>
                            <tr>
                                <td class="border-t"><strong>TOTAL (NRs.)</strong></td>
                                <td class="text-right border-t"><strong><?php echo number_format( (float)( $txn_record->amt_stamp_duty + $txn_record->amt_total_premium + $txn_record->amt_vat ) , 2, '.', '');?></strong></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <?php
                $form_elements = _OBJ_ENG_CPM_validation_rules($record->portfolio_id);

                /**
                 * Item List
                 */
                $section_elements   = $form_elements['item'];
                $item              = $object_attributes->item ?? NULL;
                ?>
                <tr>
                    <td colspan="2">
                        <strong>SPECIFICATION OF INSURED ITEMS</strong><br>
                        <table class="table table-condensed">
                            <thead>
                                <tr>
                                    <?php foreach($section_elements as $elem): ?>
                                        <td><?php echo $elem['label'] ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>

                            <tbody>
                                <tr>
                                    <?php foreach($section_elements as $elem):
                                        $key =  $elem['_key'];
                                        $value = $item->{$key};
                                    ?>

                                        <td <?php echo $key == 'sum_insured' ? 'class="text-right"' : '' ?>>
                                            <?php echo $value?>
                                        </td>
                                    <?php endforeach ?>
                                </tr>
                                <tr>
                                    <td class="text-bold">Total Sum Insured Amount(Rs.)</td>
                                    <td class="text-bold text-right"><?php echo number_format($record->object_amt_sum_insured, 2, '.', '') ?></td>
                                    <td>&nbsp;</td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo nl2br(htmlspecialchars($txn_record->txn_details)); ?></td>
                </tr>
                <tr>
                    <td colspan="2">
                        In witness whereof the undersigned acting on behalf and under the Authority of the Company that hereunder set his hand at <span style="text-decoration: underline; font-weight: bold"><?php echo $record->branch_name; ?></span> on this <span style="text-decoration: underline; font-weight: bold"><?php echo date('jS', strtotime($record->issued_date) ); ?></span> day of <span style="text-decoration: underline; font-weight: bold"><?php echo date('F, Y', strtotime($record->issued_date) ); ?></span>.
                    </td>
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