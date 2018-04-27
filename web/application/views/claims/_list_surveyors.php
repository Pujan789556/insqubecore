<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Claims:  Data List Rows - Surveyors List
*/

/**
 * Load Rows from View
 */
$total_surveyor_fee = 0.00;
foreach($records as $record):
	$total_surveyor_fee += $record->surveyor_fee;
	?>
	<tr>
		<?php if( $this->dx_auth->is_admin() ): ?>
			<td><?php echo $record->id ;?></td>
		<?php endif;?>
		<td><?php echo $record->surveyor_name ?></td>
		<td><?php echo CLAIM__surveyor_type_dropdown(FALSE)[$record->survey_type] ?></td>
		<td><?php echo $record->assigned_date ?></td>
		<td><?php echo $record->status ?></td>
		<td class="text-right"><?php echo number_format($record->surveyor_fee, 2) ?></td>
	</tr>
<?php endforeach; ?>
<tr>
	<th colspan="5">Total Surveyor Fee</th>
	<th class="text-right"><?php echo number_format($total_surveyor_fee, 2) ?></th>
</tr>