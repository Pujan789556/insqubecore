<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Schedule Print : BURGLARY
 */
$object_attributes      = json_decode($record->object_attributes);
$schedule_table_title   = $record->portfolio_name . ' बीमालेखको अनुसुची (सेड्युल)';
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
                                    <strong>कूल बीमंक रकम (रु)</strong>:
                                    <?php echo number_format($record->object_amt_sum_insured, 2); ?>
                                    <br><br><strong>अधिक</strong><br>
                                    <?php echo nl2br(htmlspecialchars($object_attributes->excess)) ?>
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
                    <td colspan="2">
                        <?php
                        /**
                         * Building and Item Information
                         */
                        $object = (object)[
                            'portfolio_id' => $record->portfolio_id,
                            'attributes' => $record->object_attributes,
                            'amt_sum_insured' => $record->object_amt_sum_insured
                        ];
                        $this->load->view('objects/snippets/_schedule_snippet_brg', ['record' => $object ]);
                         ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><?php echo nl2br(htmlspecialchars($endorsement_record->txn_details)) ?></td>
                </tr>
            </tbody>
        </table>
        <p style="text-align: center;">यस तालिकामा उल्लेख भएको प्रयोगको सीमा उल्लघंन भएमा बीमकले बीमितलाई क्षतिपूर्ति दिनेछैन ।</p>
        <?php
        /**
         * Load Footer
         */
        $this->load->view('policies/print/_schedule_footer', ['lang' => 'np']);
        ?>
    </body>
</html>