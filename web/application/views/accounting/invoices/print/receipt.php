<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Invoice Receipt Print (PDF)
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
    $branch_contact_prefix = $this->settings->orgn_name_en . ', ' . $invoice_record->branch_name;
    $header_footer = '<htmlpagefooter name="myfooter">
                        <table class="table table-footer no-border">
                            <tr>
                                <td class="border-t">'. get_contact_widget_two_lines($invoice_record->branch_contact, $branch_contact_prefix) .'</td>
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

        <table class="table no-border" width="100%">
            <tbody>
                <tr>
                    <td align="left">
                        <img style="margin-bottom: 20px;" src="<?php echo site_url('public/app/images/logo.png') ?>" alt="<?php echo $this->settings->orgn_name_en?>" width="200">
                    </td>
                    <td align="right"><h2>Receipt# <?php echo $record->receipt_code?></h2></td>
                </tr>
                <tr>
                    <td>
                        <address>
                            <strong><?php echo $this->settings->orgn_name_en?></strong><br>
                            <?php echo nl2br($this->settings->address)?>
                        </address><br/>
                        <p>PAN No. : <strong><?php echo $this->settings->pan_no?></strong></p>
                    </td>

                    <td align="right">
                        Invoice Date: <strong><?php echo $invoice_record->invoice_date?></strong><br/>
                        Invoice# <strong><?php echo $invoice_record->invoice_code?></strong><br/>
                        Policy# <strong><?php echo $invoice_record->policy_code?></strong><br/>
                        Branch: <strong><?php echo $invoice_record->branch_name?></strong>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="row">
            <div class="col-xs-12 table-responsive" style="border: 1px solid #666; padding:10px;">
                <p class="receipt-description">
                    Received with thanks from <strong class="border-b"><?php echo $invoice_record->customer_full_name?></strong>,
                    a sum of rupees <strong class="border-b"><?php echo ucfirst( number_to_words( number_format($invoice_record->amount, 2, '.', '') ) );?> (<?php echo number_format($invoice_record->amount, 2, '.', '')?>)</strong>
                    in <strong class="border-b"><?php echo IQB_AC_PAYMENT_RECEIPT_MODES[$record->received_in]?></strong>
                    dated <strong class="border-b"><?php echo $record->received_in_date ?? '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'?></strong>
                    against Policy/Invoice No. <strong class="border-b"><?php echo $invoice_record->policy_code?> / <?php echo $invoice_record->invoice_code?></strong>.
                </p>
            </div>
            <!-- /.col -->
        </div>

        <div class="row">
            <!-- accepted payments column -->
            <div class="col-xs-12">
                <p class="text-muted well well-sm no-shadow" style="margin-top: 10px;">
                    Payment by Cheque/Drafts are subject to realisation.
                </p>
                <br/>
                <br/>
                <br/>
                <p style="text-align: right">Authorised Signature</p>
            </div>
        </div>
    </body>
</html>