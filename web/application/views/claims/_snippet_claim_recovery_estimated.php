<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Claim: Details - Snippet - Claim Recovery
*/
$claim_ri_data = CLAIM__ri_breakdown_estimated($record, TRUE);
?>
<div class="box box-bordered box-default">
    <div class="box-header with-border">
        <h4 class="box-title">Claim Recovery - Estimated</h4>
    </div>
    <table class="table table-responsive table-condensed">
        <tbody>
            <?php foreach($claim_ri_data as $label => $value): ?>
                <tr>
                    <th><?php echo $label ?> (Rs.)</th>
                    <td><?php echo $value ? number_format($value, 2) : '';?></td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</div>