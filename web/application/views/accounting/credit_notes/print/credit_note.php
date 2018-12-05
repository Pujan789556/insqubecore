<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Credit Note Print (PDF)
 */
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
    $branch_contact_prefix = $this->settings->orgn_name_en . ', ' . $record->branch_name_en;
    $header_footer = '<htmlpagefooter name="myfooter">
                        <table class="table table-footer no-border">
                            <tr>
                                <td class="border-t">'. address_widget_two_lines( parse_address_record($record), $branch_contact_prefix) .'</td>
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
         * Credit Note
         */
        ?>
        <!-- <div>
            <h2 class="border-b"></h2>
        </div> -->
        <table class="table no-border" width="100%">
            <tbody>
                <tr>
                    <td colspan="2" align="left">
                        <img style="margin-bottom: 20px;" src="<?php echo site_url('static/app/images/logo.png') ?>" alt="<?php echo $this->settings->orgn_name_en?>" width="200">
                    </td>
                    <td align="right"><h2>Credit Note # <?php echo $record->id?></h2></td>
                </tr>
                <tr>
                    <td>
                        From<br/>
                        <address>
                            <strong><?php echo $this->settings->orgn_name_en?></strong><br>
                            <?php echo nl2br($this->settings->address)?>
                        </address><br/>
                        <p>PAN No. : <strong><?php echo $this->settings->pan_no?></strong></p>
                    </td>
                    <td>
                        To<br/>
                        <address>
                            <strong><?php echo $record->customer_full_name_en?></strong><br>
                            <?php echo get_contact_widget($record->customer_contact, true, true)?>
                        </address>
                    </td>
                    <td align="right">
                        Date: <strong><?php echo $record->credit_note_date?></strong><br/>
                        Policy # <strong><?php echo $record->policy_code?></strong><br/>
                        Branch: <strong><?php echo $record->branch_name_en?></strong>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="row">
            <div class="col-xs-12 table-responsive">
                <table class="table table-bordered table-responsive" width="100%">
                    <thead>
                        <tr>
                            <td>Particulars</td>
                            <td align="right">Amount (Rs.)</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach($rows as $row):?>
                            <tr>
                                <td><?php echo $row->description?></td>
                                <td align="right"><?php echo number_format($row->amount, 2)?></td>
                            </tr>
                        <?php
                        endforeach;?>
                            <tr>
                                <td align="right" class="bold">Grand Total</td>
                                <td align="right" class="bold"><?php echo number_format($record->amount, 2)?></td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    Amount in Words (Rs.):
                                    <strong>
                                        <?php
                                        echo ucfirst( amount_in_words( number_format(abs($record->amount), 2) ) );
                                        ?>
                                    </strong>
                                </td>
                            </tr>
                    </tbody>
                </table>
            </div>
            <!-- /.col -->
        </div>

        <div class="row">
            <!-- accepted payments column -->
            <div class="col-xs-12">
                <p class="text-muted well well-sm no-shadow" style="margin-top: 10px;">
                    Payment by Cheque/Drafts are subject to realisation
                </p>
                <br/>
                <br/>
                <br/>
                <p style="text-align: right">Authorised Signature</p>
            </div>
        </div>
    </body>
</html>