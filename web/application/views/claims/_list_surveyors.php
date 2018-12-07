<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Claims:  Data List Rows - Surveyors List
*/

$total_surveyor_fee = 0.00;
$total_other_fee 	= 0.00;
$total_vat 	= 0.00;
$total_tds 	= 0.00;
?>
<table class="table table-hover">
	<thead>
	    <tr>
	        <?php if( $this->dx_auth->is_admin() ): ?>
	            <th>ID</th>
	        <?php endif;?>
	        <th>Surveyor</th>
	        <th>Type</th>
	        <th>Assigned Date</th>
	        <th>Status</th>
	        <th class="text-right">Professional Fee (Rs.)</th>
	        <th class="text-right">Other Fee (Rs.)</th>
	        <th class="text-right">Gross Total Fee (Rs.)</th>
	        <th class="text-right">VAT (Rs.)</th>
	        <th class="text-right">TDS (Rs.)</th>
	        <th class="text-right">Net Total (Rs.)</th>

	    </tr>
	</thead>
	<tbody id="search-result-claim-surveyors">
	    <?php foreach($records as $surveyor):

			$total_surveyor_fee = bcadd($total_surveyor_fee, $surveyor->surveyor_fee, IQB_AC_DECIMAL_PRECISION);
			$total_other_fee 	= bcadd($total_other_fee, $surveyor->other_fee, IQB_AC_DECIMAL_PRECISION);
			$total_tds 			= bcadd($total_tds, $surveyor->tds_amount ?? 0.00, IQB_AC_DECIMAL_PRECISION);

			$single_gross_total = CLAIM__surveyor_gross_total_fee($surveyor);
			$single_net_total 	= CLAIM__surveyor_net_total_fee($surveyor);
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
			<th class="text-right"><?php echo number_format(CLAIM__surveyor_gross_total_fee_by_claim($record->id), 2) ?></th>
			<th class="text-right"><?php echo number_format(CLAIM__surveyor_vat_total_by_claim($record->id), 2) ?></th>
			<th class="text-right">(<?php echo number_format($total_tds, 2) ?>)</th>
			<th class="text-right"><?php echo number_format(CLAIM__surveyor_net_total_fee_by_claim($record->id), 2) ?></th>
		</tr>
	</tbody>
</table>