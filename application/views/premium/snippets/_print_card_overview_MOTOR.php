<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Policy: Details - Policy Premium Overview Card
*/
$attributes = $premium_record->attributes ? json_decode($premium_record->attributes) : NULL;
?>

<table class="table">
    <thead>
        <tr><td colspan="2" align="center"><h3><?php echo $title;?></h3></td></tr>
    </thead>
    <tbody>
        <?php if($attributes):?>
            <?php foreach($attributes as $section_object):?>
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
                                        <?php echo number_format((float)$sub_section->amount, 2, '.', '') ?>
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
                        <tr>
                            <td width="80%" align="right"><strong>जम्मा</strong></td>
                            <td width="100px" align="right"><strong><?php echo number_format((float)$premium_record->total_amount, 2, '.', '')?></strong></td>
                        </tr>
                        <tr>
                            <td align="right"><strong>टिकट दस्तुर</strong></td>
                            <td align="right"><strong><?php echo $premium_record->stamp_duty;?></strong></td>
                        </tr>
                        <tr>
                            <td align="right"><strong>मु. अ. क. (VAT)</strong></td>
                            <td align="right"><strong><?php echo number_format( (float)($premium_record->stamp_duty + $premium_record->total_amount) * 0.13, 2, '.', '');?></strong></td>
                        </tr>
                        <tr>
                            <td align="right"><strong>मु. अ. क.(VAT) सहित जम्मा दस्तुर</strong></td>
                            <td align="right"><strong><?php echo number_format( (float)($premium_record->stamp_duty + $premium_record->total_amount) * 1.13, 2, '.', '');?></strong></td>
                        </tr>
                    </table>
                </td>
            </tr>

        <?php else:?>
            <tr><td colspan="2" align="center">No Premium Information Found!</td></tr>
        <?php endif?>
    </tbody>
</table>