<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Schedule Print : MISCELLANEOUS - GROUP PERSONNEL ACCIDENT(GPA)
 */

$object_attributes  = json_decode($record->object_attributes);
$schedule_table_title   = 'सामुहिक दुर्घटना बीमालेख';
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
                <tr>
                    <td colspan="2">
                        <p>
                        अनुसूचीमा उल्लेख भएको प्रस्ताव तथा उद्घोषणलाई यस करारको आधार मान्ने गरी श्री <?php echo $this->settings->orgn_name_np?> (यस पछि बीमक भनिएको) ले प्राप्त गरेको र यस अनुसूचीमा उल्लेख गरे बमोजिम बीमाशुल्क भुक्तानी पनि प्राप्त भएकोले;<br>

                        अनुसूची बमोजिम बीमाशुल्क भुक्तानी गरे बापत जुन दुर्घटना घटेमा भुक्तानी दिने भनिएको हो सो घटना र भुक्तानी पाउने व्यक्तिको कानूनी अधिकार प्रमाणित भएको अवस्थामा यस करारको अधिनमा रही यस बीमालेख बमोजिमका रकमहरु बीमकले आफनो कार्यालयमा भुक्तानी दिनेछ । <br>

                        यस बीमालेखको अनुसूची, लाभको तालिका, परिभाषा, अपवादहरु, शर्तहरु र बीमितको विवरण सूची यस करारनामाको अभिन्न अंग मानिनेछ ।</p>
                    </td>
                </tr>
                <tr>
                    <td><?php echo _POLICY_schedule_title_prefix($record->status)?>: <strong><?php echo $record->code;?></strong></td>
                    <td>बीमालेखको किसिम: <strong><?php echo $record->portfolio_name; ?></strong></td>
                </tr>
                <tr>
                    <td width="50%" class="no-padding">
                        <table class="table" width="100%">
                            <tr>
                                <td>
                                    <h4 class="border-b">बीमालेख धारक (Policy Holder) संस्थाको</h4><br/>
                                    नाम ठेगाना:<br/>
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
                                    <strong>यस बीमालेखले रक्षावरण गरेका बीमितहरु</strong><br/>
                                    संलग्न बीमितको विवरण सूचि बमोजिम ।
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <strong></strong><br/>
                                    बीमांक रकम (रु): <?php echo number_format((float)$record->object_amt_sum_insured, 2)?><br>
                                    (संलग्न बीमितको विवरण सूचि बमोजिम ।)
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
                                $agent_text = implode(', ', array_filter([$record->agent_name, $record->agent_ud_code]));
                                ?>
                                <td>बीमा अभिकर्ताको नाम र इजाजत पत्र नम्बर: <?php echo $agent_text;?></td>
                            </tr>

                            <tr>
                                <td>बीमा अवधि: <?php echo $record->start_date?> देखि <?php echo $record->end_date?> सम्म</td>
                            </tr>

                            <tr>
                                <td>
                                    <strong class="border-b">बीमाशुल्क गणना</strong><br><br>
                                    <?php
                                    /**
                                     * Policy Premium Card
                                     */
                                    $cost_calculation_table_view = _POLICY__partial_view__cost_calculation_table($record->portfolio_id);
                                    $this->load->view($cost_calculation_table_view, ['endorsement_record' => $endorsement_record, 'policy_record' => $record]);
                                    ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td colspan="2"><?php echo nl2br(htmlspecialchars($endorsement_record->txn_details)) ?></td>
                </tr>

                <tr>
                    <td colspan="2">
                        <strong class="border-b">भुक्तानी पाउने व्यक्तिः</strong><br><br>
                        जीवित भए स्वयं बीमित, सो नभए बीमितले इच्छाएको व्यक्ति, सो नभए बीमा ऐन, २०४९ को दफा ३८ अनुसार बीमितको आश्रित व्यक्तिले यस बीमालेख अन्तर्गत भुक्तानी पाउनेछ ।
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <strong class="border-b">भुक्तानी पाउने अवस्थाः</strong><br><br>
                        यो बीमालेख कायम रहेको अवधीमा यस बीमालेखमा परिभाषित दुर्घटनाको एक मात्र प्रत्यक्ष कारणबाट बीमितलाई भएको शारीरिक क्षतिमा यसै बीमालेखको लाभको तालिकामा उल्लेख भए बमोजिमको रकम बीमकले भुक्तानी दिनेछ ।
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