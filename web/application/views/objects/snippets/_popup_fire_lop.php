<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Object Popover: MISCELLANEOUS - Personnel Accident(PA)
*/
$attributes 	= $record->attributes ? json_decode($record->attributes) : NULL;
$form_elements 	= _OBJ_FIRE_LOP_validation_rules($record->portfolio_id);
?>
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
                    echo htmlspecialchars($value);
                    ?>
                </td>
            </tr>
        <?php endforeach ?>
    </table>
</div>