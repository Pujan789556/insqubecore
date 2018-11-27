<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Schedule Print : MISCELLANEOUS - GROUP PERSONNEL ACCIDENT(GPA)
 */
$object_attributes  = json_decode($record->object_attributes);
$schedule_table_title   = 'सामुहिक दुर्घटना बीमालेख';
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
                                    $this->load->view('policies/print/_schedule_insured_party', ['lang' => 'np']);
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

                            <tr>
                                <td>
                                    <strong>बीमा लाभ</strong><br>
                                    <?php echo nl2br(htmlspecialchars($object_attributes->benefit ?? '')) ?>
                                </td>
                            </tr>
                        </table>
                    </td>

                    <td width="50%" class="no-padding no-border">
                        <?php
                        /**
                         * Basic Information
                         */
                        $this->load->view('policies/print/_schedule_basic',
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
        $this->load->view('policies/print/_schedule_footer', ['lang' => 'np']);
        ?>

        <pagebreak>

        <?php
        $item_headings  = _OBJ_MISC_GPA_item_headings_dropdown();
        $items              = $object_attributes->items ?? NULL;
        $item_count         = count( $items ?? [] );
         ?>
        <h3>बीमालेखले रक्षावरण गर्ने बीमितहरु</h3>
        <table class="table">
            <thead>
                <tr>
                    <td><strong>क्र. स.</strong></td>
                    <?php foreach($item_headings as $key=>$label): ?>
                        <td><strong><?php echo $label ?></strong></td>
                    <?php endforeach ?>
                </tr>
            </thead>
            <tbody>
                <?php
                $i = 1;
                foreach($items as $item_record): ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <?php foreach($item_headings as $key=>$label):
                            $value = $item_record->{$key};
                        ?>

                            <td <?php echo $key == 'sum_insured' ? 'class="text-right"' : '' ?>>
                                <?php echo $key == 'sum_insured' ? number_format($value, 2) : htmlspecialchars($value);?>
                            </td>
                        <?php endforeach ?>
                    </tr>
                <?php endforeach;?>
            </tbody>
        </table>
    </body>
</html>