<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Claim: Details - Snippet - Claim Recovery
*/

$exception_message  = '';
$claim_ri_data      = [];
try {
    $claim_ri_data = CLAIM__ri_breakdown($record, TRUE);
} catch (Exception $e) {
    $exception_message = $e->getMessage();
}
?>
<div class="box box-bordered box-default">
    <div class="box-header with-border">
        <h4 class="box-title">Claim Recovery - Settled</h4>
    </div>
    <table class="table table-responsive table-condensed">
        <tbody>
            <?php
            if(!$exception_message):
                foreach($claim_ri_data as $label => $value): ?>
                <tr>
                    <th><?php echo $label ?> (Rs.)</th>
                    <td><?php echo $value ? number_format($value, 2) : '';?></td>
                </tr>
            <?php endforeach;
            else:?>
                <tr><td><?php echo $exception_message; ?></td></tr>
            <?php endif ?>
        </tbody>
    </table>
</div>