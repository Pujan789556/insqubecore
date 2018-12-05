<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Schedule Component - Insured Party
 *
 * Language: English & Nepali
 */

$insured_title          = $lang == 'np' ? 'बीमीतको नाम थर, ठेगाना'                   : 'Name and address of Insured';
$financer_title         = $lang == 'np' ? 'बैंक वा वित्तिय कम्पनीको नाम, ठेगाना'    : 'Name and address of Financer(s)';
$care_of_title          = $lang == 'np' ? 'मार्फत'                                     : 'Care Of';

/**
 * Parse Address Record - Customer, Creditor Branch
 */
$customer_address_record        = parse_address_record($record, 'addr_customer_');
$cot_customer_address_record    = parse_address_record($record, 'addr_customer_cot_');

if($record->txn_type == IQB_POLICY_ENDORSEMENT_TYPE_OWNERSHIP_TRANSFER)
{
    $insured_party_name =  htmlspecialchars($record->cot_customer_name_en) . '<br/>';
}
else
{
    $insured_party_name =  htmlspecialchars($record->customer_name_en) . '<br/>';
}

/**
 * Policy Financed?
 */
if($record->flag_on_credit === 'Y')
{
    $financer_info = ["<strong>{$financer_title}</strong>"];
    foreach($creditors as $single)
    {
        $financer_info[] = $single->name_en . ', ' . $single->branch_name_en;
    }
    echo implode('<br/>', $financer_info), '<br/><br/>';
}
?>
<strong><?php echo $insured_title ?></strong><br/>
<?php
// Insured Party Name
echo $insured_party_name;


// Insured Party Address
$address_record = $record->txn_type == IQB_POLICY_ENDORSEMENT_TYPE_OWNERSHIP_TRANSFER
                        ? $cot_customer_address_record
                        : $customer_address_record;
$this->load->view('policies/print/_snippet_address', ['address_record' => $address_record]);

echo  $record->care_of ? '<br/><strong>'.$care_of_title.'</strong><br>' . nl2br(htmlspecialchars($record->care_of)) . '<br/>' : '';
?>