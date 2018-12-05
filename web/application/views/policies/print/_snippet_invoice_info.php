<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Schedule Component - Footer
 *
 * English & Nepali
 */
$invoice_labels = [
    'label_invoice_no'      => ['en' => 'Invoice No', 'np' => 'कर बिजक नं'],
    'label_invoice_date'    => ['en' => 'Invoice Date', 'np' => 'कर बिजक मिति'],
    'label_receipt_no'      => ['en' => 'Receipt No', 'np' => 'रसिद नं'],
    'label_receipt_date'    => ['en' => 'Receipt Date', 'np' => 'रसिद मिति'],
];

$invoice_info = [
    $invoice_labels['label_invoice_no'][$lang]      => $first_invoice->invoice_code ?? '',
    $invoice_labels['label_invoice_date'][$lang]    => $first_invoice->invoice_date ?? '',

    $invoice_labels['label_receipt_no'][$lang]      => $first_invoice->receipt_code ?? '',
    $invoice_labels['label_receipt_date'][$lang]    => ( isset($first_invoice->receipt_datetime) && !empty($first_invoice->receipt_datetime) ? date('Y-m-d', strtotime($first_invoice->receipt_datetime)) : '' )
];
?>
<table class="table no-border" width="100%">
    <?php foreach($invoice_info as $label=>$value): ?>
        <tr>
            <td width="30%" class="no-padding"><strong><?php echo $label?>:</strong></td>
            <td class="no-padding"><?php echo $value?></td>
        </tr>
    <?php endforeach ?>
</table>