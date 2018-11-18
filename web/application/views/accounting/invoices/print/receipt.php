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
    $branch_contact_prefix = $this->settings->orgn_name_en . ', ' . $invoice_record->branch_name_en;
    // $header_footer = '<htmlpagefooter name="myfooter">
    //                     <table class="table table-footer no-border">
    //                         <tr>
    //                             <td class="border-t">'. address_widget_two_lines( parse_address_record($invoice_record), $branch_contact_prefix) .'</td>
    //                         </tr>
    //                     </table>
    //                 </htmlpagefooter>
    //                 <sethtmlpagefooter name="myfooter" value="on" />';


    $inline_footer = '<table class="table table-footer no-border">
                        <tr>
                            <td class="border-t">'. address_widget_two_lines( parse_address_record($invoice_record), $branch_contact_prefix) .'</td>
                        </tr>
                    </table>';
    ?>
    </head>
    <body>
        <table class="table no-border" width="100%">
            <tbody>
                <tr>
                    <td align="left" width="40%">
                        <img style="margin-bottom: 20px;" src="<?php echo logo_path();?>" alt="<?php echo $this->settings->orgn_name_en?>" width="200">
                    </td>
                    <td width="20%" align="center"><h2>RECEIPT</h2></td>
                    <td width="40%" align="right">
                        <h3>Receipt# <?php echo $record->receipt_code?></h3>
                        <?php if($record->flag_printed == IQB_FLAG_ON): ?>
                            <h3 style="color:#666666;">DUPLICATE COPY</h3>
                        <?php endif ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <address>
                            <strong><?php echo $this->settings->orgn_name_en?></strong><br>
                            <span style="font-size: 8pt">
                                <?php echo nl2br($this->settings->address)?>
                            </span>

                        </address><br/>
                        <p>PAN No. : <strong><?php echo $this->settings->pan_no?></strong></p>
                    </td>
                    <td>
                        <table class="no-border">
                            <tr>
                                <td align="right" width="35%">Invoice Date:</td>
                                <td align="right"><strong><?php echo $invoice_record->invoice_date?></strong></td>
                            </tr>
                            <tr>
                                <td align="right" width="35%">Invoice #:</td>
                                <td align="right"><strong><?php echo $invoice_record->invoice_code?></strong></td>
                            </tr>
                            <tr>
                                <td align="right" width="35%">Policy #:</td>
                                <td align="right"><strong><?php echo $invoice_record->policy_code?></strong></td>
                            </tr>
                            <tr>
                                <td align="right" width="35%">Branch:</td>
                                <td align="right"><strong><?php echo $invoice_record->branch_name_en?></strong></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="row">
            <div class="col-xs-12 table-responsive receipt-box">
                <p class="receipt-description">
                    <?php
                    $customer_address_record = parse_address_record($invoice_record, 'addr_customer_');
                    $two_lines = address_widget_two_lines($customer_address_record);
                    $two_lines = explode('</p>', $two_lines);
                    $first_line = str_replace('<p>', '', $two_lines[0]);
                     ?>
                    Received with thanks from <strong class="border-b"><?php echo $invoice_record->customer_full_name?>,</strong>
                    <span class="border-b"><?php echo $first_line ?></span>

                    a sum of Nepalese Rupees <strong class="border-b"><?php echo number_format($invoice_record->amount, 2)?> (<?php echo ucfirst( amount_in_words( number_format($invoice_record->amount, 2, '.', '') ) );?>)</strong>

                    in <strong class="border-b"> <?php echo IQB_AC_PAYMENT_RECEIPT_MODES[$record->received_in]?></strong>
                    <?php if($record->received_in_ref):?>
                        <span class="border-b">( Ref: <?php echo $record->received_in_ref ?>)</span>
                    <?php endif ?>

                    dated <strong class="border-b"><?php echo $record->received_in_date ?? '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'?></strong>

                    against Policy/Invoice No. <strong class="border-b"><?php echo $invoice_record->policy_code?> / <?php echo $invoice_record->invoice_code?></strong>.
                </p>
            </div>
            <div class="col-xs-12 table-responsive receipt-box">
                <p class="receipt-description text-right">
                    Adjustment Amount (Rs.): <strong><?php echo $record->adjustment_amount ? number_format($record->adjustment_amount, 2) : 0.00;?></strong>
                </p>
            </div>
            <!-- /.col -->
        </div>

        <div class="row">
            <!-- accepted payments column -->
            <div class="col-xs-12">
                <p class="text-muted well well-sm no-shadow" style="font-size:8pt">
                    Payment by Cheque/Drafts are subject to realisation.
                </p>
                <br/>
                <br/>
                <p style="text-align: right">Authorised Signature</p>
            </div>
        </div>

        <?php
        /**
         * Show Footer
         */
        echo $inline_footer;
        ?>
    </body>
</html>