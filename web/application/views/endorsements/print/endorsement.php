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
    $header_footer = '<htmlpageheader name="myheader" value="on">
                        <table class="table no-border">
                            <tr>
                                <td align="left"><img style="margin-bottom: 20px;" src="'.site_url('public/app/images/logo.png').'" alt="'.$this->settings->orgn_name_en.'" width="200"></td>
                                <td align="right"><h2>'.$schedule_table_title.'</h2></td>
                            </tr>
                        </table>
                    </htmlpageheader>
                    <sethtmlpageheader name="myheader" show-this-page="1" value="on" />
                    <htmlpagefooter name="myfooter" value="on">
                        <table class="table table-footer no-border">
                            <tr>
                                <td class="border-t" align="left">'. $this->settings->orgn_name_en .'</td>
                                <td class="border-t" align="right">'. $this->settings->address .'</td>
                            </tr>
                        </table>
                    </htmlpagefooter>
                    <sethtmlpagefooter name="myfooter" show-this-page="1" value="on" />';
    ?>
    </head>
    <body>
        <!--mpdf
            <?php echo $header_footer;?>
        mpdf-->
        <br>
        <?php
        $count = count($records);
        $i = 1;
        foreach($records as $record):?>
            <table class="table" width="100%" >
                <thead><tr><td colspan="2" align="center"><h3 style="margin:0"><?php echo $schedule_table_title?></h3></td></tr></thead>
                <tbody>
                    <tr>
                        <td colspan="2">Attaching to & forming parto fo policy no. <?php echo $record->code; ?></td>
                    </tr>
                    <tr>
                        <td width="50%">
                            <table class="no-border">
                                <tr>
                                    <td>Endorsement Number</td>
                                    <td><?php echo $record->id; ?></td>
                                </tr>
                                <tr>
                                    <td>Policy Number</td>
                                    <td><?php echo $record->code; ?></td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="border-top:1px solid #333333 !important">
                                        <h4 style="margin:0">Insured Party Details</h4>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td>
                            <table class="no-border">
                                <tr>
                                    <td>Agent</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Issued At</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Issued Date</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="2">Policy Duration</td>
                                </tr>
                                <tr>
                                    <td class="text-right">From</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="text-right">To</td>
                                    <td></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td width="50%">
                            <table class="no-border">
                                <tr>
                                    <td>Old Sum Insured</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Added Sum Insured</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Total Sum Insured</td>
                                    <td></td>
                                </tr>
                            </table>
                        </td>
                        <td>
                            <h3 style="margin:0">Premium</h3>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <td>Title</td>
                                        <td>Charge</td>
                                        <td>Refund</td>
                                    </tr>
                                </thead>
                                <tr>
                                    <td>Basic Premium</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>RSTMDST</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Ownership Transfer Fee</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>VAT</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Total</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><h3 style="margin: 0">MEMORANDUM</h3></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo nl2br(htmlspecialchars($record->txn_details)); ?></td>
                    </tr>
                </tbody>
            </table>
        <?php
            /**
             * PDF Pagebreak for next Endorsement
             */
            if($count > 1 && $i < $count)
            {
                echo '<pagebreak>';
            }
        endforeach;
        /**
         * Endorsement Per Page
         */
        // exit;
        /*
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
        <?php endforeach */?>
    </body>
</html>