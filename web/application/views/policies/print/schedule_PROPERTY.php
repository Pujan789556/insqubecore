<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Schedule Print : FIRE
 */
$this->load->helper('ph_fire_fire');
$object_attributes      = json_decode($record->object_attributes);
$schedule_table_title   = 'सम्पत्ति बीमालेख तालिका';
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
        <!-- <pre><?php print_r($record); ?></pre> -->
        <table class="table" width="100%" style="margin-bottom: 8pt;">
            <thead><tr><td colspan="2" align="center"><h3><?php echo $schedule_table_title?></h3></td></tr></thead>
            <tbody>
                <tr>
                    <td colspan="2">
                        <?php echo _POLICY_schedule_title_prefix($record->status)?>: <?php echo $record->code;?>
                    </td>
                </tr>
                <tr>
                    <td>बीमकको कोड: <?php echo $this->settings->bs_company_code ?></td>
                    <td>बीमकको शाखाको कोड: <?php echo $record->branch_code ?></td>
                </tr>
                <tr>
                    <td>ग्राहकको कोड: <?php echo $record->customer_code ?></td>
                    <td>पूर्व बीमलेख नं (यदि भए):</td>
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
                                    <strong>मार्केटिङ कोड:</strong>
                                    <?php echo $record->sold_by_code ?>
                                </td>
                            </tr>
                        </table>
                    </td>

                    <td width="50%" class="no-padding">
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
                                    $this->load->view('policies/print/_snippet_invoice_info', ['lang' => 'np']);
                                    ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>

        <?php
        $districts          = district_dropdown('np');
        $item_type_indices  = _OBJ_PROPERTY_item_type_index();
        $item_types         = _OBJ_PROPERTY_item_type_dropdown(FALSE);

        $items = $object_attributes->items;
        $sn    = ['१', '२', '३', '४', '५'];
        $index = 0;
        foreach($items as $item):
         ?>
            <!-- <pre><?php print_r($items);?></pre> -->
            <table class="table" width="100%" style="margin-bottom: 8pt;">
                <tr>
                    <td>
                        <strong>बीमा गरिएको सम्पत्ति रहने स्थानको विवरण - <?php echo $sn[$index]; ?></strong><br>
                        <table class="table" width="100%" style="margin-bottom: 4pt;">
                            <tr>
                                <td width="30%">बीमा गरिएको सम्पत्ति रहने स्थानको पूरा ठेगाना</td>
                                <td>
                                    <?php
                                    $house_tole = [];
                                    $location = [];
                                    if($item->location_house_no)
                                    {
                                        $house_tole[] = "घर नं {$item->location_house_no}";
                                    }
                                    if($item->location_tole)
                                    {
                                        $house_tole[] = $item->location_tole;
                                    }

                                    if($house_tole)
                                    {
                                        $location[] = implode(', ', $house_tole);
                                    }

                                    $location[] = "$item->location_vdc - $item->location_ward_no, " . $districts[$item->location_district];

                                    $state = state_by_district($item->location_district);
                                    $location[] = $state->name_np;

                                    echo implode('<br/>', $location);
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td>बीमा गरिएको सम्पत्ति रहने स्थानको प्रकृति</td>
                                <td><?php echo htmlspecialchars($item->location_nature) ?></td>
                            </tr>
                            <tr>
                                <td>बीमा गरिएको सम्पत्तिको प्रकृति</td>
                                <td><?php echo htmlspecialchars($item->location_property_nature) ?></td>
                            </tr>
                            <tr>
                                <td>भवनको विवरण</td>
                                <td>
                                    भोगचलनको तरिका: <?php echo _OBJ_PROPERTY_usage_type_dropdown(false)[$item->location_usage_type] ?><br>
                                    तल्ला संख्या: <?php echo $item->location_storey_no ?><br>
                                    घरधनीको नाम: <?php echo htmlspecialchars($item->location_owner_name) ?><br>
                                    भवनको बनौट: <?php echo _OBJ_PROPERTY_risk_class_dropdown(false)[$item->location_risk_class] ?>
                                </td>
                            </tr>
                        </table>
                        <strong>बीमा गरिएको सम्पत्तिको विवरण - <?php echo $sn[$index++]; ?></strong><br>
                        <?php
                        $list_headings = ['क्र. सं.', 'भोगचलनको तरिका', 'बीमाङ्क रकम', 'कैफियत'];
                        ?>
                        <table class="table" width="100%">
                            <thead>
                                <tr>
                                    <td>क्र. सं.</td>
                                    <td>विवरण</td>
                                    <td>भोगचलनको तरिका</td>
                                    <td>बीमाङ्क रकम</td>
                                    <td>कैफियत</td>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i = 0;
                                $item_list = $item->list ?? NULL;
                                $si_per_property = 0.00;
                                foreach($item_list as $single ):
                                    if($single->item_sum_insured):?>
                                        <tr>
                                            <td><?php echo $item_type_indices[$i++]; ?></td>
                                            <td><?php echo $item_types[$single->item_type] ?></td>
                                            <td><?php echo _OBJ_PROPERTY_usage_type_dropdown(FALSE)[$single->item_usage_type]?? $single->item_usage_type ?></td>
                                            <td class="text-right"><?php echo number_format($single->item_sum_insured, 2); ?></td>
                                            <td><?php echo htmlspecialchars($single->item_remarks) ?></td>
                                        </tr>
                                <?php
                                    endif;
                                endforeach ?>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>
        <?php endforeach ?>


        <?php
        /**
         * Load Cost Calculation Table
         */
        $this->load->view('endorsements/snippets/_cost_calculation_table_PROPERTY',
            ['lang' => 'en', 'endorsement_record' => $endorsement_record]
        );
        ?>

        <?php if($endorsement_record->txn_details): ?>
            <table class="table" width="100%" style="margin-top:8pt">
                    <tr>
                        <td colspan="2" style="font-size: 8pt">
                            <?php echo nl2br(htmlspecialchars($endorsement_record->txn_details)); ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        <?php endif ?>
        <p style="text-align: center; font-size: 8pt">यस तालिकामा उल्लेख भएको प्रयोगको सीमा उल्लघंन भएमा बीमकले बीमितलाई क्षतिपूर्ति दिनेछैन ।</p>
    </body>
</html>