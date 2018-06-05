<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Schedule Print : ENGINEERING - BOILER EXPLOSION
 */
$this->load->helper('ph_eng_bl');

$object_attributes      = json_decode($record->object_attributes);
$schedule_table_title   = 'Boiler Explosion (Schedule)';
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
                    <td>
                        <?php
                        /**
                         * Agent Details
                         */
                        $agent_text = implode(' ', array_filter([$record->agent_bs_code, $record->agent_ud_code]));
                        ?>
                        <strong>Agent:</strong> <?php echo $agent_text;?>
                    </td>
                </tr>

                <tr>
                    <td>
                        <strong>Name and address of Insured</strong><br/>
                        <?php
                        echo htmlspecialchars($record->customer_name) ,
                                '<br/>' , get_contact_widget($record->customer_contact, true, true), '<br/>';

                        /**
                         * If Policy Object is Financed or on Loan, The financial Institute will be "Insured Party"
                         * and the customer will be "Account Party"
                         */
                        if($record->flag_on_credit === 'Y')
                        {
                            $financer_info = [
                                '<strong>Name and address of Financer(s)</strong>',

                                htmlspecialchars($record->creditor_name) . ', ' . htmlspecialchars($record->creditor_branch_name),

                                get_contact_widget($record->creditor_branch_contact, true, true)

                            ];

                            if( $record->other_creditors )
                            {
                                $financer_info = array_merge($financer_info, [
                                    '<strong>Other Financer(s)</strong>',
                                    nl2br(htmlspecialchars($record->other_creditors))
                                ]);
                            }

                            echo implode('<br/>', $financer_info), '<br/>';
                        }
                        echo  $record->care_of ? '<br/><strong>Care of</strong><br>' . nl2br(htmlspecialchars($record->care_of)) : '';
                        ?>
                    </td>
                    <td>
                        <table>
                            <tr>
                                <td class="no-border">
                                    <strong>Location of Risk:</strong><br>
                                    <?php echo nl2br(htmlspecialchars($object_attributes->risk_locaiton)) ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="no-border">
                                    <strong>Period of Insurance:</strong><br>
                                    From: <?php echo $record->start_date ?><br>
                                    To: <?php echo $record->end_date ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="no-border">
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
                        </table>
                    </td>
                </tr>
                <?php
                $form_elements = _OBJ_ENG_BL_validation_rules($record->portfolio_id);

                /**
                 * Item List
                 */
                $section_elements   = $form_elements['items'];
                $items              = $object_attributes->items ?? [];
                $item_count         = count( $items );
                ?>
                <tr>
                    <td colspan="2">
                        <strong>1. BOILER AND PRESSURE PLANT</strong><br>
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
                                                <?php echo htmlspecialchars($value)?>
                                            </td>
                                        <?php endforeach ?>
                                    </tr>
                                <?php endforeach ?>
                                <tr>
                                    <td colspan="4" class="text-bold">Total Sum Insured Amount(Rs.)</td>
                                    <td class="text-bold text-right"><?php echo number_format($record->object_amt_sum_insured, 2) ?></td>
                                    <td>&nbsp;</td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <?php
                        $section_elements   = $form_elements['third_party'];
                        $items              = $object_attributes->third_party ?? NULL;
                        $item_count         = count( $items->limit ?? [] );
                        ?>
                        <strong>2. THIRD PARTY LIABILITY</strong><br>
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
                                $total_tp_liability =  _OBJ_ENG_BL_compute_tpl_amount($object_attributes->third_party->limit ?? []);
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
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo htmlspecialchars($object_attributes->others->limit_per_event); ?></td>
                </tr>
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
        $this->load->view('policies/print/_schedule_footer_en');
        ?>
    </body>
</html>