<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Schedule Print : MISCELLANEOUS - EXPEDITION PERSONNEL ACCIDENT(EPA)
 */
$this->load->helper('ph_misc_epa');
$this->load->helper('forex');
$object_attributes  = json_decode($record->object_attributes);

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

    /**
     * Header & Footer
     */
    $creator_text = ( $record->created_by_profile_name ?? $record->created_by_username ) . ' - ' . $record->created_by_code;

    $verifier_text = $record->verified_by_code
                        ? ( $record->verified_by_profile_name ?? $record->verified_by_username ) . ' - ' . $record->verified_by_code
                        : '';

    $branch_contact_prefix = $this->settings->orgn_name_en . ', ' . $record->branch_name;

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
                                    <strong>Trekking Route:</strong> <?php echo htmlspecialchars($object_attributes->trek_route); ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>बीमांक रकम (रु):</strong> <?php echo number_format((float)$record->object_amt_sum_insured, 2, '.', '')?><br>
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
                                        <?php echo number_format((float)$amt_rescue_npr, 2, '.', '')?>
                                        (USD <?php echo number_format((float)$object_attributes->amt_rescue, 2, '.', '')?>)
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
                                    <strong class="border-b">बीमाशुल्क</strong><br><br>
                                    <?php $cost_calculation_table = json_decode($txn_record->cost_calculation_table ?? NULL);
                                    if($cost_calculation_table->schedule_cost_table):?>
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <td colspan="2"><strong>COST CALCULATION TABLE</strong></td>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($cost_calculation_table->schedule_cost_table as $row):?>
                                                    <tr>
                                                        <td><?php echo $row->label ?></td>
                                                        <td class="text-right"><?php echo number_format( (float)$row->value, 2, '.', '');?></td>
                                                    </tr>
                                                <?php endforeach ?>
                                            </tbody>
                                        </table><br>
                                    <?php endif ?>
                                    <table class="table no-margin table-bordered table-condensed">
                                        <tr>
                                            <td width="80%" class="text-right"><strong>बीमा शुल्क</strong></td>
                                            <td class="text-right"><strong><?php echo number_format((float)$txn_record->amt_total_premium, 2, '.', '')?></strong></td>
                                        </tr>
                                        <tr>
                                            <td class="text-right"><strong>टिकट दस्तुर</strong></td>
                                            <td class="text-right"><strong><?php echo number_format( (float)$txn_record->amt_stamp_duty, 2, '.', '')?></strong></td>
                                        </tr>
                                        <tr>
                                            <td class="text-right"><strong>मु. अ. क. (VAT)</strong></td>
                                            <td class="text-right"><strong><?php echo number_format( (float)$txn_record->amt_vat, 2, '.', '');?></strong></td>
                                        </tr>
                                        <tr>
                                            <td class="text-right"><strong>मु. अ. क.(VAT) सहित जम्मा दस्तुर</strong></td>
                                            <td class="text-right"><strong><?php echo number_format( (float)( $txn_record->amt_stamp_duty + $txn_record->amt_total_premium + $txn_record->amt_vat ) , 2, '.', '');?></strong></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo nl2br(htmlspecialchars($txn_record->txn_details)) ?></td>
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
    </body>
</html>