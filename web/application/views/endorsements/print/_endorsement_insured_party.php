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
$creditor_address_record        = parse_address_record($record, 'addr_creditor_');

if($record->txn_type == IQB_POLICY_ENDORSEMENT_TYPE_OWNERSHIP_TRANSFER)
{
    $insured_details =  htmlspecialchars($record->cot_customer_name) .
                        '<br/>' .
                        address_widget($cot_customer_address_record, true, true);
}
else
{
    $insured_details =  htmlspecialchars($record->customer_name) .
                        '<br/>' .
                        address_widget($customer_address_record, true, true);
}

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
echo $insured_details;

echo  $record->care_of ? '<br/><strong>'.$care_of_title.'</strong><br>' . nl2br(htmlspecialchars($record->care_of)) . '<br/>' : '';
?>