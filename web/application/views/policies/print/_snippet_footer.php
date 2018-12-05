<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Schedule Component - Footer
 *
 * English & Nepali
 */

$footer_labels = [
    'label_signature_heading' => [
        'en' => 'Signed for and on behalf of ' . htmlspecialchars($this->settings->orgn_name_en) ,
        'np' => htmlspecialchars($this->settings->orgn_name_np) . 'को तर्फबाट अधिकार प्राप्त अधिकारीको'
    ],
    'label_signature' => ['en' => 'Authorized Signature', 'np' => 'दस्तखत'],
    'label_name' => ['en' => 'Name', 'np' => 'नाम थर'],
    'label_designation' => ['en' => 'Designation', 'np' => 'दर्जा'],
    'label_office_seal' => ['en' => 'Office Seal', 'np' => 'छाप'],
];

?>
<table class="table no-border" width="100%">
    <tr>
        <td width="50%"></td>
        <td>
            <h4 class="underline"><?php echo  $footer_labels['label_signature_heading'][$lang]?></h4>
            <p style="line-height: 30px"><?php echo  $footer_labels['label_signature'][$lang]?></p>
            <p><?php echo  $footer_labels['label_name'][$lang]?>:</p>
            <p><?php echo  $footer_labels['label_designation'][$lang]?>:</p>
            <p><?php echo  $footer_labels['label_office_seal'][$lang]?>:</p>
        </td>
    </tr>
</table>