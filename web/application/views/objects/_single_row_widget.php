<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Object:  Single Row
*/
$select_text = _OBJ_select_text($record);
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
	data-selectable='<?php echo htmlspecialchars(json_encode($select_json), ENT_QUOTES, 'UTF-8')?>'
	title="Select this object."
	onclick="__do_select(this)"
	id="_data-row-object-<?php echo $record->id;?>">

	<?php if( $this->dx_auth->is_admin() ): ?>
		<td><?php echo $record->id;?></td>
	<?php endif?>
	<td><?php echo $record->portfolio_name_en;?><br/><em class="text-bold" data-toggle="tooltip" title="Sum Insured Amount">RS. <?php echo $record->amt_sum_insured;?></em></td>
	<td><?php echo $record->customer_name_en;?></td>
	<?php echo _OBJ_row_snippet($record, $_flag__show_widget_row);?>
</tr>