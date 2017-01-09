<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Policy: Details - Policy Premium Overview Card
*/
$attributes = $premium_record->attributes ? json_decode($premium_record->attributes) : NULL;
?>
<div class="box-body">
    <table class="table no-margin table-bordered">
        <tbody id="_premium-details">
            <?php if($attributes):?>
                <?php foreach($attributes as $section_object):?>
                    <tr>
                        <td><strong><?php echo $section_object->column_head?></strong></td>
                        <td class="no-padding">
                            <table class="table no-margin table-bordered table-condensed">
                                <tr><td colspan="2"><strong><?php echo $section_object->title_np?></strong></td></tr>
                                <?php foreach($section_object->sections as $sub_sections):?>
                                    <?php foreach($sub_sections as $section_object):?>
                                            <tr>
                                                <td width="80%">
                                                    <?php
                                                    $flag_section_total = $section_object->section_total ?? FALSE;
                                                    if($flag_section_total)
                                                    {
                                                        echo '<strong class="pull-right">' . $section_object->title . '</strong>';
                                                    }
                                                    else
                                                    {
                                                        echo $section_object->title;
                                                    }
                                                    ?>
                                                </td>
                                                <td width="20%" class="text-right">
                                                    <?php
                                                    $label = $section_object->label ?? NULL;
                                                    if($label)
                                                    {
                                                        echo '<span class="text-muted pull-left">'.$label.'</span>';
                                                    }

                                                    // Amount
                                                    echo number_format((float)$section_object->amount, 2, '.', '');
                                                    ?>
                                                </td>
                                            </tr>
                                    <?php endforeach?>
                                <?php endforeach?>
                            </table>
                        </td>
                    </tr>
                <?php endforeach?>

                <tr>
                    <td>&nbsp;</td>
                    <td class="no-padding">
                        <table class="table no-margin table-bordered table-condensed">
                            <tr>
                                <td width="80%" class="text-right"><strong>जम्मा</strong></td>
                                <td class="text-right"><strong><?php echo number_format((float)$premium_record->total_amount, 2, '.', '')?></strong></td>
                            </tr>
                            <tr>
                                <td class="text-right"><strong>टिकट दस्तुर</strong></td>
                                <td class="text-right"><strong><?php echo $premium_record->stamp_duty;?></strong></td>
                            </tr>
                            <tr>
                                <td class="text-right"><strong>मु. अ. क. (VAT)</strong></td>
                                <td class="text-right"><strong><?php echo number_format( (float)($premium_record->stamp_duty + $premium_record->total_amount) * 0.13, 2, '.', '');?></strong></td>
                            </tr>
                            <tr>
                                <td class="text-right"><strong>मु. अ. क.(VAT) सहित जम्मा दस्तुर</strong></td>
                                <td class="text-right"><strong><?php echo number_format( (float)($premium_record->stamp_duty + $premium_record->total_amount) * 1.13, 2, '.', '');?></strong></td>
                            </tr>
                        </table>
                    </td>
                </tr>

            <?php else:?>
                <tr><td colspan="2" class="text-muted text-center">No Premium Information Found!</td></tr>
            <?php endif?>
        </tbody>
    </table>
</div>