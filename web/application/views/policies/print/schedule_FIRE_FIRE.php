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
                <tr><td colspan="2"><?php echo _POLICY_schedule_title_prefix($record->status)?>: <?php echo $record->code;?></td></tr>
                <tr>
                    <td width="50%" class="no-padding">
                        <table class="table" width="100%">
                            <tr>
                                <td>
                                    <?php
                                    /**
                                     * Insured Party, Financer, Other Financer, Careof
                                     */
                                    $this->load->view('policies/print/_schedule_insured_party', ['lang' => 'np']);
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
                                <td>बीमालेखको किसिम: <?php echo htmlspecialchars($record->portfolio_name); ?></td>
                            </tr>

                            <tr>
                                <td>प्रस्तावकको नाम: <?php echo $record->proposer ? htmlspecialchars($record->proposer) : htmlspecialchars($record->customer_name);?></td>
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
                                        <td>क्र. स.</td>
                                        <td>सम्पत्ति</td>
                                        <td>विवरण</td>
                                        <td align="right">बीमांक (रु)</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $items      = $object_attributes->items ?? NULL;
                                    $item_count = count( $items ?? [] );
                                    $i = 0;
                                    if($item_count):
                                        foreach($items as $item_record):?>
                                            <tr>
                                                <td><?php echo ++$i; ?></td>
                                                <td><?php echo _OBJ_FIRE_FIRE_item_category_dropdown(FALSE)[ $item_record->category ]?></td>
                                                <td><?php echo nl2br(htmlspecialchars($item_record->description)); ?></td>
                                                <td align="right"><?php echo number_format($item_record->sum_insured, 2); ?></td>
                                            </tr>
                                        <?php
                                        endforeach;
                                    endif;?>
                                    <tr>
                                        <td colspan="3" align="right">जम्मा बीमांक (रु)</td>
                                        <td align="right"><?php echo  number_format($record->object_amt_sum_insured, 2);?></td>
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
        <?php
        /**
         * Load Footer
         */
        $this->load->view('policies/print/_schedule_footer', ['lang' => 'np']);
        ?>
    </body>
</html>