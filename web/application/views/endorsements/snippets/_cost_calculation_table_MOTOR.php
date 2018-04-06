<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Policy: Details - Policy Premium Overview Card
*/
$cost_calculation_table = $endorsement_record->cost_calculation_table ? json_decode($endorsement_record->cost_calculation_table) : NULL;
?>
<div class="box-body">
    <table class="table no-margin table-bordered">
        <tbody id="_premium-details">
            <?php if($cost_calculation_table):?>
                <?php foreach($cost_calculation_table as $section_object):?>
                    <tr>
                        <td><strong><?php echo $section_object->column_head?></strong></td>
                        <td class="no-padding">
                            <table class="table no-margin table-bordered table-condensed">
                                <tr><td colspan="2"><strong><?php echo $section_object->title_np?></strong></td></tr>
                                <?php foreach($section_object->sections as $sub_section):?>
                                    <tr>
                                        <td width="80%">
                                            <?php
                                            $flag_section_total = $sub_section->section_total ?? FALSE;
                                            if($flag_section_total)
                                            {
                                                echo '<strong class="pull-right">' . $sub_section->title . '</strong>';
                                            }
                                            else
                                            {
                                                echo $sub_section->title;
                                            }
                                            ?>
                                        </td>
                                        <td width="20%" class="text-right">
                                            <?php
                                            $label = $sub_section->label ?? NULL;
                                            if($label)
                                            {
                                                echo '<span class="text-muted pull-left">'.$label.'</span>';
                                            }

                                            // Amount
                                            echo number_format((float)$sub_section->amount, 2, '.', '');
                                            ?>
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
                        <table class="table no-margin table-bordered table-condensed">
                            <tr>
                                <td width="80%" class="text-right"><strong>जम्मा</strong></td>
                                <td class="text-right"><strong><?php echo number_format((float)$endorsement_record->amt_total_premium, 2, '.', '')?></strong></td>
                            </tr>
                            <tr>
                                <td class="text-right"><strong>टिकट दस्तुर</strong></td>
                                <td class="text-right"><strong><?php echo $endorsement_record->amt_stamp_duty;?></strong></td>
                            </tr>
                            <tr>
                                <td class="text-right"><strong>मु. अ. क. (VAT)</strong></td>
                                <td class="text-right"><strong><?php echo number_format( (float)$endorsement_record->amt_vat, 2, '.', '');?></strong></td>
                            </tr>
                            <tr>
                                <td class="text-right"><strong>मु. अ. क.(VAT) सहित जम्मा दस्तुर</strong></td>
                                <td class="text-right"><strong><?php echo number_format( (float)( $endorsement_record->amt_stamp_duty + $endorsement_record->amt_total_premium + $endorsement_record->amt_vat ) , 2, '.', '');?></strong></td>
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