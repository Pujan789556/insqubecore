<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Policy: MOTOR - Cost calculation Summary Table
*/
$cost_calculation_table = json_decode($endorsement_record->cost_calculation_table ?? NULL);
$total_premium          = _ENDORSEMENT__total_premium($endorsement_record);
$grand_total            = _ENDORSEMENT__grand_total($endorsement_record);
?>
<table class="table no-margin table-bordered">
    <tbody>
        <?php if($cost_calculation_table):?>
            <?php foreach($cost_calculation_table as $section_object):?>
                <tr>
                    <td><?php echo $section_object->title_np?></td>
                    <td class="text-right"><?php echo number_format($section_object->sub_total, 2); ?></td>
                </tr>
            <?php endforeach?>

            <?php
            if($endorsement_record->flag_short_term === IQB_FLAG_YES):
                $total_premium_full = (float)$endorsement_record->gross_full_amt_basic_premium + (float)$endorsement_record->gross_full_amt_pool_premium;
                $spr_rate = $endorsement_record->short_term_rate;
                ?>
                <tr>
                    <td width="80%" align="right"><strong>जम्मा वार्षिक बीमाशुल्क</strong></td>
                    <td width="100px" align="right"><strong><?php echo number_format((float)$total_premium_full, 2)?></strong></td>
                </tr>
                <tr>
                    <td width="80%" align="right"><strong>छोटो अवधिको जम्मा बीमाशुल्क (वार्षिक बीमाशुल्कको <?php echo number_format($spr_rate, 2) ?>%)</strong></td>
                    <td width="100px" align="right"><strong><?php echo number_format((float)$total_premium, 2)?></strong></td>
                </tr>
            <?php else: ?>
                <tr>
                    <td width="80%" align="right"><strong>जम्मा बीमाशुल्क</strong></td>
                    <td width="100px" align="right"><strong><?php echo number_format((float)$total_premium, 2)?></strong></td>
                </tr>
            <?php endif; ?>
            <tr>
                <td class="text-right"><strong>टिकट दस्तुर</strong></td>
                <td class="text-right"><strong><?php echo number_format( (float)$endorsement_record->net_amt_stamp_duty, 2);?></strong></td>
            </tr>
            <tr>
                <td class="text-right"><strong>मु. अ. क. (VAT)</strong></td>
                <td class="text-right"><strong><?php echo number_format( (float)$endorsement_record->net_amt_vat, 2);?></strong></td>
            </tr>
            <tr>
                <td class="text-right"><strong>मु. अ. क.(VAT) सहित जम्मा दस्तुर</strong></td>
                <td class="text-right"><strong><?php echo number_format( (float)$grand_total , 2);?></strong></td>
            </tr>

        <?php else:?>
            <tr><td colspan="2" class="text-muted text-center">No Premium Information Found!</td></tr>
        <?php endif?>
    </tbody>
</table>