<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Policy: Details - Policy Premium Overview Card
*/
$cost_calculation_table = $endorsement_record->cost_calculation_table ? json_decode($endorsement_record->cost_calculation_table) : NULL;
$total_premium          = (float)$endorsement_record->amt_basic_premium + (float)$endorsement_record->amt_pool_premium;
$grand_total            = $total_premium + $endorsement_record->amt_stamp_duty + $endorsement_record->amt_vat;
?>

<table class="table">
    <thead>
        <tr><td colspan="2" align="center"><h3><?php echo $title;?></h3></td></tr>
    </thead>
    <tbody>
        <?php if($cost_calculation_table):?>
            <?php foreach($cost_calculation_table as $section_object):?>
                <tr>
                    <td><strong><?php echo $section_object->column_head?></strong></td>
                    <td class="no-padding">
                        <table class="table no-border">
                            <tr><td colspan="3"><strong><?php echo $section_object->title_np?></strong></td></tr>
                            <?php foreach($section_object->sections as $sub_section):?>
                                <tr>
                                    <?php
                                    $flag_section_total = $sub_section->section_total ?? FALSE;
                                    ?>
                                    <td align="<?php echo $flag_section_total ? 'right' : 'left'?>">
                                        <?php
                                        if($flag_section_total)
                                        {
                                            echo '<strong>' . $sub_section->title . '</strong>';
                                        }
                                        else
                                        {
                                            echo $sub_section->title;
                                        }
                                        ?>
                                    </td>
                                    <td align="center" width="20px">
                                        <?php echo  $sub_section->label ?? '&nbsp;';?>
                                    </td>
                                    <td align="right" width="120px" class="<?php echo $flag_section_total ? 'bold' : ''?>">
                                        <?php echo number_format((float)$sub_section->amount, 2) ?>
                                    </td>
                                </tr>
                            <?php endforeach?>
                        </table>
                    </td>
                </tr>
            <?php endforeach?>

            <tr>
                <td>&nbsp;</td>
                <td class="no-padding">
                    <table class="table" cellpadding="0" cellspacing="0">
                        <?php
                        if($endorsement_record->flag_short_term === IQB_FLAG_YES):
                            $full_premium_obj   = (object)_ENDORSEMENT__compute_full_premium($endorsement_record);
                            $total_premium_full = (float)$full_premium_obj->amt_basic_premium + (float)$full_premium_obj->amt_pool_premium;
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
                            <td align="right"><strong>टिकट दस्तुर</strong></td>
                            <td align="right"><strong><?php echo number_format( (float)$endorsement_record->amt_stamp_duty, 2);?></strong></td>
                        </tr>
                        <tr>
                            <td align="right"><strong>मु. अ. क. (VAT)</strong></td>
                            <td align="right"><strong><?php echo number_format( (float)$endorsement_record->amt_vat, 2);?></strong></td>
                        </tr>
                        <tr>
                            <td align="right"><strong>मु. अ. क.(VAT) सहित जम्मा दस्तुर</strong></td>
                            <td align="right"><strong><?php echo number_format( (float)$grand_total , 2);?></strong></td>
                        </tr>
                    </table>
                </td>
            </tr>

        <?php else:?>
            <tr><td colspan="2" align="center">No Premium Information Found!</td></tr>
        <?php endif?>
    </tbody>
</table>