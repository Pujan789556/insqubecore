<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Schedule Print : MISCELLANEOUS - HEALTH INSURANCE (HI)
 */
$this->load->helper('ph_misc_hi');
$object_attributes  = json_decode($record->object_attributes);

$schedule_table_title   = 'स्वास्थ्य उपचार बीमालेख';

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
                                    <h4 class="border-b">बीमालेख धारक (Policy Holder) संस्थाको</h4><br/>
                                    नाम ठेगाना:<br/>
                                    <?php
                                    /**
                                     * If Policy Object is Financed or on Loan, The financial Institute will be "Insured Party"
                                     * and the customer will be "Account Party"
                                     */
                                    if($record->flag_on_credit === 'Y')
                                    {
                                        echo '<strong>INS.: ' . htmlspecialchars($record->creditor_name) . ', ' . htmlspecialchars($record->creditor_branch_name) . '</strong><br/>';
                                        echo '<br/>' . get_contact_widget($record->creditor_branch_contact, true, true) . '<br/>';

                                        // Care of
                                        echo '<strong>A/C.: ' . htmlspecialchars($record->customer_name) . '<br/></strong>';
                                        echo '<br/>' . get_contact_widget($record->customer_contact, true, true);

                                        echo  $record->care_of ? '<br/>C/O.: ' . htmlspecialchars($record->care_of) : '';
                                    }
                                    else
                                    {
                                        echo htmlspecialchars($record->customer_name) . '<br/>';
                                        echo '<br/>' . get_contact_widget($record->customer_contact, true, true);
                                    }
                                    ?>
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
                                    $this->load->view($cost_calculation_table_view, ['endorsement_record' => $endorsement_record, 'policy_record' => $record, 'title' => $cost_table_title]);
                                    ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <strong>यस बीमालेखले रक्षावरण गरेका बीमित कर्मचारीहरु</strong><br/>
                        <?php
                        $item_headings  = _OBJ_MISC_HI_item_headings_dropdown();

                        /**
                         * Item List
                         */
                        $items              = $object_attributes->items ?? NULL;
                        $item_count         = count( $items->sum_insured ?? [] );
                        ?>

                        <table class="table table-bordered table-condensed no-margin">
                            <thead>
                                <tr>
                                    <?php foreach($item_headings as $key=>$label): ?>
                                        <td><?php echo $label ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>

                            <tbody>
                                <?php for ($i=0; $i < $item_count; $i++): ?>
                                    <tr>
                                        <?php foreach($item_headings as $key=>$label):
                                            $value = $items->{$key}[$i];
                                        ?>

                                            <td <?php echo $key == 'sum_insured' || $key == 'premium' ? 'class="text-right"' : '' ?>>
                                                <?php echo htmlspecialchars($value)?>
                                            </td>
                                        <?php endforeach ?>
                                    </tr>
                                <?php endfor ?>
                            </tbody>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td colspan="2"><?php echo nl2br(htmlspecialchars($endorsement_record->txn_details)) ?></td>
                </tr>
            </tbody>
        </table><br/>

        <?php
        /**
         * Load Footer
         */
        $this->load->view('policies/print/_schedule_footer_np');
        ?>
    </body>
</html>