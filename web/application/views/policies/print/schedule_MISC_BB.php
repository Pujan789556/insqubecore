<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Schedule Print : MISCELLANEOUS - BANKER'S BLANKET(BB)
 */
$object_attributes  = json_decode($record->object_attributes);
$premium_attributes = json_decode($record->premium_attributes);
$total_premium  = (float)$endorsement_record->amt_basic_premium + (float)$endorsement_record->amt_pool_premium;
$grand_total    = $total_premium + $endorsement_record->amt_stamp_duty + $endorsement_record->amt_vat;
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
                    <td>बीमालेखको किसिम: <strong><?php echo $record->portfolio_name; ?></strong></td>
                </tr>
                <tr>
                    <td width="50%" class="no-padding">
                        <table class="table" width="100%">
                            <tr>
                                <td>
                                    <h4>बीमितको नाम ठेगाना</h4>
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
                                    नाम: <?php echo nl2br($this->security->xss_clean($record->proposer));?><br/>
                                    ठेगाना: <?php echo nl2br($this->security->xss_clean($record->proposer_address));?><br>
                                    पेशा: <?php echo nl2br($this->security->xss_clean($record->proposer_profession));?>
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
                                <td>बीमा अवधि: <?php echo $record->start_date?> देखि <?php echo $record->end_date?> सम्म</td>
                            </tr>

                            <tr>
                                <td>
                                    <?php $cost_calculation_table = json_decode($endorsement_record->cost_calculation_table ?? NULL);
                                        if($cost_calculation_table):?>
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <td colspan="2"><strong>बीमाशुल्क तालिका</strong></td>
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
                                    <table class="table no-margin table-bordered table-condensed">
                                        <tr>
                                            <td width="80%" class="text-right"><strong>कुल बीमा शुल्क</strong></td>
                                            <td class="text-right"><strong><?php echo number_format($total_premium, 2)?></strong></td>
                                        </tr>
                                        <tr>
                                            <td class="text-right"><strong>टिकट दस्तुर</strong></td>
                                            <td class="text-right"><strong><?php echo number_format( (float)$endorsement_record->amt_stamp_duty, 2)?></strong></td>
                                        </tr>
                                        <tr>
                                            <td class="text-right"><strong>टिकटको मु. अ. क. (VAT)</strong></td>
                                            <td class="text-right"><strong><?php echo number_format( (float)$endorsement_record->amt_vat, 2);?></strong></td>
                                        </tr>
                                        <tr>
                                            <td class="text-right"><strong>मु. अ. क.(VAT) सहित जम्मा दस्तुर</strong></td>
                                            <td class="text-right"><strong><?php echo number_format($grand_total , 2);?></strong></td>
                                        </tr>
                                    </table>
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

        <table class="table no-border">
            <tr>
                <td width="50%">
                    कर बिजक नं: <br/>
                    मिति:<br/>
                    रु.:
                </td>
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