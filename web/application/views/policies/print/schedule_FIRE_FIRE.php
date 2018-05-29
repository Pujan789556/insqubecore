<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Schedule Print : FIRE
 */
$this->load->helper('ph_fire_fire');
$object_attributes      = json_decode($record->object_attributes);
$schedule_table_title   = 'अग्नि बीमालेखको तालिका (सेड्युल)';
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

    $branch_contact_prefix = $this->settings->orgn_name_en . ', ' . $record->branch_name_en;

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
            <thead><tr><td colspan="2" align="center"><h3><?php echo $schedule_table_title?></h3></td></tr></thead>
            <tbody>
                <tr><td colspan="2"><?php echo _POLICY_schedule_title_prefix($record->status)?>: <?php echo $record->code;?></td></tr>
                <tr>
                    <td width="50%" class="no-padding">
                        <table class="table" width="100%">
                            <tr>
                                <td>
                                    <strong>बीमीतको विवरण (नाम थर, ठेगाना):</strong><br/>
                                    <?php
                                    echo $this->security->xss_clean($record->customer_name) . '<br/>';
                                    echo '<br/>' . get_contact_widget($record->customer_contact, true, true);
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    पेशा: <?php echo $record->customer_profession;?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>बीमा योग्य हित रहेको बैंक/वित्तीय संस्थाको विवरण</strong> <br/>
                                    <strong>नाम:</strong>
                                    <?php echo implode(',', array_filter([$this->security->xss_clean($record->creditor_name), $this->security->xss_clean($record->creditor_branch_name)])); ?><br/>
                                    <strong>ठेगाना:</strong> <?php echo get_contact_widget($record->creditor_branch_contact, true, true); ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>बीमाको विषयवस्तु रहेको स्थान, भवन वा सम्पत्तिको विवरण</strong><br/>
                                    <?php

                                    $object = (object)[
                                        'attributes' => $record->object_attributes
                                    ];

                                    $this->load->view('objects/snippets/_schedule_snippet_fire', ['record' => $object ]);
                                     ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    रसिद नं.: <br/>
                                    रसिदको मिति:  समय:
                                </td>
                            </tr>
                        </table>
                    </td>

                    <td width="50%" class="no-padding">
                        <table class="table">
                            <tr>
                                <td>बीमालेखको किसिम: <?php echo $record->portfolio_name; ?></td>
                            </tr>

                            <tr>
                                <td>प्रस्तावकको नाम: <?php echo $record->proposer ? $this->security->xss_clean($record->proposer) : $this->security->xss_clean($record->customer_name);?></td>
                            </tr>

                            <tr>
                                <td>बीमा प्रस्ताव भएको मिति: <?php echo $record->proposed_date?></td>
                            </tr>

                            <tr>
                                <td>बीमालेख जारी भएको स्थान र मिति: <?php echo $this->dx_auth->get_branch_code()?>, <?php echo $record->issued_date?></td>
                            </tr>

                            <tr>
                                <td>रक्षावरण गरिएका जोखिमहरु: <?php echo _OBJ_policy_package_dropdown($record->portfolio_id)[$record->policy_package]?></td>
                            </tr>

                            <tr>
                                <td>
                                    जोखिम बहन गर्न शूरु हुने मिति: <?php echo $record->start_date?><br/>
                                    समय:
                                </td>
                            </tr>

                            <tr>
                                <?php
                                /**
                                 * Agent Details
                                 */
                                $agent_text = implode(' ', array_filter([$record->agent_bs_code, $record->agent_ud_code]));
                                ?>
                                <td>बीमा अभिकर्ताको नाम र इजाजत पत्र नम्बर: <?php echo $agent_text;?></td>
                            </tr>

                            <tr>
                                <td>बीमा अवधि: <?php echo $record->start_date?> देखि <?php echo $record->end_date?> सम्म</td>
                            </tr>

                            <tr>
                                <td>
                                    <?php
                                    $this->load->view('endorsements/snippets/_schedule_cost_calculation_table_risks_FIRE', ['endorsement_record' => $endorsement_record]);
                                    ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td colspan="2">
                        <strong>बीमालेखले रक्षावरण गरेको सम्पत्तिको विवरण</strong>
                        <?php if($object_attributes->item_attached === 'N'): ?>
                            <table>
                                <thead>
                                    <tr>
                                        <td>सम्पत्ति</td>
                                        <td>विवरण</td>
                                        <td align="right">बीमांक (रु)</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $items      = $object_attributes->items ?? NULL;
                                    $item_count = count( $items->category ?? [] );
                                    if($item_count):
                                        for ($i=0; $i < $item_count; $i++):?>
                                            <tr>
                                                <td><?php echo _OBJ_FIRE_FIRE_item_category_dropdown(FALSE)[ $items->category[$i] ]?></td>
                                                <td><?php echo nl2br(htmlspecialchars($items->description[$i])); ?></td>
                                                <td align="right"><?php echo $items->sum_insured[$i]; ?></td>
                                            </tr>
                                        <?php
                                        endfor;
                                    endif;?>
                                    <tr>
                                        <td colspan="2" align="right">जम्मा बीमांक (रु)</td>
                                        <td align="right"><?php echo  $record->object_amt_sum_insured;?></td>
                                    </tr>
                                </tbody>
                            </table>
                        <?php endif;?>
                    </td>
                </tr>

                <tr>
                    <td colspan="2"><?php echo nl2br(htmlspecialchars($endorsement_record->txn_details)); ?></td>
                </tr>
            </tbody>
        </table>
        <p style="text-align: center;">यस तालिकामा उल्लेख भएको प्रयोगको सीमा उल्लघंन भएमा बीमकले बीमितलाई क्षतिपूर्ति दिनेछैन ।</p>
        <table class="table no-border">
            <tr>
                <td width="50%">&nbsp;</td>
                <td align="left">
                    <h4 class="underline"><?php echo $this->settings->orgn_name_np?> तर्फबाट अधिकार प्राप्त अधिकारीको</h4>
                    <p style="line-height: 30px">दस्तखत:</p>
                    <p>नाम थर:</p>
                    <p>छाप:</p>
                    <p>दर्जा:</p>
                </td>
            </tr>
        </table>
    </body>
</html>