<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Schedule Print : MISCELLANEOUS - PERSONNEL ACCIDENT(PA)
 */
$object_attributes      = json_decode($record->object_attributes);
$schedule_table_title   = 'व्यक्तिगत दुर्घटना बीमालेख';
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
                    <td colspan="2">
                        <p>
                        अनुसूचीमा उल्लेख भएको प्रस्ताव तथा उद्घोषणलाई यस करारको आधार मान्ने गरी श्री <?php echo $this->settings->orgn_name_np?> (यस पछि बीमक भनिएको) ले प्राप्त गरेको र यस अनुसूचीमा उल्लेख गरे बमोजिम बीमाशुल्क भुक्तानी पनि प्राप्त भएकोले;<br>

                        अनुसूची बमोजिम बीमाशुल्क भुक्तानी गरे बापत जुन दुर्घटना घटेमा भुक्तानी दिने भनिएको हो सो घटना र भुक्तानी पाउने व्यक्तिको कानूनी अधिकार प्रमाणित भएको अवस्थामा यस करारको अधिनमा रही यस बीमालेख बमोजिमका रकमहरु बीमकले आफनो कार्यालयमा भुक्तानी दिनेछ । <br>

                        यस बीमालेखको अनुसूची, लाभको तालिका, परिभाषा, अपवादहरु, शर्तहरु र बीमितको विवरण सूची यस करारनामाको अभिन्न अंग मानिनेछ ।</p>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo _POLICY_schedule_title_prefix($record->status)?>: <strong><?php echo $record->code;?></strong></td>
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
                                    $this->load->view('policies/print/_snippet_insured_party', ['lang' => 'np']);
                                    ?>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <strong>इच्छाएको ब्यक्तिको</strong><br/>
                                    नाम थर : <?php echo htmlspecialchars($object_attributes->nominee) ?><br>
                                    पिताको नाम थर: <?php echo htmlspecialchars($object_attributes->nominee_father) ?><br>
                                    आमाको नाम थर: <?php echo htmlspecialchars($object_attributes->nominee_mother) ?><br>
                                    बीमित र इच्छाएको ब्यक्ति बीचको नाता: <?php echo htmlspecialchars($object_attributes->nominee_relation) ?>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    बीमांक रकम (रु): <?php echo number_format($endorsement_record->amt_sum_insured_net, 2)?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    लाभ: <?php echo htmlspecialchars($object_attributes->benefits) ?>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <?php
                                    /**
                                     * Invoice, Receipt Info
                                     */
                                    $this->load->view('policies/print/_snippet_invoice_info', ['lang' => 'np']);
                                    ?>
                                </td>
                            </tr>
                        </table>
                    </td>

                    <td width="50%" class="no-padding no-border">
                        <?php
                        /**
                         * Basic Information
                         */
                        $this->load->view('policies/print/_snippet_basic',
                            ['lang' => 'np', 'record' => $record]
                        );
                        ?>
                        <table class="table">
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

        <?php
        /**
         * Load Footer
         */
        $this->load->view('policies/print/_snippet_footer', ['lang' => 'np']);
        ?>
    </body>
</html>