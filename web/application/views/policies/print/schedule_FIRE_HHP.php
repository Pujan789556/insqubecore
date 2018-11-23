<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Schedule Print : FIRE - HOUSEHOLDER
 */
$this->load->helper('ph_fire_fire');
$object_attributes      = json_decode($record->object_attributes);
$schedule_table_title   = 'गार्हस्थ बीमालेख';
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
                                    पेशा: <?php echo $record->customer_profession;?>
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
                                <td>प्रस्तावकको नाम: <?php echo $record->proposer ? htmlspecialchars($record->proposer) : htmlspecialchars($record->customer_name);?></td>
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
                                    $this->load->view('endorsements/snippets/_schedule_cost_calculation_table_risks_FIRE_HHP', ['endorsement_record' => $endorsement_record]);
                                    ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <strong>बीमालेखले रक्षावरण गरेको सम्पत्तिको विवरण</strong><br/>
                        <table>
                            <thead>
                                <tr>
                                    <td>क्र. स.</td>
                                    <td>विवरण</td>
                                    <td align="right">बीमांक (रु)</td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>खण्ड १</td>
                                    <td>
                                        <?php
                                        $building = $object_attributes->building;

                                        /**
                                         * Building Details
                                         * ------------------
                                         *  Address:
                                         *  house no - x, tole, vdc ward no - x, district
                                         *
                                         *  Kitta No: xx, Storey No: yy
                                         */

                                        $bld_data = [];
                                        if($building->house_no)
                                            $bld_data[] = 'घर नं ' . $building->house_no;

                                        if($building->tole)
                                            $bld_data[] = $building->tole;

                                        $vdc_text = local_body_dropdown_by_district($building->district, 'np', FALSE)[$building->vdc] ?? '';
                                        if($vdc_text)
                                            $bld_data[] = $vdc_text . ' - ' . $building->ward_no;

                                        $district_text = district_dropdown('np', FALSE)[$building->district] ?? '';
                                        if($district_text)
                                            $bld_data[] = $district_text;

                                        echo "<strong>भवन</strong><br/>", implode(', ', $bld_data), '<br/>';

                                        $bld_data_other = [];
                                        if($building->plot_no)
                                            $bld_data_other[] = 'कित्ता नं: ' . $building->plot_no;

                                        if($building->storey_no)
                                            $bld_data_other[] = 'तल्ला ' . $building->storey_no;

                                        echo implode(', ', $bld_data_other);
                                         ?>
                                    </td>
                                    <td align="right"><?php echo number_format($building->sum_insured, 2); ?></td>
                                </tr>

                                <tr>
                                    <td>खण्ड २</td>
                                    <td>
                                        <strong>मालसामान</strong><br>
                                        <?php
                                        /**
                                         * SUM Insured of Goods must be Mentioned in order to have goods information.
                                         */
                                        $goods = $object_attributes->goods;
                                        if($goods->sum_insured)
                                        {
                                            echo nl2br(htmlspecialchars($goods->description));

                                            $attached_text = "<br/>संलग्न सूची बमोजिम: ";
                                            if($object_attributes->document)
                                            {
                                                $attached_text .= "छ ।";
                                            }
                                            else
                                            {
                                                $attached_text .= "छैन ।";
                                            }
                                            echo $attached_text;
                                        }
                                        ?>
                                    </td>
                                    <td align="right">
                                        <?php  echo number_format($goods->sum_insured, 2);?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>खण्ड ३</td>
                                    <td>टेलिभिजन दुर्घटनाबाट फुटेमा : अधिकतम क्षतिपूर्ति रकम रु. १५,०००।००</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>खण्ड ४</td>
                                    <td>व्यक्तिगत दुर्घटना बीमा  : अधिकतम क्षतिपूर्ति रकम रु. १००,०००।०० </td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="2" align="right"><strong>खण्ड १ र २ को जम्मा (रु)</strong></td>
                                    <td align="right"><strong><?php echo number_format($record->object_amt_sum_insured, 2) ?></strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo nl2br(htmlspecialchars($endorsement_record->txn_details)); ?></td>
                </tr>
            </tbody>
        </table>
        <p style="text-align: center;">यस तालिकामा उल्लेख भएको प्रयोगको सीमा उल्लङ्घन भएमा बीमकले बीमितलाई क्षतिपूर्ति दिनेछैन ।</p>
        <?php
        /**
         * Load Footer
         */
        $this->load->view('policies/print/_schedule_footer', ['lang' => 'np']);
        ?>
    </body>
</html>