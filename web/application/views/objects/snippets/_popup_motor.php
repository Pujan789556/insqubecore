<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Object Snippet: Motor Popover
*/

// Let's load helper if not already loaded
$this->load->helper('ph_motor');

$attributes = $record->attributes ? json_decode($record->attributes, TRUE) : NULL;
$v_rules = _OBJ_MOTOR_validation_rules($record->portfolio_id, TRUE);
$formatted_attriutes = [];
if($attributes)
{
	foreach($v_rules as $field)
	{
		$label = $field['label'];
		$key 	= $field['_key'];
		$value = $attributes[$key] ?? '-';
		if($field['_type'] == 'dropdown')
		{
			$value = $value ? ( $field['_data'][$value] ?? htmlspecialchars($value) ) : '-';
		}
		$formatted_attriutes[$label] = $value;
	}
}
?>
<?php if($formatted_attriutes):?>
	<div class="box no-border">
        <div class="box-body no-padding">
        	<table class="table table-responsive table-condensed table-hover">
				<tr><td width="50%"><strong>Portfolio</strong></td><td><?php echo $record->portfolio_name_en;?></td></tr>
				<tr><td><strong>Customer</strong></td><td><?php echo $record->customer_name_en;?></td></tr>
				<tr><td><strong>Sum Insured Amount</strong></td><td>Rs. <?php echo $record->amt_sum_insured;?></td></tr>
				<?php foreach($formatted_attriutes as $key=>$value):?>
					<tr>
						<td><strong><?php echo $key?></strong></td>
						<td><?php echo $value?></td>
					</tr>
				<?php endforeach;?>
			</table>
        </div>
    </div>

<?php endif;?>