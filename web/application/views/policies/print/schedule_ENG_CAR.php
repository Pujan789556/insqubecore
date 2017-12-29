<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Schedule Print : ENGINEERING - CONTRACTOR ALL RISK
 */
$this->load->helper('ph_eng_car');

$object_attributes      = json_decode($record->object_attributes);
$schedule_table_title   = "Contractor's All Risks (Schedule)";

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
                        <?php
                        /**
                         * If Policy Object is Financed or on Loan, The financial Institute will be "Insured Party"
                         * and the customer will be "Account Party"
                         */
                        if($record->flag_on_credit === 'Y')
                        {
                            echo '<br/><strong>Name and address of Financer(s)</strong><br/>',
                                $this->security->xss_clean($record->creditor_name) , ', ' , $this->security->xss_clean($record->creditor_branch_name), '<br/>',
                                get_contact_widget($record->creditor_branch_contact, true, true) , '<br/>' ,
                                nl2br($this->security->xss_clean($record->other_creditors));

                            echo  $record->care_of ? '<br/>C/O.: ' . $this->security->xss_clean($record->care_of) : '';
                        }
                        ?>

                        <strong>Name and address of Principal</strong><br/>
                        <?php echo nl2br(htmlspecialchars($object_attributes->principal)) ?>

                        <br/><br/><strong>Name and address of Contractor</strong><br/>
                        <?php
                        echo $this->security->xss_clean($record->customer_name),
                            '<br/>' . get_contact_widget($record->customer_contact, true, true);
                        ?>
                    </td>
                    <td>
                        <strong>Contract Title:</strong><br>
                        <?php echo nl2br(htmlspecialchars($object_attributes->contract_title)) ?><br><br>

                        <strong>Contract No.:</strong><br>
                        <?php echo nl2br(htmlspecialchars($object_attributes->contract_no)) ?><br><br>

                        <strong>Location of Risk:</strong><br>
                        <?php echo nl2br(htmlspecialchars($object_attributes->risk_locaiton)) ?>
                    </td>
                </tr>

                <tr>
                    <td>
                        <strong>Period of Insurance:</strong><br>
                        From: <strong><?php echo $record->start_date ?></strong><br>
                        To: <strong><?php echo $record->end_date ?></strong> <em>plus <strong><?php echo $object_attributes->maintenance_period ?></strong> months maintenance period.</em>
                    </td>
                    <td>
                        <?php $cost_calculation_table = json_decode($txn_record->cost_calculation_table ?? NULL);
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
                                            <td class="text-right"><?php echo number_format( (float)$row->value, 2, '.', '');?></td>
                                        </tr>
                                    <?php endforeach ?>
                                </tbody>
                            </table><br>
                        <?php endif ?>

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
                $form_elements = _OBJ_ENG_CAR_validation_rules($record->portfolio_id);

                /**
                 * Item List
                 */
                $section_elements   = $form_elements['items'];
                $items              = $object_attributes->items ?? NULL;
                $item_count         = count( $items->sum_insured ?? [] );

                $insured_items_dropdown = _OBJ_ENG_CAR_insured_items_dropdown(true, false);
                $insured_items_dropdown_g = _OBJ_ENG_CAR_insured_items_dropdown(false, true);
                ?>
                <tr>
                    <td colspan="2">
                        <strong>SECTION I - MATERIAL DAMAGE</strong><br>
                        <table class="table table-condensed">
                            <thead>
                                <tr>
                                    <?php foreach($section_elements as $elem): ?>
                                        <td><?php echo $elem['label'] ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                unset($section_elements[0]); // remove sn field
                                $i = 0;
                                foreach($insured_items_dropdown_g as $title => $groups): ?>

                                    <tr><th colspan="3" class="text-left"><?php echo $title ?></th></tr>

                                    <?php foreach($groups as $sn=>$label ): ?>
                                        <tr>
                                            <td><?php echo $label ?></td>
                                            <?php foreach($section_elements as $elem):
                                                    $key =  $elem['_key'];
                                                    $value = $items->{$key}[$i];
                                                ?>
                                                    <td <?php echo $key == 'sum_insured' ? 'class="text-right"' : 'text-left' ?>>
                                                        <?php echo $value?>
                                                    </td>
                                                <?php endforeach ?>
                                        </tr>
                                    <?php
                                    $i++;
                                    endforeach;
                                endforeach; ?>
                                <tr>
                                    <td class="text-bold">Total Sum Insured Amount(Rs.)</td>
                                    <td class="text-bold text-right"><?php echo number_format($record->object_amt_sum_insured, 2, '.', '') ?></td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td><sup>(1)</sup>AOG - Earthquake, volcanism, tsunami, storm, cyclone, flood, inundation, landslide</td>
                                    <td class="text-right"><?php echo number_format($record->object_amt_sum_insured, 2, '.', '') ?></td>
                                    <td><?php echo $object_attributes->risk->deductibles; ?></td>
                                </tr>
                            </tbody>
                        </table>
                        <p><sup>(1)</sup> <i style="font-size: 8pt">Limit of indemnity in respect of each and every loss or damage and/or series of losses or damages arising out of any one envent.</i></p>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <?php
                        $section_elements   = $form_elements['third_party'];
                        $items              = $object_attributes->third_party ?? NULL;
                        $item_count         = count( $items->limit ?? [] );
                        ?>
                        <strong>SECTION II - THIRD PARTY LIABILITY</strong><br>
                        <table class="table table-condensed">
                            <thead>
                                <tr>
                                    <?php foreach($section_elements as $elem): ?>
                                        <td>
                                            <?php if($elem['_key'] == 'limit'): ?>
                                                <sup>(2)</sup>
                                            <?php endif; ?>
                                            <?php echo $elem['label'] ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>

                            <tbody>
                                <?php
                                $total_tp_liability =  _OBJ_ENG_CAR_compute_tpl_amount($object_attributes->third_party->limit ?? []);
                                for ($i=0; $i < $item_count; $i++): ?>
                                    <tr>
                                        <?php
                                        foreach($section_elements as $elem):
                                            $key =  $elem['_key'];
                                            $value = $items->{$key}[$i];

                                            // If we have dropdown, load label from this
                                            $dd_data = $elem['_data'] ?? NULL;
                                            if( $dd_data )
                                            {
                                                $value = $dd_data[$value] ?? $value;
                                            }

                                            // Comput total
                                            if( $key == 'limit' )
                                            {
                                                $value  = (float) filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

                                                // format this to echo
                                                $value = number_format($value, 2, '.', '');
                                            }
                                        ?>

                                            <td <?php echo $key == 'limit' ? 'class="text-right"' : '' ?>>
                                                <?php echo $value?>
                                            </td>
                                        <?php endforeach ?>
                                    </tr>
                                <?php endfor ?>
                                <tr>
                                    <td class="text-bold">Total Third Party Liability(Rs.)</td>
                                    <td class="text-bold text-right"><?php echo number_format($total_tp_liability, 2, '.', '') ?></td>
                                    <td>&nbsp;</td>
                                </tr>
                            </tbody>
                        </table>
                        <p><sup>(2)</sup> <i style="font-size: 8pt">Limit of indemnity in respect of any one accident or series of accidents arising out of one event.</i></p>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars($object_attributes->others->limit_per_event); ?></td>
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