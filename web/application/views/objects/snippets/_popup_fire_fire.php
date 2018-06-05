<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Object Snippet: Fire Popover
*/
$districts 	= district_dropdown();
$attributes = $record->attributes ? json_decode($record->attributes) : NULL;
?>
<div class="box-body" style="overflow-x: scroll;">
	<?php if($attributes->item_attached === 'Y'): ?>
		<table class="table table-bordered table-condensed no-margin">
		    <tr>
		        <th>Total Sum Insured (Rs)</th>
		        <td>
		            <?php echo $attributes->sum_insured; ?>
		        </td>
		    </tr>
		    <tr>
		        <th>Download Item List</th>
		        <td>
		            <?php echo anchor('objects/download/' . $attributes->document, 'Download', 'target="_blank"') ?>
		        </td>
		    </tr>
		</table>
	<?php else: ?>
		<table class="table table-bordered table-condensed no-margin">
		    <thead>
		        <tr>
		        	<th>S.N.</th>
		            <th>Item Category</th>
		            <th>Ownership</th>
		            <th>Description</th>
		            <th>Sum Insured(Rs.)</th>
		        </tr>
		    </thead>
		    <?php
		    $items     	= $attributes->items ?? [];
		    $item_count         = count( $items ?? [] );
		    ?>
		    <tbody class="form-inline">
		        <?php
		        $i = 1;
                    foreach($items as $item_record):?>
		            	<tr>
		            		<td><?php echo $i++; ?></td>
		            		<td><?php echo _OBJ_FIRE_FIRE_item_category_dropdown(FALSE)[ $item_record->category ]?></td>
		            		<td><?php echo _OBJ_FIRE_FIRE_item_ownership_dropdown(FALSE)[ $item_record->ownership ]; ?></td>
		            		<td><?php echo nl2br(htmlspecialchars($item_record->description)); ?></td>
		            		<td class="text-right"><?php echo number_format($item_record->sum_insured, 2); ?></td>
		            	</tr>
		            <?php endforeach;?>
		    </tbody>
		</table>
	<?php endif; ?>
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
	        			<td><?php echo htmlspecialchars($land_building->owner_name[$i])?></td>
	        			<td><?php echo htmlspecialchars($land_building->owner_address[$i])?></td>
	        			<td><?php echo htmlspecialchars($land_building->owner_contacts[$i])?></td>
	        			<td><?php echo htmlspecialchars($land_building->plot_no[$i])?></td>
	        			<td><?php echo htmlspecialchars($land_building->house_no[$i])?></td>
	        			<td><?php echo htmlspecialchars($land_building->tole[$i])?></td>
	        			<td><?php echo $land_building->district[$i] ? $districts[ $land_building->district[$i] ] : ''?></td>
	        			<td><?php echo htmlspecialchars($land_building->vdc[$i])?></td>
	        			<td><?php echo htmlspecialchars($land_building->ward_no[$i])?></td>
	        			<td><?php echo htmlspecialchars($land_building->storey_no[$i])?></td>
	        			<td><?php echo $land_building->category[$i] ? _OBJ_FIRE_FIRE_item_building_category_dropdown()[ $land_building->category[$i] ] : ''?></td>
	        			<td><?php echo htmlspecialchars($land_building->used_for[$i])?></td>
	        		</tr>
	        <?php
	        	endfor;
	        endif;?>
	    </tbody>
	</table>
</div>
