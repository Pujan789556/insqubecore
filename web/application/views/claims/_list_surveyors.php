<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Claims:  Data List Rows - Surveyors List
*/

/**
 * Load Rows from View
 */
$total_surveyor_fee = 0.00;
$total_other_fee 	= 0.00;
$total_vat 	= 0.00;
$total_tds 	= 0.00;
$gross_total = 0.00;
$grand_total = 0.00;
foreach($records as $record):
	$total_surveyor_fee = bcadd($total_surveyor_fee, $record->surveyor_fee, IQB_AC_DECIMAL_PRECISION);
	$total_other_fee = bcadd($total_other_fee, $record->other_fee, IQB_AC_DECIMAL_PRECISION);
	$total_vat = bcadd($total_vat, $record->vat_amount ?? 0.00, IQB_AC_DECIMAL_PRECISION);
	$total_tds = bcadd($total_tds, $record->tds_amount ?? 0.00, IQB_AC_DECIMAL_PRECISION);

	$single_total = bcsub(
		ac_bcsum([$record->surveyor_fee, $record->other_fee, $record->vat_amount ?? 0.00], IQB_AC_DECIMAL_PRECISION),
		$record->tds_amount ?? 0.00,
		IQB_AC_DECIMAL_PRECISION
	);

	$single_gross_total = bcadd($record->surveyor_fee, $record->other_fee);

	$gross_total = bcadd($gross_total, $single_gross_total, IQB_AC_DECIMAL_PRECISION);
	$grand_total = bcadd($grand_total, $single_total, IQB_AC_DECIMAL_PRECISION);
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
		<td class="text-right"><?php echo number_format($record->other_fee, 2) ?></td>
		<td class="text-right"><?php echo number_format($single_gross_total, 2) ?></td>
		<td class="text-right"><?php echo number_format($record->vat_amount, 2) ?></td>
		<td class="text-right">(<?php echo number_format($record->tds_amount, 2) ?>)</td>
		<td class="text-right"><?php echo number_format($single_total, 2) ?></td>
	</tr>
<?php endforeach ?>
<tr>
	<th colspan="5">Total</th>
	<th class="text-right"><?php echo number_format($total_surveyor_fee, 2) ?></th>
	<th class="text-right"><?php echo number_format($total_other_fee, 2) ?></th>
	<th class="text-right"><?php echo number_format($gross_total, 2) ?></th>
	<th class="text-right"><?php echo number_format($total_vat, 2) ?></th>
	<th class="text-right">(<?php echo number_format($total_tds, 2) ?>)</th>
	<th class="text-right"><?php echo number_format($grand_total, 2) ?></th>
</tr>