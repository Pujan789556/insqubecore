<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Schedule Component - Insured Party
 *
 * Language: English & Nepali
 */

$insured_title          = $lang == 'np' ? 'बीमीतको नाम थर, ठेगाना'                  : 'Name and address of Insured';
$financer_title         = $lang == 'np' ? 'बैंक वा वित्त कम्पनीको नाम थर, ठेगाना'   : 'Name and address of Financer(s)';
$other_financer_title   = $lang == 'np' ? 'अरु बैंक वा वित्त कम्पनीको विवरण'        : 'Other Financer(s)';
$care_of_title          = $lang == 'np' ? 'मार्फत'                  : 'Care Of';


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
    echo implode('<br/>', $financer_info), '<br/><br/>';
}
?>
<strong><?php echo $insured_title ?></strong><br/>
<?php
echo htmlspecialchars($record->customer_name) ,
        '<br/>' , get_contact_widget($record->customer_contact, true, true);

echo  $record->care_of ? '<br/><strong>'.$care_of_title.'</strong><br>' . nl2br(htmlspecialchars($record->care_of)) . '<br/>' : '';
?>