<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Fiscal Year:  Single Row
*/

// --------------------------------------------------------------

/**
 * Check if this is Current Fiscal Year
 */
$flag_current_fiscal_year = $this->current_fiscal_year->starts_at_en == $record->fy_starts_at && $this->current_fiscal_year->ends_at_en == $record->fy_ends_at;

$current_class = $flag_current_fiscal_year ? 'text-success text-bold' : '';
$current_title = $flag_current_fiscal_year ? 'Current Fiscal Year' : '';
?>
<tr class="searchable <?php echo $current_class;?>"
	title="<?php echo $current_title?>"
	<?php echo $flag_current_fiscal_year ? 'data-toggle="tooltip"' : ''?>
	data-id="<?php echo $record->id; ?>"
	id="_data-row-<?php echo $record->id;?>">
	<td><?php echo $record->id;?></td>
	<td><?php echo $record->fy_code_np;?></td>
	<td><?php echo $record->starts_at;?></td>
	<td><?php echo $record->ends_at;?></td>
	<td><?php echo fiscal_year_quarters_dropdown()[$record->quarter];?></td>
</tr>