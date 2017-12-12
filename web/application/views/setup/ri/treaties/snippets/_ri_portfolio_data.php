<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Setup - RI - Treaty : RI Portfolios - Data View
*/
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
			<th>Treaty Maximum Capacity</th>
			<th>Maximum Retention Amount</th>
			<th>Defnied Retention Amount</th>
			<th>Defined Retention Apply?</th>
			<th>QS Retention(%)</th>
			<th>QS Quota(%)</th>
			<th>Surplus 1st Line</th>
			<th>Surplus 2nd Line</th>
			<th>Surplus 3rd Line</th>
			<th>EOL Amount L1</th>
			<th>EOL Amount L2</th>
			<th>EOL Amount L3</th>
			<th>EOL Amount L4</th>
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
				<td><?php echo $portfolio->ac_basic ? ri_ac_basic_dropdown(FALSE)[$portfolio->ac_basic]: '-'?></td>
				<td><?php echo yes_no_text($portfolio->flag_claim_recover_from_ri, '-')?></td>
				<td><?php echo yes_no_text($portfolio->flag_comp_cession_apply, '-')?></td>
				<td><?php echo $portfolio->comp_cession_percent;?></td>
				<td><?php echo $portfolio->comp_cession_max_amt;?></td>
				<td><?php echo $portfolio->treaty_max_capacity_amt;?></td>
				<td><?php echo $portfolio->qs_max_ret_amt;?></td>
				<td><?php echo $portfolio->qs_def_ret_amt;?></td>
				<td><?php echo $portfolio->flag_qs_def_ret_apply ? yes_no_text(false)[$portfolio->flag_qs_def_ret_apply] : '-';?></td>
				<td><?php echo $portfolio->qs_retention_percent;?></td>
				<td><?php echo $portfolio->qs_quota_percent;?></td>
				<td><?php echo $portfolio->qs_lines_1;?></td>
				<td><?php echo $portfolio->qs_lines_2;?></td>
				<td><?php echo $portfolio->qs_lines_3;?></td>
				<td><?php echo $portfolio->eol_layer_amount_1;?></td>
				<td><?php echo $portfolio->eol_layer_amount_2;?></td>
				<td><?php echo $portfolio->eol_layer_amount_3;?></td>
				<td><?php echo $portfolio->eol_layer_amount_4;?></td>
			</tr>
		<?php endforeach?>
	</tbody>
</table>