<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Policy: MOTOR - Cost calculation Summary Table
*/
$cost_calculation_table = json_decode($endorsement_record->cost_calculation_table ?? '[]');
$total_premium          = (float)$endorsement_record->amt_basic_premium + (float)$endorsement_record->amt_pool_premium;
$grand_total            = $total_premium + $endorsement_record->amt_stamp_duty + $endorsement_record->amt_vat;
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
            <tr>
                <td width="80%" class="text-right"><strong>जम्मा</strong></td>
                <td class="text-right"><strong><?php echo number_format((float)$total_premium, 2)?></strong></td>
            </tr>
            <tr>
                <td class="text-right"><strong>टिकट दस्तुर</strong></td>
                <td class="text-right"><strong><?php echo number_format( (float)$endorsement_record->amt_stamp_duty, 2);?></strong></td>
            </tr>
            <tr>
                <td class="text-right"><strong>मु. अ. क. (VAT)</strong></td>
                <td class="text-right"><strong><?php echo number_format( (float)$endorsement_record->amt_vat, 2);?></strong></td>
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