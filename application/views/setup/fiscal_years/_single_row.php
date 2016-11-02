<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Fiscal Year:  Single Row
*/

// --------------------------------------------------------------

/**
 * Check if this is current Fiscal Year
 */
$flag_current_fiscal_year = $this->current_fiscal_year->starts_at_en == $record->starts_at_en && $this->current_fiscal_year->ends_at_en == $record->ends_at_en;

$current_class = $flag_current_fiscal_year ? 'text-success text-bold' : '';
$current_title = $flag_current_fiscal_year ? 'Current Fiscal Year' : '';
?>
<tr class="searchable <?php echo $current_class;?>"
	title="<?php echo $current_title?>"
	<?php echo $flag_current_fiscal_year ? 'data-toggle="tooltip"' : ''?>
	data-id="<?php echo $record->id; ?>"
	id="_data-row-<?php echo $record->id;?>">
	<td><?php echo $record->id;?></td>
	<td><?php echo $record->code_np;?></td>
	<td><?php echo $record->code_en;?></td>

	<td><?php echo str_to_nepdate($record->starts_at_np);?></td>
	<td><?php echo str_to_nepdate($record->ends_at_np);?></td>

	<td><?php echo $record->starts_at_en;?></td>
	<td><?php echo $record->ends_at_en;?></td>

</tr>