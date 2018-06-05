<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Schedule Print : MISCELLANEOUS - CASH IN SAFE
 */
$object_attributes      = json_decode($record->object_attributes);
$schedule_table_title   = $record->portfolio_name . ' बीमालेखको अनुसुची (सेड्युल)';
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
                                    <strong>बीमीतको विवरण</strong><br/>
                                    नाम थर, ठेगाना:<br/>
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
                                    बिमांक रकम (रु): <strong><?php echo number_format( (float)$endorsement_record->net_amt_sum_insured, 2);?></strong>
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

                    <td width="50%" class="no-padding">
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
                                <td>रक्षावरण गरिएका जोखिमहरु: <?php echo _OBJ_policy_package_dropdown($record->portfolio_id)[$record->policy_package]?></td>
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
                                    $this->load->view('endorsements/snippets/_schedule_cost_calculation_table_risks_MISC_CS', ['endorsement_record' => $endorsement_record]);
                                    ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td colspan="2"><?php echo nl2br(htmlspecialchars($endorsement_record->txn_details)) ?></td>
                </tr>
            </tbody>
        </table>
        <p style="text-align: center;">यस तालिकामा उल्लेख भएको प्रयोगको सीमा उल्लघंन भएमा बीमकले बीमितलाई क्षतिपूर्ति दिनेछैन ।</p>
        <table class="table">
            <tr>
                <td width="50%">
                    कर बिजक नं: <br/>
                    मिति:<br/>
                    रु.:
                </td>
                <td align="left">
                    <h4 class="underline"><?php echo htmlspecialchars($this->settings->orgn_name_np)?> तर्फबाट अधिकार प्राप्त अधिकारीको</h4>
                    <p style="line-height: 30px">दस्तखत:</p>
                    <p>नाम थर:</p>
                    <p>छाप:</p>
                    <p>दर्जा:</p>
                </td>
            </tr>
        </table>
    </body>
</html>