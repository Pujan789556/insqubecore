<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Object Snippet: Property Popover
*/
$districts 	= district_dropdown();
$attributes = $record->attributes ? json_decode($record->attributes) : NULL;
$items = $attributes->items ?? [];
$item_count = count($items);
// echo '<pre>'; print_r($record);echo '</pre>';

$v_rules 		= _OBJ_PROPERTY_validation_rules();
$location_rules = $v_rules['property_location'];
$item_types 	= _OBJ_PROPERTY_item_type_dropdown(FALSE);
$single_item_rule = $v_rules['property_item_list'][array_keys($item_types)[0]];

$risk_category_dropdown = _PROPERTY_risk_category_dropdown();
$item_type_indices 		= _OBJ_PROPERTY_item_type_index();

$index = 1;;
foreach($items as $item):
?>
	<h4>Property - <?php echo  $index++;?></h4>
	<table class="table table-bordered table-condensed no-margin">
	    <tr>
	        <th>Risk Category</th>
	        <td>
	            <?php echo $risk_category_dropdown[$item->risk_category] ?? $item->risk_category; ?>
	        </td>
	        <th>Risk Code</th>
	        <td>
	            <?php echo $item->risk_code; ?>
	        </td>
	    </tr>
	</table>


	<table class="table table-bordered table-condensed no-margin">
	    <tr>
	    	<?php foreach($location_rules as $elem): ?>
	    		<th><?php echo $elem['label'] ?></th>
	    	<?php endforeach ?>
	    </tr>
	    <tr>
	        <?php foreach($location_rules as $elem): ?>
	    		<td>
	    			<?php
	    			$key = $elem['_key'];
	    			$val = $item->{$key} ?? NULL;
	    			$val = $elem['_data'][$val] ?? $val;
	    			echo $val;
	    			?>
    			</td>
	    	<?php endforeach ?>
	    </tr>
	</table>

	<table class="table table-bordered table-condensed">
        <thead>
            <tr>
            	<th width="5%">S.N.</th>
            	<th width="45%">Item Description</th>
            	<th width="15%">Usage Type</th>
            	<th width="15%">Sum Insured</th>
            	<th width="20%">Remarks</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $i = 0;
            $item_list = $item->list ?? NULL;
            $si_per_property = 0.00;
            foreach($item_list as $single ):
            	if($single->item_sum_insured):
            		$si_per_property = bcadd($si_per_property, $single->item_sum_insured, IQB_AC_DECIMAL_PRECISION);
            		?>
	                <tr>
	                	<td><?php echo $item_type_indices[$i++]; ?></td>
	                	<td><?php echo $item_types[$single->item_type] ?></td>
	                	<td><?php echo _OBJ_PROPERTY_usage_type_dropdown(FALSE)[$single->item_usage_type]?? $single->item_usage_type ?></td>
	                	<td class="text-right"><?php echo number_format($single->item_sum_insured, 2); ?></td>
	                	<td><?php echo htmlspecialchars($single->item_remarks) ?></td>
	                </tr>
            <?php
            	endif;
        	endforeach ?>
        </tbody>
        <tfoot>
        	<tr>
        		<th colspan="3">Total Sum Insured of this Property (Rs.)</th>
        		<th class="text-right"><?php echo number_format($si_per_property, 2); ?></th>
        		<th>&nbsp;</th>
        	</tr>
        </tfoot>
    </table>
<?php endforeach?>
<table class="table table-bordered table-condensed no-margin">
    <tr>
        <th width="65%">Total Sum Insured (Rs.)</th>
        <th class="text-right" width="15%">
            <?php echo number_format($record->amt_sum_insured, 2); ?>
        </th>
        <th width="20%">&nbsp</th>
    </tr>
</table>
