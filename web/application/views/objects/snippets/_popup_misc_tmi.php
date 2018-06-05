<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Object Popover: MISCELLANEOUS - TRAVEL MEDICAL INSURANCE(TMI)
*/
$attributes 	= $record->attributes ? json_decode($record->attributes) : NULL;
$form_elements 	= _OBJ_MISC_TMI_validation_rules($record->portfolio_id);

// Update Plan Dropdown data
$form_elements['basic'][2]['_data'] = _OBJ_MISC_TMI_plan_dropdown(FALSE);


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
    <div class="col-xs-12">
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">Basic Information</h4>
            </div>
            <table class="table table-bordered table-condensed no-margin">
                <?php
                $basic_elements = $form_elements['basic'];

                foreach($basic_elements as $elem): ?>
                    <tr>
                        <th width="30%"><?php echo $elem['label']; ?></th>
                        <td>
                            <?php
                            $value = $attributes->{$elem['_key']};
                            $value = ($elem['_type'] == 'dropdown' || $elem['_type'] == 'radio') ? $elem['_data'][$value] : $value;

                            /**
                             * if DOB, compute age
                             */
                            if( $elem['_key'] == 'dob')
                            {
                                $age    = date_difference($value, date('Y-m-d'), 'y');
                                $value  = "{$value} ({$age} years)";
                            }
                            echo htmlspecialchars($value);
                            ?>
                        </td>
                    </tr>
                <?php endforeach ?>
            </table>
        </div>
    </div>
</div>