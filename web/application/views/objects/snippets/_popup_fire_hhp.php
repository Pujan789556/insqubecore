<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Object Snippet: Fire - Householder Popover
*/
$districts 	= district_dropdown();
$attributes = $record->attributes ? json_decode($record->attributes) : NULL;
$form_elements 	= _OBJ_FIRE_HHP_validation_rules($record->portfolio_id);
?>
<div class="box box-solid box-bordered">
    <div class="box-header with-border">
        <h4 class="box-title">Basic Information</h4>
    </div>
    <table class="table table-bordered table-condensed no-margin">
        <tr>
            <th>Total Sum Insured (Rs.)</th>
            <td><?php echo number_format($record->amt_sum_insured,2) ?></td>
        </tr>
    </table>
</div>
<div class="box box-solid box-bordered">
    <div class="box-header with-border">
        <h4 class="box-title">Building Information</h4>
    </div>
    <table class="table table-bordered table-condensed no-margin">
        <?php
        $elements = $form_elements['building'];
        $building = $attributes->building;
        foreach($elements as $elem):
        	$key = $elem['_key'];
        	// Escape VDC ID
        	if($key == 'vdc') continue;
        	?>
            <tr>
                <th><?php echo $elem['label']; ?></th>
                <td>
                    <?php
                    $value      = $building->{$key} ?? NULL;
                    $elem_data  = $elem['_data'] ?? NULL;
                    if($elem_data){
                        $value = $elem_data[$value];
                    }
                    if($key == 'sum_insured'){
                        $value = number_format($value, 2);
                    }
                    echo htmlspecialchars($value);
                    ?>
                </td>
            </tr>
        <?php endforeach ?>
    </table>
</div>
<div class="box box-solid box-bordered">
    <div class="box-header with-border">
        <h4 class="box-title">Goods Information</h4>
    </div>
    <table class="table table-bordered table-condensed no-margin">
        <?php
        $elements = $form_elements['goods'];
        $goods = $attributes->goods;
        foreach($elements as $elem):
        	$key = $elem['_key'];
        	?>
            <tr>
                <th><?php echo $elem['label']; ?></th>
                <td>
                    <?php
                    $value      = $goods->{$key} ?? NULL;
                    $elem_data  = $elem['_data'] ?? NULL;
                    if($elem_data){
                        $value = $elem_data[$value];
                    }
                    if($key == 'sum_insured'){
                        $value = number_format(floatval($value), 2);
                    }
                    echo htmlspecialchars($value);
                    ?>
                </td>
            </tr>
        <?php
    	endforeach;
        if($attributes->document):
        ?>
        	<tr>
        		<th>Download Item List</th>
        		<td><?php echo anchor('objects/download/' . $attributes->document, 'Download', 'target="_blank"') ?></td>
        	</tr>
        <?php endif ?>
    </table>
</div>
