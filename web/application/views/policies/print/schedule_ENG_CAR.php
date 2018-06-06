<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Schedule Print : ENGINEERING - CONTRACTOR ALL RISK
 */
$this->load->helper('ph_eng_car');

$object_attributes      = json_decode($record->object_attributes);
$schedule_table_title   = "Contractor's All Risks (Schedule)";
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
                        $this->load->view('policies/print/_schedule_insured_party_car', ['object_attributes' => $object_attributes, 'record' => $record]);
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

                <tr>
                    <td colspan="2">
                        <?php
                        /**
                         * Policy Installments
                         */
                        $this->load->view('policies/print/_schedule_installments');
                        ?>
                    </td>
                </tr>



                <?php
                $form_elements = _OBJ_ENG_CAR_validation_rules($record->portfolio_id);

                /**
                 * Item List
                 */
                $section_elements   = $form_elements['items'];
                $items              = $object_attributes->items ?? NULL;

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
                                                    $value = $items[$i]->{$key} ?? '';
                                                    $value = $key == 'sum_insured' ? number_format($value, 2) : $value;
                                                ?>
                                                    <td <?php echo $key == 'sum_insured' ? 'class="text-right"' : 'text-left' ?>>
                                                        <?php echo htmlspecialchars($value)?>
                                                    </td>
                                                <?php endforeach ?>
                                        </tr>
                                    <?php
                                    $i++;
                                    endforeach;
                                endforeach; ?>
                                <tr>
                                    <td class="text-bold">Total Sum Insured Amount(Rs.)</td>
                                    <td class="text-bold text-right"><?php echo number_format($record->object_amt_sum_insured, 2) ?></td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td><sup>(1)</sup>AOG - Earthquake, volcanism, tsunami, storm, cyclone, flood, inundation, landslide</td>
                                    <td class="text-right"><?php echo number_format($record->object_amt_sum_insured, 2) ?></td>
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
                                                $value = number_format($value, 2);
                                            }
                                        ?>

                                            <td <?php echo $key == 'limit' ? 'class="text-right"' : '' ?>>
                                                <?php echo htmlspecialchars($value)?>
                                            </td>
                                        <?php endforeach ?>
                                    </tr>
                                <?php endfor ?>
                                <tr>
                                    <td class="text-bold">Total Third Party Liability(Rs.)</td>
                                    <td class="text-bold text-right"><?php echo number_format($total_tp_liability, 2) ?></td>
                                    <td>&nbsp;</td>
                                </tr>
                            </tbody>
                        </table>
                        <p><sup>(2)</sup> <i style="font-size: 8pt">Limit of indemnity in respect of any one accident or series of accidents arising out of one event.</i></p>
                    </td>
                </tr>
                <?php
                $show_limit_per_event = $object_attributes->others->show_limit_per_event ?? NULL;
                if($show_limit_per_event):
                ?>
                    <tr>
                        <td colspan="2"><strong>Limit per event:</strong> <?php echo htmlspecialchars($object_attributes->others->limit_per_event); ?></td>
                    </tr>
                <?php endif ?>
                <tr>
                    <td colspan="2"><?php echo nl2br(htmlspecialchars($endorsement_record->txn_details)); ?></td>
                </tr>
                <tr>
                    <td colspan="2">
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