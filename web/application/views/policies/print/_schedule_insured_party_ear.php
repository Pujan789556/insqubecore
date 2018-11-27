<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Schedule Component - Insured Party - ENG - EAR
 *
 * Language: English
 */
$insured_title          = 'Name and address of Contractor';
$financer_title         = 'Name and address of Financer(s)';
$other_financer_title   = 'Other Financer(s)';
$care_of_title          = 'Care Of';

/**
 * Parse Address Record - Customer, Creditor Branch
 */
$customer_address_record = parse_address_record($record, 'addr_customer_');
?>
<strong>Name and address of Principal</strong><br/>
<?php echo nl2br(htmlspecialchars($object_attributes->principal)) ?><br/><br/>

<strong><?php echo $insured_title ?></strong><br/>
<?php
// Insured Party Name
echo htmlspecialchars($record->customer_name), '<br/>';

// Insured Party Address
$this->load->view('policies/print/_snippet_address', ['address_record' => $customer_address_record]);

/**
 * Policy Financed?
 */
if($record->flag_on_credit === 'Y')
{
    $financer_info = ["<br/><strong>{$financer_title}</strong>"];
    foreach($creditors as $single)
    {
        $financer_info[] = $single->name . ', ' . $single->branch_name;
    }
    echo implode('<br/>', $financer_info), '<br/>';
}
echo  $record->care_of ? '<br/><strong>'.$care_of_title.'</strong><br>' . nl2br(htmlspecialchars($record->care_of)) . '<br/>' : '';
?>