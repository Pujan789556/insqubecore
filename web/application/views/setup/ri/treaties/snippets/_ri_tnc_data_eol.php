<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Setup - RI - Treaty : RI Tax & Commission - EOL - Data View
*/
?>
<table class="table table-striped table-hover table-condensed">
	<thead>
		<tr>
			<th>Title</th>
			<th>Layer 1</th>
			<th>Layer 2</th>
			<th>Layer 3</th>
			<th>Layer 4</th>
		</tr>
	</thead>
	<tbody>
		<?php
		/**
		 * Tax & Commission Titles
		 */
		$tnc_col_prefix = [
			'eol_min_n_deposit_amt' => 'Minimum & Deposit Premium',
			'eol_premium_mode' 		=> 'Premium Mode',
			'eol_min_rate' 			=> 'Minimum Rate',
			'eol_max_rate' 			=> 'Maximum Rate',
			'eol_fixed_rate' 		=> 'Fixed Rate',
			'eol_loading_factor' 	=> 'Loading Factor',
			'eol_tax_ri' 			=> 'RI Tax(%)',
			'eol_comm_ib' 			=> 'IB Commission(%)',
			'flag_eol_rr' 			=> 'Reinstatement Required'
		];

		$tnc_col_postfix = ['l1','l2', 'l3', 'l4'];

		foreach($tnc_col_prefix as $col_prefix => $heading):
		?>
			<tr>
				<td><?php echo $heading;?></td>
				<?php foreach ($tnc_col_postfix as $col_postfix):?>
					<td><?php echo $record->{$col_prefix . '_' . $col_postfix}?></td>
				<?php endforeach?>
			</tr>
		<?php endforeach?>
	</tbody>
</table>