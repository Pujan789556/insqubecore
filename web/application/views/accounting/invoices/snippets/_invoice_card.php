<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Invoice: Invoice Card
*/
?>
<div class="box box-bordered box-solid" id="invoice-card">
    <div class="box-body">
        <!-- Main content -->
        <section class="invoice no-margin no-border">
            <!-- title row -->
            <div class="row">
                <div class="col-xs-12">
                    <h2 class="page-header">
                    <i class="fa fa-globe"></i> <?php echo $this->settings->orgn_name_en?>
                    <small class="pull-right">Invoice Date: <?php echo $record->invoice_date?></small>
                    </h2>
                </div>
                <!-- /.col -->
            </div>
            <!-- info row -->
            <div class="row invoice-info">
                <div class="col-sm-4 invoice-col">
                    From
                    <address>
                        <strong><?php echo $this->settings->orgn_name_en?></strong><br>
                        <?php echo nl2br($this->settings->address)?>
                    </address>
                    <p>PAN: <strong><?php echo $this->settings->pan_no?></strong></p>
                </div>
                <!-- /.col -->
                <div class="col-sm-4 invoice-col">
                    To
                    <address>
                        <strong><?php echo $record->customer_full_name?></strong><br>
                        <?php
                        $customer_address_record = parse_address_record($record, 'addr_customer_');
                        echo address_widget($customer_address_record, true, true);
                        ?>
                        <?php //echo get_contact_widget($record->customer_contact, true, true)?>
                    </address>
                </div>
                <!-- /.col -->
                <div class="col-sm-4 invoice-col">
                    <b>Invoice #<?php echo $record->invoice_code?></b><br/>
                    Policy # <strong><?php echo $record->policy_code?></strong><br/>
                    Branch: <strong><?php echo $record->branch_name_en?></strong>
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
            <!-- Table row -->
            <div class="row">
                <div class="col-xs-12 table-responsive">
                    <table class="table table-bordered table-responsive">
                        <thead>
                            <tr>
                                <th>Particulars</th>
                                <th class="text-right">Amount (Rs.)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $vat_row = array_pop($rows);
                            $taxable_total_amount = 0.00;
                            foreach($rows as $row):?>
                                <tr>
                                    <td><?php echo $row->description?></td>
                                    <td class="text-right"><?php echo number_format($row->amount, 2)?></td>
                                </tr>
                            <?php
                                $taxable_total_amount += $row->amount;
                            endforeach;?>

                                <tr>
                                    <th class="text-right">Taxable Total Amount</th>
                                    <th class="text-right"><?php echo number_format($taxable_total_amount, 2)?></th>
                                </tr>
                                <tr>
                                    <th class="text-right"><?php echo $vat_row->description?></th>
                                    <th class="text-right" class="text-right"><?php echo number_format($vat_row->amount, 2)?></th>
                                </tr>
                                <tr>
                                    <th class="text-right">Grand Total</th>
                                    <th class="text-right"><?php echo number_format($record->amount, 2)?></th>
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
            <!-- /.row -->
            <div class="row">
                <!-- accepted payments column -->
                <div class="col-xs-12">
                    <p class="text-muted well well-sm no-shadow" style="margin-top: 10px;">
                        Payment by Cheque/Drafts are subject to realisation
                    </p>
                </div>
            </div>
            <!-- /.row -->
        </section>
        <!-- /.content -->
    </div>
</div>
