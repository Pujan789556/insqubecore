<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Endorsement Print
 */
$this->load->helper('ph_eng_car');

$schedule_table_title   = "सम्पुष्टि विवरण";
$record = $records[0];
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
    $header_footer = '<htmlpagefooter name="myfooter">
                        <table class="table table-footer no-border">
                            <tr>
                                <td class="border-t" align="left">Policy Code: '. $record->code .'</td>
                                <td class="border-t" align="right">'. $this->settings->orgn_name_en .'</td>
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
         <table class="table" width="100%">
            <thead><tr><td colspan="3" align="center"><h3><?php echo $schedule_table_title?></h3></td></tr></thead>
        </table>
        <?php
        /**
         * Endorsement Per Page
         */
        foreach($records as $record):?>
            <table class="table" width="100%">
                <tbody>
                    <tr>
                        <td width="100%">
                            <strong>सम्पुष्टि विवरण</strong><br>
                            <?php echo nl2br(htmlspecialchars($record->txn_details)); ?>
                        </td>
                    </tr>
                    <?php if($record->txn_type == IQB_POLICY_TXN_TYPE_ET ):
                        $total_premium = (float)$record->amt_basic_premium + (float)$record->amt_pool_premium;
                        $grand_total = $total_premium + $record->amt_stamp_duty + $record->amt_vat;
                    ?>
                        <tr>
                            <td width="100%" class="no-padding">
                                <table class="table" width="100%">
                                    <tr>
                                        <td>बीमा शुल्क</td>
                                        <td class="text-right"><?php echo number_format($total_premium, 2); ?></td>
                                    </tr>
                                    <tr>
                                        <td>टिकट दस्तुर</td>
                                        <td class="text-right"><?php echo number_format($record->amt_stamp_duty, 2); ?></td>
                                    </tr>
                                    <tr>
                                        <td>मु. अ. क. (VAT)</td>
                                        <td class="text-right"><?php echo number_format($record->amt_vat, 2);?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-right"><strong>मु. अ. क.(VAT) सहित जम्मा दस्तुर</strong></td>
                                        <td class="text-right"><strong><?php echo number_format( $grand_total , 2);?></strong></td>
                                    </tr>
                                </table>
                            </td>
                    <?php endif ?>
                </tbody>
            </table><pagebreak>
        <?php endforeach ?>
    </body>
</html>