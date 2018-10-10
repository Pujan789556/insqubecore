<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Invoice Print (PDF)
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
    // $header_footer = '<htmlpagefooter name="myfooter">
    //                     <table class="table table-footer no-border">
    //                         <tr>
    //                             <td class="border-t">'. address_widget_two_lines( parse_address_record($record), $branch_contact_prefix) .'</td>
    //                         </tr>
    //                     </table>
    //                 </htmlpagefooter>
    //                 <sethtmlpagefooter name="myfooter" value="on" />';

    $inline_footer = '<table class="table table-footer no-border">
                        <tr>
                            <td class="border-t">'. address_widget_two_lines( parse_address_record($record), $branch_contact_prefix) .'</td>
                        </tr>
                    </table>';
    ?>
    </head>
    <body>
        <?php
        /**
         * Invoice
         */
        ?>
        <!-- <div>
            <h2 class="border-b"></h2>
        </div> -->
        <table class="table no-border" width="100%">
            <tbody>
                <tr>
                    <td colspan="2" align="left">
                        <img style="margin-bottom: 20px;" src="<?php echo logo_url();?>" alt="<?php echo $this->settings->orgn_name_en?>" width="200">
                    </td>
                    <td align="right">
                        <h2>Invoice # <?php echo $record->invoice_code?></h2>
                        <?php if($record->flag_printed == IQB_FLAG_ON): ?>
                            <h3 style="color:#666666;">DUPLICATE COPY</h3>
                        <?php endif ?>
                    </td>
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
                            <strong><?php echo $record->customer_full_name?></strong><br>
                            <?php
                            $customer_address_record = parse_address_record($record, 'addr_customer_');
                            echo address_widget($customer_address_record, true, true);
                            if($record->customer_pan)
                            {
                                echo '<br>PAN:', $record->customer_pan;
                            }
                            ?>
                        </address>

                    </td>
                    <td>
                        <table class="no-border">
                            <tr>
                                <td align="right" width="35%">Invoice Date:</td>
                                <td align="right"><strong><?php echo $record->invoice_date?></strong></td>
                            </tr>

                            <tr>
                                <td align="right" width="35%">Policy #:</td>
                                <td align="right"><strong><?php echo $record->policy_code?></strong></td>
                            </tr>
                            <tr>
                                <td align="right" width="35%">Branch:</td>
                                <td align="right"><strong><?php echo $record->branch_name_en?></strong></td>
                            </tr>
                        </table>

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
                        $vat_row = array_pop($rows);
                        $taxable_total_amount = 0.00;
                        foreach($rows as $row):?>
                            <tr>
                                <td><?php echo $row->description?></td>
                                <td align="right"><?php echo number_format($row->amount, 2)?></td>
                            </tr>
                        <?php
                            $taxable_total_amount += $row->amount;
                        endforeach;?>

                            <tr>
                                <td align="right" class="bold">Taxable Total Amount</td>
                                <td align="right" class="bold"><?php echo number_format($taxable_total_amount, 2)?></td>
                            </tr>
                            <tr>
                                <td align="right" class="bold"><?php echo $vat_row->description?></td>
                                <td align="right" class="bold"><?php echo number_format($vat_row->amount, 2)?></td>
                            </tr>
                            <tr>
                                <td align="right" class="bold">Grand Total</td>
                                <td align="right" class="bold"><?php echo number_format($record->amount, 2)?></td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    Amount in Words (Rs.):
                                    <strong>
                                        <?php
                                        echo ucfirst( amount_in_words( number_format($record->amount, 2, '.', '') ) );
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
                <p class="text-muted well well-sm no-shadow" style="font-size:8pt">
                    Payment by Cheque/Drafts are subject to realisation
                </p>
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