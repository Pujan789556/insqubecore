<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Setup - RI - Treaty : RI Portfolios - Data View
*/
$treaty_type_id =  (int)$portfolios[0]->treaty_type_id;
?>
<table class="table table-striped table-hover table-condensed">
	<thead>
		<tr>
			<th>SN</th>
			<th>Portfolio</th>
			<th>Claim Recover From RI</th>
			<th>Treaty Maximum Capacity</th>
			<th>Commission (%)</th>
			<th>IB Tax (%)</th>
			<th>RI Tax (%)</th>
			<th>Maximum Retention Amount</th>

			<?php if( in_array($treaty_type_id, [IQB_RI_TREATY_TYPE_QT, IQB_RI_TREATY_TYPE_QS]) ): ?>
				<th>QS Retention(%)</th>
				<th>QS Quota(%)</th>
			<?php endif ?>

			<?php if( in_array($treaty_type_id, [IQB_RI_TREATY_TYPE_SP, IQB_RI_TREATY_TYPE_QS]) ): ?>
				<th>Surplus 1st Line</th>
				<th>Surplus 2nd Line</th>
				<th>Surplus 3rd Line</th>
			<?php endif ?>

		</tr>
	</thead>
	<tbody>
		<?php
		$portfolio_sn = 1;
		foreach ($portfolios as $portfolio):?>
			<tr>
				<td><?php echo $portfolio_sn++;?></td>
				<td>
					<?php echo $portfolio->protfolio_parent_code  . ' - ' . $portfolio->portfolio_name_en . "({$portfolio->portfolio_code})"?>
				</td>
				<td><?php echo yes_no_text($portfolio->flag_claim_recover_from_ri, '-')?></td>
				<td><?php echo $portfolio->treaty_max_capacity_amt;?></td>
				<td><?php echo $portfolio->commission_percent;?></td>
				<td><?php echo $portfolio->ib_tax_percent;?></td>
				<td><?php echo $portfolio->ri_tax_percent;?></td>
				<td><?php echo $portfolio->qs_max_ret_amt;?></td>

				<?php if( in_array($treaty_type_id, [IQB_RI_TREATY_TYPE_QT, IQB_RI_TREATY_TYPE_QS]) ): ?>
					<td><?php echo $portfolio->qs_retention_percent;?></td>
					<td><?php echo $portfolio->qs_quota_percent;?></td>
				<?php endif ?>

				<?php if( in_array($treaty_type_id, [IQB_RI_TREATY_TYPE_SP, IQB_RI_TREATY_TYPE_QS]) ): ?>
					<td><?php echo $portfolio->qs_lines_1;?></td>
					<td><?php echo $portfolio->qs_lines_2;?></td>
					<td><?php echo $portfolio->qs_lines_3;?></td>
				<?php endif ?>
			</tr>
		<?php endforeach?>
	</tbody>
</table>