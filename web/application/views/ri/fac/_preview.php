<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* RI - FAC Distribution - Preview
*/
?>
<div class="box box-solid box-bordered">
    <div class="box-header with-border">
        <h4 class="box-title">FAC Overview</h4>
    </div>
    <table class="table table-responsive table-condensed">
        <tbody>
            <tr>
                <th>FAC SI (Rs.)</th>
                <td class="text-right"><?php echo number_format($record->si_treaty_fac, 2);?></td>
            </tr>
            <tr>
                <th>FAC Premium (Rs.)</th>
                <td class="text-right"><?php echo number_format($record->premium_treaty_fac, 2);?></td>
            </tr>
        </tbody>
    </table>
</div>

<?php if($fac_distribution): ?>
	<table class="table table-condensed table-bordered table-responsive">
		<thead>
			<tr>
				<th>SN</th>
				<th>Company</th>
				<th>Distribution(%)</th>
				<th>SI</th>
				<th>Premium</th>
				<th>Commission(%)</th>
				<th>Commission (Rs)</th>
				<th>RI Tax (%)</th>
				<th>RI Tax (Rs)</th>
				<th>IB Tax (%)</th>
				<th>IB Tax (Rs)</th>
			</tr>
		</thead>
		<tbody>
			<?php
			$sn = 1;
			$fac_percent_total 				= 0.00;
			$fac_si_amount_total 			= 0.00;
			$fac_premium_amount_total 		= 0.00;
			$fac_commission_amount_total 	= 0.00;
			$fac_ri_tax_amount_total 		= 0.00;
			$fac_ib_tax_amount_total 		= 0.00;

			foreach($fac_distribution as $distrib):
				$fac_percent_total 				+= $distrib->fac_percent;
				$fac_si_amount_total 			+= $distrib->fac_si_amount;
				$fac_premium_amount_total 		+= $distrib->fac_premium_amount;
				$fac_commission_amount_total 	+= $distrib->fac_commission_amount;
				$fac_ri_tax_amount_total 		+= $distrib->fac_ri_tax_amount;
				$fac_ib_tax_amount_total 		+= $distrib->fac_ib_tax_amount;
				?>
				<tr>
					<td><?php echo $sn++;?>.</td>
					<td>
						<a href="<?php echo site_url('companies/details/' . $distrib->company_id)?>" target="_blank">
							<?php echo $distrib->company_name?>
						</a>
					</td>
					<td class="text-right"><?php echo $distrib->fac_percent?></td>
					<td class="text-right"><?php echo number_format($distrib->fac_si_amount, 2);?></td>
					<td class="text-right"><?php echo number_format($distrib->fac_premium_amount, 2);?></td>
					<td class="text-right"><?php echo $distrib->fac_commission_percent?></td>
					<td class="text-right"><?php echo number_format($distrib->fac_commission_amount, 3);?></td>
					<td class="text-right"><?php echo $distrib->fac_ri_tax_percent?></td>
					<td class="text-right"><?php echo number_format($distrib->fac_ri_tax_amount, 3);?></td>
					<td class="text-right"><?php echo $distrib->fac_ib_tax_percent?></td>
					<td class="text-right"><?php echo number_format($distrib->fac_ib_tax_amount, 3);?></td>
				</tr>
			<?php endforeach?>

		</tbody>
		<tfoot>
			<tr>
				<th colspan="2" class="text-bold text-right">Total(%):</th>
				<th class="text-right"><?php echo number_format($fac_percent_total, 2);?></th>
				<th class="text-right"><?php echo number_format($fac_si_amount_total, 2);?></th>
				<th class="text-right"><?php echo number_format($fac_premium_amount_total, 2);?></th>
				<th>&nbsp;</th>
				<th class="text-right"><?php echo number_format($fac_commission_amount_total, 3);?></th>
				<th>&nbsp;</th>
				<th class="text-right"><?php echo number_format($fac_ri_tax_amount_total, 3);?></th>
				<th>&nbsp;</th>
				<th class="text-right"><?php echo number_format($fac_ib_tax_amount_total, 3);?></th>
			</tr>
		</tfoot>
	</table>
<?php else: ?>
	<p class="text-muted small">No distribution data found!</p>
<?php endif ?>