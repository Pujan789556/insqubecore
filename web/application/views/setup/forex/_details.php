<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Forex: Details View
*/
// echo '<pre>'; print_r($record); echo '</pre>';
$rate_columns = [
	'BaseCurrency'  	=> 'Base Currency',
    'BaseValue'			=> 'Base Value',
	'TargetCurrency'	=> 'Target Currency',
    'TargetBuy'     	=> 'Target Buy',
    'TargetSell'    	=> 'Target Sell'
];
$exchange_rates = json_decode($record->exchange_rates ?? []);
?>
<div class="box box-bordered box-default">
    <div class="box-header with-border">
        <h4 class="box-title">Basic Information</h4>
    </div>
    <table class="table table-responsive table-condensed">
        <tbody>
            <tr>
                <th width="20%">Exchange Date</th>
                <td><?php echo $record->exchange_date;?></td>
            </tr>
        </tbody>
    </table>
</div>
<div class="box box-bordered box-default">
    <div class="box-header with-border">
        <h4 class="box-title">Forex Details</h4>
    </div>
    <table class="table table-responsive table-stripped table-bordered table-hover">
        <thead>
            <tr>
            	<?php foreach($rate_columns as $column => $label): ?>
                	<th><?php echo $label ?></th>
            	<?php endforeach ?>
            </tr>
        </thead>
        <tbody>
        	<?php foreach($exchange_rates as $single): ?>
        		<tr>
		            <?php foreach($rate_columns as $column => $label): ?>
	                    <td class="text-right"><?php echo $single->{$column} ?? '-' ?></td>
		            <?php endforeach ?>
	            </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</div>