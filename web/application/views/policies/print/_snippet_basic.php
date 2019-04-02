<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Schedule Component - Basic Information
 *
 * Dates, Agent Info, Portfolio Info
 *
 * Language: English & Nepali
 */
$labels = [
    'portfolio_label' => [
        'en' => 'Portfolio Type',
        'np' => 'बीमालेखको किसिम'
    ],

    'proposer_label' => [
        'en' => 'Policy Proposed By',
        'np' => 'प्रस्तावकको नाम',
    ],

    'proposed_date_label' => [
        'en' => 'Policy Proposed Date',
        'np' => 'बीमा प्रस्ताव भएको मिति',
    ],

    'issued_date_place_label' => [
        'en' => 'Policy Issued Date & Place',
        'np' => 'बीमालेख जारी भएको स्थान र मिति',
    ],

    'start_date_label' => [
        'en' => 'Policy Start Date',
        'np' => 'जोखिम बहन गर्न शूरु हुने मिति',
    ],

    'start_time_label' => [
        'en' => 'Time',
        'np' => 'समय',
    ],

    'agent_label' => [
        'en' => 'Agent Information',
        'np' => 'बीमा अभिकर्ताको नाम र इजाजत पत्र नम्बर',
    ],

    'duration_label' => [
        'en' => 'Policy Duration',
        'np' => 'बीमा अवधि',
    ],
];

$portfolio_name_col = 'portfolio_name_'.$lang;
?>

<table class="table">
    <tr>
        <td><?php echo $labels['portfolio_label'][$lang]; ?>: <?php echo $record->{$portfolio_name_col}; ?></td>
    </tr>

    <tr>
        <td><?php echo $labels['proposer_label'][$lang]; ?>: <?php echo nl2br(htmlspecialchars($record->proposer));?></td>
    </tr>

    <tr>
        <td><?php echo $labels['proposed_date_label'][$lang]; ?>: <?php echo $record->proposed_date?></td>
    </tr>

    <tr>
        <td><?php echo $labels['issued_date_place_label'][$lang]; ?>: <?php echo $record->branch_code?>, <?php echo $record->issued_date?></td>
    </tr>

    <tr>
        <td>
            <?php echo $labels['start_date_label'][$lang]; ?>: <?php echo $record->start_date?><br/>
            <?php echo $labels['start_time_label'][$lang]; ?>: <?php echo $record->start_time?>
        </td>
    </tr>

    <tr>
        <?php
        /**
         * Agent Details
         */
        $agent_text = implode(' ', array_filter([$record->agent_bs_code, $record->agent_ud_code]));
        ?>
        <td><?php echo $labels['agent_label'][$lang]; ?>: <?php echo $agent_text ? $agent_text : 'n/a';?></td>
    </tr>

    <tr>
        <td>
            <?php echo $labels['duration_label'][$lang]; ?>:
            <?php
            if($lang == 'en')
            {
                $date_text = $record->start_date . ' to ' . $record->end_date;
            }
            else
            {
                $date_text = $record->start_date . ' देखि ' . $record->end_date . ' सम्म';
            }
            echo $date_text;
            ?>
            (<?php echo _POLICY_duration_formatted($record->start_date, $record->end_date, $lang); ?>)
        </td>
    </tr>
</table>