<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Policy: Details - Sales Staff Card
 */
?>
<div class="box box-bordered box-default">
    <div class="box-header with-border">
        <h3 class="no-margin">Sales Staff</h3>
    </div>
    <?php
    $sales_staff_profile = $record->sales_staff_profile ? json_decode($record->sales_staff_profile) : NULL;
    ?>
    <div class="box-body">
        <table class="table table-condensed no-margin no-border">
            <tbody>
                <tr>
                    <td class="text-bold">Username</td>
                    <td><?php echo $record->sales_staff_username?></td>
                </tr>
                <tr>
                    <td class="text-bold">Name</td>
                    <td><?php echo $sales_staff_profile->name ?? '';?></td>
                </tr>
                <tr>
                    <td class="text-bold">Designation</td>
                    <td><?php echo $sales_staff_profile->designation ?? '';?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>