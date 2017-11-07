<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Object Snippet: Burglary Popover
*/
$districts 		= district_dropdown();
$attributes 	= json_decode($record->attributes ?? NULL);
$form_elements 	= _OBJ_MISC_BRG_validation_rules($record->portfolio_id);
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
    			<th width="30%"><?php echo $elem['label']; ?></th>
    			<td><?php echo $attributes->{$elem['_key']}; ?></td>
    		</tr>
		<?php endforeach ?>
    </table>
</div>

<div class="box-body" style="overflow-x: scroll;">
	<table class="table table-bordered table-condensed no-margin" >
	    <thead>
	        <tr>
	            <th>Owner Name(s)</th>
	            <th>Owner Address</th>
	            <th>Owner Contacts (Mobile/Phone)</th>
	            <th>Land Plot No.</th>
	            <th>House No.</th>
	            <th>Tole/Street Address</th>
	            <th>District</th>
	            <th>VDC/Municipality</th>
	            <th>Ward No.</th>
	            <th>No. of Storeys</th>
	            <th>Construction Category</th>
	            <th>Used For</th>
	        </tr>
	    </thead>
	    <?php
	    $land_building	= $attributes->land_building ?? NULL;
	    $item_count   	= count( $land_building->owner_name ?? [] );
	    ?>
	    <tbody class="form-inline">
	        <?php
	        if($item_count):
	            for ($i=0; $i < $item_count; $i++):?>
	        		<tr>
	        			<td><?php echo $land_building->owner_name[$i]?></td>
	        			<td><?php echo $land_building->owner_address[$i]?></td>
	        			<td><?php echo $land_building->owner_contacts[$i]?></td>
	        			<td><?php echo $land_building->plot_no[$i]?></td>
	        			<td><?php echo $land_building->house_no[$i]?></td>
	        			<td><?php echo $land_building->tole[$i]?></td>
	        			<td><?php echo $land_building->district[$i] ? $districts[ $land_building->district[$i] ] : ''?></td>
	        			<td><?php echo $land_building->vdc[$i]?></td>
	        			<td><?php echo $land_building->ward_no[$i]?></td>
	        			<td><?php echo $land_building->storey_no[$i]?></td>
	        			<td><?php echo $land_building->category[$i] ? _OBJ_MISC_BRG_item_building_category_dropdown()[ $land_building->category[$i] ] : ''?></td>
	        			<td><?php echo $land_building->used_for[$i]?></td>
	        		</tr>
	        <?php
	        	endfor;
	        endif;?>
	    </tbody>
	</table>
</div>
