<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Object Popover: MISCELLANEOUS - BANKER'S BLANKET(BB)
*/
$attributes 	= $record->attributes ? json_decode($record->attributes) : NULL;
$form_elements 	= _OBJ_MISC_BB_validation_rules($record->portfolio_id);

$ref = $ref ?? '';
if($ref === 'policy_overview_tab')
{
    $col = 'col-xs-12';
}
else
{
    $col = 'col-md-6';
}
?>

<div class="row">
    <div class="<?php echo $col ?>">
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">Basic Information</h4>
            </div>
            <table class="table table-bordered table-condensed no-margin">
                <?php
                $basic_elements = $form_elements['basic'];

                foreach($basic_elements as $elem): ?>
                    <tr>
                        <th><?php echo $elem['label']; ?></th>
                        <td>
                            <?php
                            $value      = $attributes->{$elem['_key']};
                            $elem_data  = $elem['_data'] ?? NULL;
                            if($elem_data){
                                $value = $elem_data[$value];
                            }
                            echo $value;
                            ?>
                        </td>
                    </tr>
                <?php endforeach ?>
            </table>
        </div>
    </div>

    <div class="<?php echo $col ?>">
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">Sum Insured Information</h4>
            </div>
            <table class="table table-bordered table-condensed no-margin">
                <?php
                $basic_elements = $form_elements['sum_insured'];
                $sum_insured_record = $attributes->sum_insured;
                foreach($basic_elements as $elem): ?>
                    <tr>
                        <th><?php echo $elem['label']; ?></th>
                        <td class="text-right">
                            <?php
                            $value      = $sum_insured_record->{$elem['_key']};
                            $elem_data  = $elem['_data'] ?? NULL;
                            if($elem_data){
                                $value = $elem_data[$value];
                            }
                            echo $value;
                            ?>
                        </td>
                    </tr>
                <?php endforeach ?>
                <tr>
                    <td class="text-bold">Total Sum Insured Amount(Rs.)</td>
                    <td class="text-bold text-right"><?php echo number_format($record->amt_sum_insured, 2) ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>