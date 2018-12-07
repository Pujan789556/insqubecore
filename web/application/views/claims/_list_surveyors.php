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
foreach($records as $surveyor):
	$total_surveyor_fee = bcadd($total_surveyor_fee, $surveyor->surveyor_fee, IQB_AC_DECIMAL_PRECISION);
	$total_other_fee = bcadd($total_other_fee, $surveyor->other_fee, IQB_AC_DECIMAL_PRECISION);
	$total_vat = bcadd($total_vat, $surveyor->vat_amount ?? 0.00, IQB_AC_DECIMAL_PRECISION);
	$total_tds = bcadd($total_tds, $surveyor->tds_amount ?? 0.00, IQB_AC_DECIMAL_PRECISION);

	$single_gross_total = CLAIM__surveyor_gross_total_fee($surveyor);
	$single_net_total 	= CLAIM__surveyor_net_total_fee($surveyor);

	$gross_total = bcadd($gross_total, $single_gross_total, IQB_AC_DECIMAL_PRECISION);
	$grand_total = bcadd($grand_total, $single_net_total, IQB_AC_DECIMAL_PRECISION);
	?>
	<tr>
		<?php if( $this->dx_auth->is_admin() ): ?>
			<td><?php echo $surveyor->id ;?></td>
		<?php endif;?>
		<td><?php echo $surveyor->surveyor_name ?></td>
		<td><?php echo CLAIM__surveyor_type_dropdown(FALSE)[$surveyor->survey_type] ?></td>
		<td><?php echo $surveyor->assigned_date ?></td>
		<td><?php echo $surveyor->status ?></td>
		<td class="text-right"><?php echo number_format($surveyor->surveyor_fee, 2) ?></td>
		<td class="text-right"><?php echo number_format($surveyor->other_fee, 2) ?></td>
		<td class="text-right"><?php echo number_format($single_gross_total, 2) ?></td>
		<td class="text-right"><?php echo number_format($surveyor->vat_amount, 2) ?></td>
		<td class="text-right">(<?php echo number_format($surveyor->tds_amount, 2) ?>)</td>
		<td class="text-right"><?php echo number_format($single_net_total, 2) ?></td>
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