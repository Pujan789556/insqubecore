<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Setup - RI - Pool Treaty : Pool Portfolios - Data View
*/
?>
<table class="table table-striped table-hover table-condensed">
	<thead>
		<tr>
			<th>SN</th>
			<th>Portfolio</th>
			<th>Retention(%)</th>
			<th>Commission(%)</th>
			<th>IB Tax(%)</th>
			<th>RI Tax(%)</th>
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
				<td><?php echo $portfolio->retention;?></td>
				<td><?php echo $portfolio->commission;?></td>
				<td><?php echo $portfolio->ib_tax;?></td>
				<td><?php echo $portfolio->ri_tax;?></td>
			</tr>
		<?php endforeach?>
	</tbody>
</table>