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
			<th>A/C Basic</th>
			<th>Claim Recover From RI</th>
			<th>Compulsory Cession Apply?</th>
			<th>Compulsory Cession(%)</th>
			<th>Compulsory Cession(Max Amount)</th>
			<th>Compulsory Cession RI Commission(%)</th>
			<th>Compulsory Cession RI Tax(%)</th>
			<th>Compulsory Cession IB Tax(%)</th>
			<th>Treaty Maximum Capacity</th>
			<th>Maximum Retention Amount</th>
			<th>Defnied Retention Amount</th>
			<th>Defined Retention Apply?</th>

			<?php if( in_array($treaty_type_id, [IQB_RI_TREATY_TYPE_QT, IQB_RI_TREATY_TYPE_QS]) ): ?>
				<th>QS Retention(%)</th>
				<th>QS Quota(%)</th>
			<?php endif ?>

			<?php if( in_array($treaty_type_id, [IQB_RI_TREATY_TYPE_SP, IQB_RI_TREATY_TYPE_QS]) ): ?>
				<th>Surplus 1st Line</th>
				<th>Surplus 2nd Line</th>
				<th>Surplus 3rd Line</th>
			<?php endif ?>

			<?php if($treaty_type_id == IQB_RI_TREATY_TYPE_EOL): ?>
				<th>EOL Amount L1</th>
				<th>EOL Amount L2</th>
				<th>EOL Amount L3</th>
				<th>EOL Amount L4</th>
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
				<td><?php echo $portfolio->ac_basic ? RI__ac_basic_dropdown(FALSE)[$portfolio->ac_basic]: '-'?></td>
				<td><?php echo yes_no_text($portfolio->flag_claim_recover_from_ri, '-')?></td>
				<td><?php echo yes_no_text($portfolio->flag_comp_cession_apply, '-')?></td>
				<td><?php echo $portfolio->comp_cession_percent;?></td>
				<td><?php echo $portfolio->comp_cession_max_amt;?></td>
				<td><?php echo $portfolio->comp_cession_comm_ri;?></td>
				<td><?php echo $portfolio->comp_cession_tax_ri;?></td>
				<td><?php echo $portfolio->comp_cession_tax_ib;?></td>

				<td><?php echo $portfolio->treaty_max_capacity_amt;?></td>
				<td><?php echo $portfolio->qs_max_ret_amt;?></td>
				<td><?php echo $portfolio->qs_def_ret_amt;?></td>
				<td><?php echo $portfolio->flag_qs_def_ret_apply ? yes_no_text(false)[$portfolio->flag_qs_def_ret_apply] : '-';?></td>

				<?php if( in_array($treaty_type_id, [IQB_RI_TREATY_TYPE_QT, IQB_RI_TREATY_TYPE_QS]) ): ?>
					<td><?php echo $portfolio->qs_retention_percent;?></td>
					<td><?php echo $portfolio->qs_quota_percent;?></td>
				<?php endif ?>

				<?php if( in_array($treaty_type_id, [IQB_RI_TREATY_TYPE_SP, IQB_RI_TREATY_TYPE_QS]) ): ?>
					<td><?php echo $portfolio->qs_lines_1;?></td>
					<td><?php echo $portfolio->qs_lines_2;?></td>
					<td><?php echo $portfolio->qs_lines_3;?></td>
				<?php endif ?>

				<?php if($treaty_type_id == IQB_RI_TREATY_TYPE_EOL): ?>
					<td><?php echo $portfolio->eol_layer_amount_1;?></td>
					<td><?php echo $portfolio->eol_layer_amount_2;?></td>
					<td><?php echo $portfolio->eol_layer_amount_3;?></td>
					<td><?php echo $portfolio->eol_layer_amount_4;?></td>
				<?php endif ?>
			</tr>
		<?php endforeach?>
	</tbody>
</table>