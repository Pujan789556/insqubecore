<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Customers:  Single Row
*/
$attributes = $record->attributes ? json_decode($record->attributes) : NULL;
$select_text = implode(', ', [$attributes->make, $attributes->model, $attributes->reg_no, $attributes->engine_no, $attributes->chasis_no]);
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
	<?php echo _PO_row_snippet($record);?>
</tr>