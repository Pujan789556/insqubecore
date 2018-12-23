<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Schedule Component - Cost Calculation Table
 *
 * English & Nepali
 */
if($lang == 'en')
{
    $total_premium_label        = "Total Premium";
    $annual_total_premium_label = "Total Annual Premium";
    $short_term_total_label     = "Total Short Term Premium (%s%% of Annual Premium)";
    $stamp_duty_label           = "Stamp Duty";
    $vat_label                  = "VAT";
    $grand_total_label          = "Grand Total (NRs.)";
}
else
{
    $total_premium_label        = "जम्मा बीमाशुल्क";
    $annual_total_premium_label = "जम्मा वार्षिक बीमाशुल्क";
    $short_term_total_label     = "छोटो अवधिको जम्मा बीमाशुल्क (वार्षिक बीमाशुल्कको %s%%)";
    $stamp_duty_label           = "टिकट दस्तुर";
    $vat_label                  = "मु. अ. क. (VAT)";
    $grand_total_label          = "जम्मा दस्तुर (रु)";
}
$total_premium              = (float)$endorsement_record->net_amt_basic_premium + (float)$endorsement_record->net_amt_pool_premium;
$total_premium_formatted    = number_format($total_premium, 2);
$grand_total                = number_format($total_premium + $endorsement_record->net_amt_stamp_duty + $endorsement_record->net_amt_vat, 2);
?>
<table class="table table-condensed">
    <?php
    if($endorsement_record->flag_short_term === IQB_FLAG_YES):
        $annual_total_premium = (float)$endorsement_record->gross_full_amt_basic_premium + (float)$endorsement_record->gross_full_amt_pool_premium;
        $spr_rate               = number_format($endorsement_record->short_term_rate, 2);
        ?>
        <tr>
            <td class="text-right"><?php echo $annual_total_premium_label ?></td>
            <td class="text-right"><?php echo $annual_total_premium?></td>
        </tr>
        <tr>
            <td class="text-right"><?php echo sprintf($short_term_total_label, $spr_rate); ?></td>
            <td class="text-right"><?php echo $total_premium_formatted?></td>
        </tr>
    <?php else: ?>
        <tr>
            <td class="text-right"><?php echo $total_premium_label ?></td>
            <td class="text-right"><?php echo $total_premium_formatted?></td>
        </tr>
    <?php endif; ?>
    <tr>
        <td class="text-right"><?php echo $stamp_duty_label ?></td>
        <td class="text-right"><?php echo number_format((float)$endorsement_record->net_amt_stamp_duty, 2)?></td>
    </tr>
    <tr>
        <td class="text-right"><?php echo $vat_label ?></td>
        <td class="text-right"><?php echo number_format((float)$endorsement_record->net_amt_vat, 2)?></td>
    </tr>
    <tr>
        <td class="text-right border-t-thicker"><strong><?php echo $grand_total_label ?></strong></td>
        <td class="text-right border-t-thicker"><strong><?php echo $grand_total;?></strong></td>
    </tr>
</table>
