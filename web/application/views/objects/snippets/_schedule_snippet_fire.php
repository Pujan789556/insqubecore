<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Object Snippet: Fire Popover
*/
$districts 	= district_dropdown();
$attributes = $record->attributes ? json_decode($record->attributes) : NULL;
?>
<table class="table table-bordered table-condensed no-margin" >
    <thead>
        <tr>
            <td>भवन/सम्पत्ति धनीको नाम</td>
            <td>ठेगाना</td>
            <td>सम्पर्क नं</td>
            <td>कित्ता नं.</td>
            <td>घर नं.</td>
            <td>टोल</td>
            <td>जिल्ला</td>
            <td>गा.वि.स./नगरपालिका</td>
            <td>वडा नं.</td>
            <td>तला संख्या</td>
        </tr>
    </thead>
    <?php
    $land_building	= $attributes->land_building ?? NULL;
    $item_count   	= count( $land_building->owner_name ?? [] );
    ?>
    <tbody>
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
        		</tr>
        <?php
        	endfor;
        endif;?>
    </tbody>
</table>
