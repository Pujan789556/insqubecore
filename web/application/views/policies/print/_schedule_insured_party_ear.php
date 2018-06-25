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
?>
<strong>Name and address of Principal</strong><br/>
<?php echo nl2br(htmlspecialchars($object_attributes->principal)) ?><br/><br/>

<strong><?php echo $insured_title ?></strong><br/>
<?php
echo htmlspecialchars($record->customer_name) ,
        '<br/>' , get_contact_widget($record->customer_contact, true, true), '<br/>';

/**
 * Policy Financed?
 */
if($record->flag_on_credit === 'Y')
{
    $financer_info = [
        '<strong>' . $financer_title . '</strong>',

        htmlspecialchars($record->creditor_name) . ', ' . htmlspecialchars($record->creditor_branch_name),

        get_contact_widget($record->creditor_branch_contact, true, true)

    ];

    if( $record->other_creditors )
    {
        $financer_info = array_merge($financer_info, [
            '<strong>'.$other_financer_title.'</strong>',
            nl2br(htmlspecialchars($record->other_creditors))
        ]);
    }

    echo implode('<br/>', $financer_info), '<br/>';
}
echo  $record->care_of ? '<br/><strong>'.$care_of_title.'</strong><br>' . nl2br(htmlspecialchars($record->care_of)) . '<br/>' : '';
?>