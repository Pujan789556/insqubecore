<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Schedule Print : MISCELLANEOUS - BANKER'S BLANKET(BB)
 */
$object_attributes  = json_decode($record->object_attributes);
$schedule_table_title   = 'बैंकरको क्षतिपूर्ति बीमालेख';
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
                <tr>
                    <td><?php echo _POLICY_schedule_title_prefix($record->status)?>: <strong><?php echo $record->code;?></strong></td>
                    <td>बीमालेखको किसिम: <strong><?php echo htmlspecialchars($record->portfolio_name); ?></strong></td>
                </tr>
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
                                    रसिद नं.: <br/>
                                    रसिदको मिति:  समय:
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <?php
                                    $si_components = $object_attributes->sum_insured;
                                     ?>
                                    <strong>रक्षावरण गरिएका अवस्था तथा जोखिमहरु</strong><br>
                                    <table class="table no-border">
                                        <tr>
                                            <td>१. आधारभूत बीमांक (रक्षावरणको बुंदा नं (क) देखि (ङ) सम्म)</td>
                                            <td class="text-right"><?php echo number_format((float)$si_components->basic, 2)?></td>
                                        </tr>
                                        <tr>
                                            <td>२. थप बीमांक (रक्षावरणको बुंदा नं (क) को लागि)</td>
                                            <td class="text-right"><?php echo number_format((float)$si_components->cip, 2)?></td>
                                        </tr>
                                        <tr>
                                            <td>३. थप बीमांक (रक्षावरणको बुंदा नं (ख) को लागि)</td>
                                            <td class="text-right"><?php echo number_format((float)$si_components->cit, 2)?></td>
                                        </tr>
                                        <tr>
                                            <td>४. एटिएममा राखिएको अधिकतम रकम</td>
                                            <td class="text-right"><?php echo number_format((float)$object_attributes->cash_in_atm, 2)?></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>कर्मचारी संख्या</strong>: <?php echo $object_attributes->staff_count; ?>
                                </td>
                            </tr>
                        </table>
                    </td>

                    <td width="50%" class="no-padding no-border">
                        <table class="table">
                            <tr>
                                <td>
                                    <strong>बीमालेख धारकको तर्फबाट बीमा प्रस्ताव गर्ने प्रस्तावकको</strong><br/>
                                    नाम: <?php echo nl2br(htmlspecialchars($record->proposer));?><br/>
                                    ठेगाना: <?php echo nl2br(htmlspecialchars($record->proposer_address));?><br>
                                    पेशा: <?php echo nl2br(htmlspecialchars($record->proposer_profession));?>
                                </td>
                            </tr>

                            <tr>
                                <td>बीमा प्रस्ताव भएको मिति: <?php echo $record->proposed_date?></td>
                            </tr>

                            <tr>
                                <td>बीमालेख जारी भएको स्थान र मिति: <?php echo $this->dx_auth->get_branch_code()?>, <?php echo $record->issued_date?></td>
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
                                <td>
                                    बीमा अवधि: <?php echo $record->start_date?> देखि <?php echo $record->end_date?> सम्म
                                    (<?php echo _POLICY_duration_formatted($record->start_date, $record->end_date, 'np'); ?>)
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <?php
                                    /**
                                     * Load Cost Calculation Table
                                     */
                                    $this->load->view('endorsements/snippets/premium/_index',
                                        ['lang' => 'np', 'endorsement_record' => $endorsement_record]
                                    );
                                    ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td colspan="2">
                        <strong>अधिक (एक्सेस/डिडक्टिवल)</strong><br>
                        <?php echo nl2br(htmlspecialchars($object_attributes->excess_deductibles)) ?>
                    </td>
                </tr>

                <tr>
                    <td colspan="2">
                        <strong>सम्पुष्टीहरु</strong><br>
                        <?php echo nl2br(htmlspecialchars($endorsement_record->txn_details)) ?>
                    </td>
                </tr>

            </tbody>
        </table><br/>

        <?php
        /**
         * Load Footer
         */
        $this->load->view('policies/print/_schedule_footer', ['lang' => 'np']);
        ?>
    </body>
</html>