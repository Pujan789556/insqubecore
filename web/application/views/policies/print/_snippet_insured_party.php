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
	$creditor_name 			= "name_{$lang}";
    $creditor_branch_name 	= "branch_name_{$lang}";
    $financer_info 			= ["<strong>{$financer_title}</strong>"];
    foreach($creditors as $single)
    {
        $financer_info[] = $single->{$creditor_name} . ', ' . $single->{$creditor_branch_name};
    }
    echo implode('<br/>', $financer_info), '<br/><br/>';
}
?>
<strong><?php echo $insured_title ?></strong><br/>
<?php

// Insured Party Name
$customer_name_col = "customer_name_{$lang}";
echo htmlspecialchars($record->{$customer_name_col}), '<br/>';

// Insured Party Address
$this->load->view('policies/print/_snippet_address', ['address_record' => $customer_address_record, 'lang' => $lang]);

echo  $record->care_of ? '<br/><strong>'.$care_of_title.'</strong><br>' . nl2br(htmlspecialchars($record->care_of)) . '<br/>' : '';
?>