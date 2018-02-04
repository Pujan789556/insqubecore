<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* RI Transactions: Details View
*/
$si_columns = [
    'si_gross'              => 'SI Gross',
    'si_comp_cession'       => 'SI Compulsory Cession',
    'si_treaty_total'       => 'SI Treaty Total',
    'si_treaty_retaintion'  => 'SI Retaintion',
    'si_treaty_quota'       => 'SI Quota',
    'si_treaty_1st_surplus' => 'SI 1st Surplus',
    'si_treaty_2nd_surplus' => 'SI 2nd Surplus',
    'si_treaty_3rd_surplus' => 'SI 3rd Surplus',
    'si_treaty_fac'         => 'SI FAC'
];
$premium_columns = [
    'premium_gross'                 => 'Premium Gross',
    'premium_pool'                  => 'Premium Pool',
    'premium_net'                   => 'Premium Net',
    'premium_comp_cession'          => 'Premium Compulsory Cession',
    'premium_treaty_total'          => 'Premium Treaty Total',
    'premium_treaty_retaintion'     => 'Premium Retaintion',
    'premium_treaty_quota'          => 'Premium Quota',
    'premium_treaty_1st_surplus'    => 'Premium 1st Surplus',
    'premium_treaty_2nd_surplus'    => 'Premium 2nd Surplus',
    'premium_treaty_3rd_surplus'    => 'Premium 3rd Surplus',
    'premium_treaty_fac'            => 'Premium FAC'
];
?>
<div class="box box-bordered box-default">
    <div class="box-header with-border">
        <h4 class="box-title">Basic Information</h4>
    </div>
    <table class="table table-responsive table-condensed">
        <tbody>
            <tr>
                <th>Policy Code</th>
                <td><?php echo anchor('policies/details/' . $record->policy_id, $record->policy_code, ['target' => '_blank']);?></td>
            </tr>
            <tr>
                <th>Treaty Type</th>
                <td><?php echo $record->treaty_type_name;?></td>
            </tr>
            <tr>
                <th>Distribution Type</th>
                <td class="<?php echo $record->premium_type == IQB_RI_TRANSACTION_PREMIUM_TYPE_BASIC ? 'text-green' : 'text-orange'?>">
                    <strong><?php echo IQB_RI_TRANSACTION_PREMIUM_TYPES[$record->premium_type];?></strong>
                </td>
            </tr>

        </tbody>
    </table>
</div>

<div class="box box-bordered box-default">
    <div class="box-header with-border">
        <h4 class="box-title">RI Transaction Details</h4>
    </div>

    <div class="box-body">
        <div class="row">
            <div class="col-sm-6">
                <table class="table table-responsive table-stripped table-bordered">
                    <thead>
                        <tr>
                            <th colspan="2">Sum Insured Distribution</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($si_columns as $column => $label): ?>
                            <tr>
                                <td><?php echo $label ?></td>
                                <td class="text-right"><?php echo $record->{$column} ?? '-' ?></td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
            <div class="col-sm-6">
                <table class="table table-responsive table-stripped table-bordered">
                    <thead>
                        <tr>
                            <th colspan="2">Premium Distribution</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($premium_columns as $column => $label): ?>
                            <tr>
                                <td><?php echo $label ?></td>
                                <td class="text-right"><?php echo $record->{$column} ?? '-' ?></td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
