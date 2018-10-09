<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Schedule Print : MISCELLANEOUS - EXPEDITION PERSONNEL ACCIDENT(EPA)
 */
$this->load->helper('ph_misc_epa');
$this->load->helper('forex');
$object_attributes      = json_decode($record->object_attributes);
$schedule_table_title   = 'सामुहिक दुर्घटना बीमालेख(EPA)';
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
                                    <strong>Trekking Route:</strong> <?php echo htmlspecialchars($object_attributes->trek_route); ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>बीमांक रकम (रु):</strong> <?php echo number_format((float)$record->object_amt_sum_insured, 2)?><br>
                                    (संलग्न बीमितको विवरण सूचि बमोजिम ।)
                                </td>
                            </tr>

                            <?php if ((float)$object_attributes->amt_rescue):?>
                                <tr>
                                    <td>
                                        <?php
                                        $amt_rescue_npr = forex_conversion($record->issued_date, 'USD', floatval($object_attributes->amt_rescue));
                                         ?>
                                        <strong>खोज तथा उद्धार रकम (रु):</strong>
                                        <?php echo number_format((float)$amt_rescue_npr, 2)?>
                                        (USD <?php echo number_format((float)$object_attributes->amt_rescue, 2)?>)
                                    </td>
                                </tr>
                            <?php endif ?>
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
                        यस बीमलेख अन्तर्गत हुलदंगा, आतंकवाद तथा  द्धेशपुर्ण  जोखिम वहन हुने छ | <br>
                        यस बीमालेख अन्तर्गत १६ वर्ष भन्दा मुनि तथा ६० वर्ष उमेर भन्दा माथिको कर्मचारीहरुको बीमा जोखिम बहन हुने छैन |
                    </td>
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
        $form_elements = _OBJ_MISC_EPA_validation_rules($record->portfolio_id);

        /**
         * Item List
         */
        $section_elements   = $form_elements['items'];
        // Remove First Element
        // array_shift($section_elements); // staff_trek_type

        $items              = $object_attributes->items ?? NULL;
        $item_count         = count( $items->sum_insured ?? [] );
        ?>

        <table class="table table-bordered table-condensed no-margin">
            <thead>
                <tr>
                    <td colspan="7"><strong>यस बीमालेखले रक्षावरण गरेका बीमितहरु</strong></td>
                </tr>
            </thead>
            <?php
            $staff_above_bc = [];
            $staff_bc       = [];
            for ($i=0; $i < $item_count; $i++)
            {
                // Extract Single Object
                $single_item = new stdClass();
                foreach($section_elements as $elem)
                {
                    $key = $elem['_key'];
                    $single_item->{$key} = $items->{$key}[$i];
                }
                $staff_trek_type = $single_item->staff_trek_type;
                echo $staff_trek_type;
                if($staff_trek_type == 'E')
                {
                    $staff_above_bc[] = $single_item;
                }
                else
                {
                    $staff_bc[] = $single_item;
                }
            }

            // echo '<pre>';
            // print_r($staff_above_bc);
            // print_r($staff_bc);exit;

            // Remove First Two Element
            array_shift($section_elements); // staff_trek_type
            array_shift($section_elements); // staff_type

            ?>

            <?php if($staff_above_bc): ?>
                <thead>
                    <tr>
                        <td colspan="7"><strong>Risk Covering above Base Camp (above 19000 feet) also.</strong></td>
                    </tr>
                    <tr>
                        <td>क्र सं</td>
                        <?php foreach($section_elements as $elem): ?>
                            <td><?php echo $elem['label'] ?></td>
                        <?php endforeach; ?>
                    </tr>

                </thead>
                <tbody>
                    <?php
                    $row_count = 1;
                    foreach ($staff_above_bc as $single): ?>
                        <tr>
                            <td><?php echo $row_count++; ?></td>
                            <?php foreach($section_elements as $elem):

                                $key =  $elem['_key'];
                                $value = $single->{$key};

                                // If dropdown, get the label
                                $value = $elem['_type'] == 'dropdown' ? $elem['_data'][$value] : $value;
                            ?>
                                <td <?php echo $key == 'sum_insured' || $key == 'medical' ? 'class="text-right"' : '' ?>>
                                    <?php echo $value?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            <?php endif ?>

            <?php if($staff_bc): ?>
                <thead>
                    <tr>
                        <td colspan="7"><strong>Risk Covering upto Base Camp (upto 19000 feet) only.</strong></td>
                    </tr>
                    <tr>
                        <td>क्र सं</td>
                        <?php foreach($section_elements as $elem): ?>
                            <td><?php echo $elem['label'] ?></td>
                        <?php endforeach; ?>
                    </tr>

                </thead>
                <tbody>
                    <?php
                    $row_count = 1;
                    foreach ($staff_bc as $single): ?>
                        <tr>
                            <td><?php echo $row_count++; ?></td>
                            <?php foreach($section_elements as $elem):

                                $key =  $elem['_key'];
                                $value = $single->{$key};

                                // If dropdown, get the label
                                $value = $elem['_type'] == 'dropdown' ? $elem['_data'][$value] : $value;
                            ?>
                                <td <?php echo $key == 'sum_insured' || $key == 'medical' ? 'class="text-right"' : '' ?>>
                                    <?php echo $value?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            <?php endif ?>
        </table>

        <table class="no-border">
            <tr>
                <td><?php echo nl2br(htmlspecialchars($endorsement_record->txn_details)) ?></td>
            </tr>
        </table>

    </body>
</html>