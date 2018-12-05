<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Schedule Component - Insured Party
 *
 * Language: English & Nepali
 */

$insured_title          = $lang == 'np' ? 'बीमीतको नाम थर, ठेगाना'                  : 'Name and address of Insured';
$financer_title         = $lang == 'np' ? 'बैंक वा वित्तिय कम्पनीको नाम, ठेगाना'   : 'Name and address of Financer(s)';
$care_of_title          = $lang == 'np' ? 'मार्फत'                  : 'Care Of';

/**
 * Parse Address Record - Customer, Creditor Branch
 */
$customer_address_record = parse_address_record($record, 'addr_customer_');

/**
 * Policy Financed?
 */
if($record->flag_on_credit === 'Y')
{
    $financer_info = ["<strong>{$financer_title}</strong>"];
    foreach($creditors as $single)
    {
        $financer_info[] = $single->name . ', ' . $single->branch_name;
    }
    echo implode('<br/>', $financer_info), '<br/><br/>';
}
?>
<strong><?php echo $insured_title ?></strong><br/>
<?php

// Insured Party Name
echo htmlspecialchars($record->customer_name_en), '<br/>';

// Insured Party Address
$this->load->view('policies/print/_snippet_address', ['address_record' => $customer_address_record]);

echo  $record->care_of ? '<br/><strong>'.$care_of_title.'</strong><br>' . nl2br(htmlspecialchars($record->care_of)) . '<br/>' : '';
?>