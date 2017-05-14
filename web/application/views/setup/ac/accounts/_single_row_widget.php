<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Chart of Account:  Single Row Widget
*/

// ------------------------------------------------------------------------------

$group_path = [];
if( count($record->acg_path) > 2 )
{
	array_shift($record->acg_path); // Remove "Chart of Account"
	foreach($record->acg_path as $path)
	{
		$group_path[]=$path->name;
	}
}
else
{
	$group_path[] = $record->group_name;
}
$group_path_selectable = $group_path;
$group_path_selectable[] = '<strong>' . $record->name . '</strong>';

$group_path_str = implode('<i class="fa fa-angle-right text-bold text-red" style="margin:0 5px;"></i>', $group_path);
$selectable_path_str = implode('<i class="fa fa-angle-right text-bold text-red" style="margin:0 5px;"></i>', $group_path_selectable);

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
if($widget_type === 'account')
{
	$select_json = [
		'fields' => [
			['ref' => 'account_id', 'val' => $record->id],
		],
		'html' => [
			['ref' => '_text-ref-account', 'val' => $selectable_path_str]
		]
	];
}

?>
<tr class="selectable pointer"
	data-selectable='<?php echo json_encode($select_json)?>'
	data-target-rowid='<?php echo $target_row_id?>'
	title="Select this account."
	data-toggle="tooltip"
	onclick="__do_select(this)"
	id="_data-row-<?php echo $record->id;?>">
	<?php if( $this->dx_auth->is_admin() ): ?>
		<td><?php echo $record->id;?></td>
	<?php endif?>
	<td><?php echo $group_path_str; ?> </td>
	<td><?php echo $record->name;?></td>
	<td><?php echo  active_inactive_text($record->active);?></td>
</tr>