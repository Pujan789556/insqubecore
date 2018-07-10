<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Schedule Print : Agriculture - FISH
 */
$object_attributes      = json_decode($record->object_attributes);
$schedule_table_title   = 'माछाको बीमालेख';
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
                                    <strong>माछा पालिएको खोरको वास्तविक ठेगाना</strong><br/>
                                    <?php  echo nl2br(htmlspecialchars($object_attributes->risk_locaiton))?>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <strong>साझेदारको विवरण (नाम र ठेगाना)</strong><br/>
                                    <?php  echo nl2br(htmlspecialchars($object_attributes->partner_details))?>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <strong>लगानीकर्ताको (बैंक, वित्तिय कम्पनी वा सहकारी) विवरण</strong><br>
                                    <?php  echo nl2br(htmlspecialchars($object_attributes->invester_details))?>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <strong>इच्छाइएको व्यक्तिको विवरण</strong><br/>
                                    <table class="no-border">
                                        <tr>
                                            <td width="40%">नाम थर</td>
                                            <td><?php  echo nl2br(htmlspecialchars($object_attributes->nominee_name ?? ''))?></td>
                                        </tr>
                                        <tr>
                                            <td>बिमितसँगको नाता</td>
                                            <td><?php  echo nl2br(htmlspecialchars($object_attributes->nominee_relation ?? ''))?></td>
                                        </tr>
                                        <tr>
                                            <td>पिताको नाम</td>
                                            <td><?php  echo nl2br(htmlspecialchars($object_attributes->nominee_father ?? ''))?></td>
                                        </tr>
                                        <tr>
                                            <td>माताको नाम</td>
                                            <td><?php  echo nl2br(htmlspecialchars($object_attributes->nominee_mother ?? ''))?></td>
                                        </tr>
                                        <tr>
                                            <td>संपर्क नं (मोबाईल / आवास)</td>
                                            <td><?php  echo nl2br(htmlspecialchars($object_attributes->nominee_contact ?? ''))?></td>
                                        </tr>
                                    </table>
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
                                <td>बीमालेखको किसिम: <?php echo htmlspecialchars($record->portfolio_name); ?></td>
                            </tr>

                            <tr>
                                <td>प्रस्तावकको नाम: <?php echo nl2br(htmlspecialchars($record->proposer));?></td>
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
            </tbody>
        </table><br/>

        <table class="table">
            <?php
            $section_elements  = _OBJ_AGR_FISH_validation_rules($record->portfolio_id)['items'];
            $items              = $object_attributes->items ?? NULL;
            $item_count         = count( $items ?? [] );
            ?>
            <thead>
                <tr>
                    <td colspan="10" class="text-bold">बीमीत माछाको विवरण</td>
                </tr>
                <tr>
                        <td class="text-bold">क्र. सं.</td>
                        <?php foreach($section_elements as $elem): ?>
                            <td class="text-bold"><?php echo $elem['label'] ?></td>
                        <?php endforeach; ?>
                    </tr>
            </thead>
            <tbody>
                <?php
                $i = 1;
                $breed_dropdown = _OBJ_AGR_breed_dropdown( $object_attributes->bs_agro_category_id );
                foreach($items as $item_record): ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <?php foreach($section_elements as $elem):
                            $key =  $elem['_key'];
                            $value = $item_record->{$key};

                            $elem_data  = $elem['_data'] ?? NULL;

                            // Update Breed dropdown
                            if($elem['_key'] == 'breed')
                            {
                                $value = $breed_dropdown[$value];
                            }
                            else if($elem_data){
                                $value = $elem_data[$value];
                            }
                        ?>

                            <td <?php echo $key == 'sum_insured' ? 'class="text-right"' : '' ?>>
                                <?php echo $key == 'sum_insured' ? number_format($value, 2) : htmlspecialchars($value);?>
                            </td>
                        <?php endforeach ?>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="8">पोखरी/रेसवेको बीमा शुल्क (रु)</td>
                    <td class="text-right"><?php echo number_format(_OBJ_AGR_FISH_pond_sum_insured_amount($object_attributes), 2) ?></td>
                </tr>
                <tr>
                    <td colspan="8" class="text-bold">जम्मा बीमांक रकम(रु)</td>
                    <td class="text-bold text-right"><?php echo number_format($endorsement_record->net_amt_sum_insured, 2) ?></td>
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