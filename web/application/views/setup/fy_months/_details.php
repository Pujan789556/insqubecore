<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<?php if($records): ?>
    <table class="table table-condensed table-bordered table-responsive">
        <thead>
            <tr>
                <th>ID</th>
                <th>Month</th>
                <th>Start</th>
                <th>End</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($records as $single): ?>
                <tr>
                    <td><?php echo $single->id ?></td>
                    <td><?php echo $months[$single->month_id]; ?></td>
                    <td><?php echo $single->starts_at ?></td>
                    <td><?php echo $single->ends_at ?></td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
<?php else: ?>
    <p class="text-muted small">No data found!</p>
<?php endif ?>