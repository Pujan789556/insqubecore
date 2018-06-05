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
                        <strong>Name and address of Insured</strong><br/>
                        <?php
                        echo $this->security->xss_clean($record->customer_name) ,
                                '<br/>' , get_contact_widget($record->customer_contact, true, true), '<br/>';

                        /**
                         * If Policy Object is Financed or on Loan, The financial Institute will be "Insured Party"
                         * and the customer will be "Account Party"
                         */
                        if($record->flag_on_credit === 'Y')
                        {
                            $financer_info = [
                                '<strong>Name and address of Financer(s)</strong>',

                                $this->security->xss_clean($record->creditor_name) . ', ' . $this->security->xss_clean($record->creditor_branch_name),

                                get_contact_widget($record->creditor_branch_contact, true, true)

                            ];

                            if( $record->other_creditors )
                            {
                                $financer_info = array_merge($financer_info, [
                                    '<strong>Other Financer(s)</strong>',
                                    nl2br($this->security->xss_clean($record->other_creditors))
                                ]);
                            }

                            echo implode('<br/>', $financer_info), '<br/>';
                        }
                        echo  $record->care_of ? '<br/><strong>Care of</strong><br>' . nl2br($this->security->xss_clean($record->care_of)) : '';
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
                                    <td>SN</td>
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
                                                <?php echo $key == 'sum_insured' ? number_format($value, 2) : $value;?>
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
                    <td colspan="2"><?php echo nl2br(htmlspecialchars($endorsement_record->txn_details)); ?></td>
                </tr>
                <tr>
                    <td colspan="2">
                        In witness whereof the undersigned acting on behalf and under the Authority of the Company that hereunder set his hand at <span style="text-decoration: underline; font-weight: bold"><?php echo $record->branch_name_en; ?></span> on this <span style="text-decoration: underline; font-weight: bold"><?php echo date('jS', strtotime($record->issued_date) ); ?></span> day of <span style="text-decoration: underline; font-weight: bold"><?php echo date('F, Y', strtotime($record->issued_date) ); ?></span>.
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