<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Policy: Details - Policy Premium Overview Card - MISCELLANEOUS - TRAVEL MEDICAL INSURANCE(TMI)
*/
$cost_calculation_table = json_decode($endorsement_record->cost_calculation_table ?? NULL);
$total_premium 	= (float)$endorsement_record->amt_basic_premium + (float)$endorsement_record->amt_pool_premium;
$grand_total 	= $total_premium + $endorsement_record->amt_stamp_duty + $endorsement_record->amt_vat;
?>
<div class="box-body">
	<table class="table no-margin table-bordered">
		<tbody id="_premium-details">
			<tr>
			    <td class="no-padding" colspan="2">
			        <table class="table no-margin table-bordered table-condensed">
			            <tr>
			                <td width="80%" class="text-right"><strong>Gross Premium</strong></td>
			                <td class="text-right"><strong><?php echo number_format($total_premium, 2)?></strong></td>
			            </tr>
			            <tr>
			                <td class="text-right"><strong>Stamp Duty</strong></td>
			                <td class="text-right"><strong><?php echo number_format( (float)$endorsement_record->amt_stamp_duty, 2)?></strong></td>
			            </tr>
			            <tr>
			                <td class="text-right"><strong>VAT</strong></td>
			                <td class="text-right"><strong><?php echo number_format( (float)$endorsement_record->amt_vat, 2);?></strong></td>
			            </tr>
			            <tr>
			                <td class="text-right"><strong>Total Premium</strong></td>
			                <td class="text-right"><strong><?php echo number_format( $grand_total , 2);?></strong></td>
			            </tr>
			        </table>
			    </td>
			</tr>
		</tbody>
	</table>
</div>