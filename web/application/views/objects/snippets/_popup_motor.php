<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Object Snippet: Motor Popover
*/
$attributes = $record->attributes ? json_decode($record->attributes, TRUE) : NULL;
$v_rules = _PO_MOTOR_validation_rules($record->portfolio_id, TRUE);
$formatted_attriutes = [];
if($attributes)
{
	foreach($v_rules as $field)
	{
		$label = $field['label'];
		$key 	= $field['_key'];
		$value = $attributes[$key] ?? '-';
		if($field['_type'] == 'dropdown')
		{
			$value = $value ? ( $field['_data'][$value] ?? $value ) : '-';
		}
		$formatted_attriutes[$label] = $value;
	}
}
?>
<?php if($formatted_attriutes):?>
	<table class="table table-responsive no-margin no-border">
		<?php foreach($formatted_attriutes as $key=>$value):?>
			<tr>
				<td><strong><?php echo $key?></strong></td>
				<td><?php echo $value?></td>
			</tr>
		<?php endforeach;?>
	</table>
<?php endif;?>