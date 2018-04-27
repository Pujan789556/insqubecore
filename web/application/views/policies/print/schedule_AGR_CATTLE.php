<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Schedule Print : Agriculture - Cattle
 */

$this->load->helper('ph_agr_cattle');

$object_attributes  = json_decode($record->object_attributes);
$premium_attributes = json_decode($record->premium_attributes);

$schedule_table_title   = 'पशुधनको बीमालेख';

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
                <tr><td colspan="2"><?php echo _POLICY_schedule_title_prefix($record->status)?>: <?php echo $record->code;?></td></tr>
                <tr>
                    <td width="50%" class="no-padding">
                        <table class="table" width="100%">
                            <tr>
                                <td>
                                    <h4 class="border-b">बीमीतको विवरण</h4><br/>
                                    नाम थर, ठेगाना:<br/>
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
                                    <strong>पशुधन पालिएको गोठको वास्तविक ठेगाना</strong><br/>
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
                                    रसिद नं.: <br/>
                                    रसिदको मिति:  समय:
                                </td>
                            </tr>
                        </table>
                    </td>

                    <td width="50%" class="no-padding no-border">
                        <table class="table">
                            <tr>
                                <td>बीमालेखको किसिम: <?php echo $record->portfolio_name; ?></td>
                            </tr>

                            <tr>
                                <td>प्रस्तावकको नाम: <?php echo nl2br($this->security->xss_clean($record->proposer));?></td>
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
                                    <?php
                                    /**
                                     * Policy Premium Card
                                     */
                                    $cost_calculation_table_view = _POLICY__partial_view__cost_calculation_table($record->portfolio_id);
                                    $this->load->view($cost_calculation_table_view, ['endorsement_record' => $endorsement_record, 'policy_record' => $record, 'title' => $cost_table_title]);
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
            $section_elements  = _OBJ_AGR_CATTLE_validation_rules($record->portfolio_id)['items'];
            $items              = $object_attributes->items ?? NULL;
            $item_count         = count( $items->sum_insured ?? [] );
            ?>
            <thead>
                <tr>
                    <td colspan="10" class="text-bold">बीमीत पशुधनको विवरण</td>
                </tr>
                <tr>
                        <td class="text-bold">क्र. सं.</td>
                        <?php foreach($section_elements as $elem): ?>
                            <td class="text-bold"><?php echo $elem['label'] ?></td>
                        <?php endforeach; ?>
                    </tr>
            </thead>
            <tbody>
                <?php for ($i=0; $i < $item_count; $i++): ?>
                    <tr>
                        <td><?php echo $i+1; ?></td>
                        <?php foreach($section_elements as $elem):
                            $key =  $elem['_key'];
                            $value = $items->{$key}[$i];

                            $elem_data  = $elem['_data'] ?? NULL;
                            if($elem_data){
                                $value = $elem_data[$value];
                            }
                        ?>

                            <td <?php echo $key == 'sum_insured' ? 'class="text-right"' : '' ?>>
                                <?php echo $key == 'sum_insured' ? number_format($value, 2) : $value;?>
                            </td>
                        <?php endforeach ?>
                    </tr>
                <?php endfor ?>
                <tr>
                    <td colspan="9" class="text-bold">जम्मा बीमांक रकम(रु)</td>
                    <td class="text-bold text-right"><?php echo number_format($record->object_amt_sum_insured, 2) ?></td>
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