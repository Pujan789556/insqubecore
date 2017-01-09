<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Object:  Single Row
*/
$select_text = _PO_select_text($record);
$select_json = [
	'fields' => [
		['id' => 'object-id', 'val' => $record->id],
		['id' => 'object-text', 'val' => $select_text]
	],
	'html' => [
		['id' => '_text-ref-object', 'val' => $select_text]
	]
];

?>
<tr class="selectable pointer"
	data-selectable='<?php echo json_encode($select_json)?>'
	title="Select this object."
	onclick="__do_select(this)"
	id="_data-row-object-<?php echo $record->id;?>">

	<?php if( $this->dx_auth->is_admin() ): ?>
		<td><?php echo $record->id;?></td>
	<?php endif?>
	<td><?php echo $record->portfolio_name;?></td>
	<td><?php echo $record->customer_name;?></td>
	<?php echo _PO_row_snippet($record, $_flag__show_widget_row);?>
</tr>