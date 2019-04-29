<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Chart of Account:  Single Row Widget
*/

// ------------------------------------------------------------------------------

$group_path_str = ac_account_group_path_formatted($record->acg_path);
$selectable_path_str = ac_account_group_path_formatted($record->acg_path, $record->name, '');

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
			['ref' => '_text-ref-account', 'val' => htmlentities($selectable_path_str, ENT_QUOTES)]
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