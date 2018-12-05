<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Policy: Details - Policy Premium Overview Card - MARINE
*/
$cost_calculation_table = $endorsement_record->cost_calculation_table ? json_decode($endorsement_record->cost_calculation_table) : NULL;
$total_premium = (float)$endorsement_record->amt_basic_premium + (float)$endorsement_record->amt_pool_premium;
$grand_total = $total_premium + $endorsement_record->amt_stamp_duty + $endorsement_record->amt_vat;
?>
<div class="box-body">
	<table class="table no-margin table-bordered">
		<tbody id="_premium-details">
			<?php if($cost_calculation_table): ?>

				<?php foreach($cost_calculation_table as $row):?>
					<tr>
						<th><?php echo $row->label ?></th>
						<td class="text-right"><?php echo number_format( (float)$row->value, 2);?></td>
					</tr>
				<?php endforeach ?>
				<tr>
				    <td class="no-padding" colspan="2">
				        <?php
                        /**
                         * Load Cost Calculation Table - Summary
                         */
                        $this->load->view('endorsements/snippets/premium/_summary_table',
                            ['lang' => 'en', 'endorsement_record' => $endorsement_record]
                        );
                        ?>
				    </td>
				</tr>
			<?php else: ?>
				<tr><td class="text-muted text-center">No Premium Information Found!</td></tr>
			<?php endif ?>
		</tbody>
	</table>
</div>