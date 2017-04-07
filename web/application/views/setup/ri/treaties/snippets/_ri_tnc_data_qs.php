<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Setup - RI - Treaty : RI Tax & Commission - Quota & Surplus - Data View
*/
?>
<table class="table table-striped table-hover table-condensed">
	<thead>
		<tr>
			<th>Title</th>
			<th>Quota</th>
			<th>1st Surplus</th>
			<th>2nd Surplus</th>
			<th>3rd Surplus</th>
		</tr>
	</thead>
	<tbody>
		<?php
		/**
		 * Tax & Commission Titles
		 */
		$tnc_col_prefix = [
			'qs_comm_ri' 	=> 'RI Commission(%)',
			'qs_tax_ri'		=> 'RI Tax (%)',
			'qs_comm_ib' 	=> 'Insurance Board Commission(%)',
			'qs_piop'		=> 'Portfolio In & Out Premium(%)',
			'qs_piol'		=> 'Portfolio In & Out Loss (%)',
			'qs_pio_ib_cp'			=> 'Portfolio In & Out IB Claim Provision (%)',
			'qs_profit_comm' 		=> 'Profit Commission (%)',
			'flag_qs_comm_scale' 	=> ['label' => 'Apply Commission Scale?', 'callback' => 'yes_no_text']
		];

		$tnc_col_postfix = ['quota','surplus_1', 'surplus_2', 'surplus_3'];

		foreach($tnc_col_prefix as $col_prefix => $heading):
		?>
			<tr>
				<td>
					<?php
					$callback = NULL;
					if(is_array($heading))
					{
						$label 		= $heading['label'];
						$callback 	= $heading['callback'] ?? NULL;
					}
					else
					{
						$label = $heading;
					}
					echo $label;
					?>
				</td>
				<?php foreach ($tnc_col_postfix as $col_postfix):?>
					<td>
						<?php
						$value = $record->{$col_prefix . '_' . $col_postfix};
						if($callback && function_exists($callback))
						{
							echo call_user_func($callback, $value);
						}
						else
						{
							echo $value;
						}
						?>
					</td>
				<?php endforeach?>
			</tr>
		<?php endforeach?>
	</tbody>
</table>