<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Surveyors:  Single Row Widget
*/

// ------------------------------------------------------------------------------

/**
 * Extract Widget Data
 */
$widget_data 	= explode(':', $widget_reference); // target-row-id:widget_type
$target_row_id 	= $widget_data[0];
$widget_type 	= $widget_data[1];


/**
 * Build Seletable JSON based on widget type
 */
$select_json = [];
if($widget_type === 'party')
{
	$select_json = [
		'fields' => [
			['ref' => 'party_id', 'val' => $record->id],
		],
		'html' => [
			['ref' => '_text-ref-party', 'val' => $record->name ]
		]
	];
}
?>
<tr class="selectable pointer"
	data-selectable='<?php echo json_encode($select_json)?>'
	data-target-rowid='<?php echo $target_row_id?>'
	title="Select this party."
	data-toggle="tooltip"
	onclick="__do_select(this)"
	id="_data-row-<?php echo $record->id;?>">
	<?php if( $this->dx_auth->is_admin() ): ?>
		<td><?php echo $record->id;?></td>
	<?php endif?>
	<td>
		<a href="<?php echo site_url('surveyors/details/' . $record->id);?>"
						title="View agent details.">
						<?php echo $record->name;?></a>
	</td>
	<td><?php echo $record->type == '1' ? 'Individual' : 'Company';?></td>
	<td>
		<?php
		if($record->flag_vat_registered)
		{
			$flag_str = '<i class="fa fa-check text-green" title="Yes" data-toggle="tooltip"></i>';
		}
		else
		{
			$flag_str = '<i class="fa fa-minus" title="No" data-toggle="tooltip"></i>';
		}
		echo $flag_str;
		?>
	</td>
	<td>
		<?php
		if($record->active)
		{
			$active_str = '<i class="fa fa-circle text-green" title="Active" data-toggle="tooltip"></i>';
		}
		else
		{
			$active_str = '<i class="fa fa-circle-thin" title="Not Active" data-toggle="tooltip"></i>';
		}
		echo $active_str;
		?>
	</td>
</tr>