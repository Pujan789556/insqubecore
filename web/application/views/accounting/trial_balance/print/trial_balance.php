<?php defined('BASEPATH') OR exit('No direct script access allowed');
ini_set('memory_limit', '-1');
/**
 * Trial Balance Print
 */
$header_footer = '<htmlpagefooter name="myfooter">
                            <table class="table table-footer no-border">
                                <tr>
                                    <td class="border-t">' .
                                        $this->settings->orgn_name_en . '<br/>' .
                                        $this->settings->address .
                                    '</td>' .
                                    '<td class="border-t" align="right">Page {PAGENO} of {nb}</td>' .
                                '</tr>
                            </table>
                        </htmlpagefooter>
                        <sethtmlpagefooter name="myfooter" value="on" />';
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
    <style type="text/css">
        .ledger-data td{font-size: 8pt;}
    </style>
    </head>
    <body>
        <?php
        /**
         * Header & Footer
         */
        ?>
        <!--mpdf
            <?php echo $header_footer;?>
        mpdf-->

        <?php
        /**
         * Ledger Details
         */
        $this->load->view('accounting/trial_balance/_list');
        ?>
    </body>
</html>