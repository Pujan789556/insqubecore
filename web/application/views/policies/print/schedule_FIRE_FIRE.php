<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Schedule Print : FIRE
 */
$this->load->helper('ph_fire_fire');
$object_attributes      = json_decode($record->object_attributes);
$schedule_table_title   = 'अग्नि बीमालेखको तालिका (सेड्युल)';
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
                                    <strong>कूल बीमंक रकम (रु)</strong>: <?php echo number_format($record->object_amt_sum_insured, 2); ?>
                                </td>
                            </tr>
                            <?php
                            $cost_calculation_table = $endorsement_record->cost_calculation_table ? json_decode($endorsement_record->cost_calculation_table) : NULL;
                            $risk_table     = NULL;
                            if($cost_calculation_table)
                            {
                                $risk_table       = $cost_calculation_table->risk_table;
                            }
                             if($risk_table): ?>
                                <tr>
                                   <td class="no-padding">
                                       <table class="table no-margin table-bordered">
                                           <thead>
                                               <tr>
                                                   <td>रक्षावरण गरिएका जोेखिमहरु</td>
                                                   <td class="text-right">दर (रु प्रति हजार)</td>
                                                   <td class="text-right">बीमाशुल्क (रु.)</td>
                                               </tr>
                                           </thead>
                                           <tbody>
                                               <?php foreach($risk_table as $dt): ?>
                                                    <tr>
                                                        <td><?php echo $dt[0] ?></td>
                                                        <td class="text-right"><?php //echo number_format((float)$dt[1], 3);?></td>
                                                        <td class="text-right"><?php echo number_format((float)$dt[2], 2);?></td>
                                                    </tr>
                                                <?php endforeach ?>
                                           </tbody>
                                       </table>
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

                    <td width="50%" class="no-padding">
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
                                    $this->load->view('endorsements/snippets/_schedule_cost_calculation_table_risks_FIRE', ['endorsement_record' => $endorsement_record]);
                                    ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td colspan="2">
                        <strong>बीमाको विषयवस्तु रहेको स्थान, भवन वा सम्पत्तिको विवरण</strong><br/>
                        <?php

                        $object = (object)[
                            'attributes' => $record->object_attributes
                        ];

                        $this->load->view('objects/snippets/_schedule_snippet_fire', ['record' => $object ]);
                         ?>
                    </td>
                </tr>

                <tr>
                    <td colspan="2" style="font-size: 9pt">
                        माथि उल्लेखित बीमाशुल्कको हिसाब विवरण तथा मालसामानको विवरण पछाडि पृष्‍ठमा उल्लेख भए बमोजिम कायम गरिएको छ ।
                    </td>
                </tr>

                <tr>
                    <td colspan="2" style="font-size: 8pt"><?php echo nl2br(htmlspecialchars($endorsement_record->txn_details)); ?></td>
                </tr>
            </tbody>
        </table>
        <p style="text-align: center; font-size: 9pt">यस तालिकामा उल्लेख भएको प्रयोगको सीमा उल्लघंन भएमा बीमकले बीमितलाई क्षतिपूर्ति दिनेछैन ।</p>
        <?php
        /**
         * Load Footer
         */
        $this->load->view('policies/print/_schedule_footer', ['lang' => 'np']);
        ?>

        <?php
        /**
         * Details Premium Distribution
         */
        $cost_calculation_table = $endorsement_record->cost_calculation_table ? json_decode($endorsement_record->cost_calculation_table) : NULL;
        $detail_premium_table = NULL;
        if($cost_calculation_table)
        {
            $summary_table          = $cost_calculation_table->summary_table;
            $detail_premium_table   = $cost_calculation_table->detail_premium_table ?? NULL;
        }

        $premium_computation_table  = json_decode($endorsement_record->premium_computation_table ?? '[]', TRUE);
        $portfolio_risks            = portfolio_risks($record->portfolio_id);
        $items                      = $object_attributes->items ?? [];

        $risk_dropdown = [];
        foreach($portfolio_risks as $pr)
        {
            $risk_dropdown[$pr->code] = $pr->name_en;
        }

        $manual_rate_table = $premium_computation_table['manual']['rate'] ?? [];

        if($manual_rate_table && $object_attributes->item_attached == IQB_FLAG_NO):
            $risk_reference = [];
        ?>
            <pagebreak>
            <h3>Schedule attaching to and forming part of the Policy No: <?php echo $record->code; ?></h3>
            <table class="table" style="font-size: 8pt">
                <thead>
                    <tr>
                        <td align="left">SNO</td>
                        <td align="left">Property</td>
                        <td align="left">Description</td>
                        <td align="right">Sum Insured</td>
                        <td align="right">Risk</td>
                        <td align="right">Rate</td>
                        <td align="right">Premium</td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $item_index = 0;
                    $sn = 1;
                    foreach($items as $item):
                        /**
                         * Risk Premium Table
                         */
                        $premium_table = [];
                        foreach( $manual_rate_table as $risk_code => $rates )
                        {
                            $rate = $rates[$item_index];
                            if($rate)
                            {
                                $premium_table[] = (object)[
                                    'risk'      => $risk_code,
                                    'rate'      => $rate,
                                    'premium'   => $item->sum_insured * $rate / 1000.00
                                ];
                                $risk_reference[$risk_code] = $risk_dropdown[$risk_code];
                            }
                        }

                        $risk_count = count($premium_table);
                        if($risk_count > 1)
                        {
                            $rowspan = $risk_count + 1;
                            $rowspan = 'rowspan="'.$rowspan.'"';
                        }
                        else
                        {
                            $rowspan = '';
                        }
                        ?>
                        <tr>
                            <td <?php echo $rowspan ?>><?php echo $sn++ ?></td>
                            <td <?php echo $rowspan ?>><?php echo _OBJ_FIRE_FIRE_item_category_dropdown(FALSE)[ $item->category ] ?></td>
                            <td <?php echo $rowspan ?>><?php echo nl2br(htmlspecialchars($item->description)) ?></td>
                            <td <?php echo $rowspan ?> align="right"><?php echo number_format($item->sum_insured, 2) ?></td>

                            <?php if($rowspan): ?>
                                </tr>
                                <?php foreach($premium_table as $pt):?>
                                        <tr>
                                            <td align="right"><?php echo $pt->risk ?></td>
                                            <td align="right"><?php echo number_format($pt->rate, 4) ?></td>
                                            <td align="right"><?php echo number_format($pt->premium, 2) ?></td>
                                        </tr>
                                    <?php
                                endforeach ?>
                            <?php else:
                                    $pt = $premium_table[0];
                                    ?>
                                    <td align="right"><?php echo $pt->risk ?></td>
                                    <td align="right"><?php echo number_format($pt->rate, 4) ?></td>
                                    <td align="right"><?php echo number_format($pt->premium, 2) ?></td>
                                </tr>
                            <?php endif ?>
                    <?php
                        // Next item index
                        $item_index++;
                    endforeach ?>

                </tbody>
            </table>
            <h4>KEYS</h4>
            <table class="no-border">
                <?php foreach($risk_reference as $key=>$label): ?>
                    <tr>
                        <td><?php echo $key ?></td>
                        <td><?php echo $label ?></td>
                    </tr>
                <?php endforeach ?>
            </table>
        <?php endif; ?>
    </body>
</html>