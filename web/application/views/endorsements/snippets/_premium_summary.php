<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Endorsement - Premium Summary on Premium Form
*/
$old_object = get_object_from_policy_record($policy_record);
$new_object = NULL;
$premium_compute_text = '';
$refund_compute_text = '';
if( !_ENDORSEMENT_is_first( $record->txn_type) )
{
    // $compute_ref_dd = _ENDORSEMENT_compute_reference_dropdown(FALSE);
    // $premium_compute_text   = $compute_ref_dd[$record->pc_ref_basic] ?? '';
    // $refund_compute_text    = $compute_ref_dd[$record->rc_ref_basic] ?? '';
}

$total_premium  = _ENDORSEMENT__total_premium($record);
$grand_total    = _ENDORSEMENT__grand_total($record);


/**
 * If active, use endorsement record else from object information
 */
if($record->status == IQB_ENDORSEMENT_STATUS_ACTIVE )
{
    $gross_si   = $record->amt_sum_insured_object;
    $net_si     = $record->amt_sum_insured_net;
}
else
{
    $policy_object  =   _OBJ__get_latest(
                            $policy_record->object_id,
                            $record->txn_type,
                            $record->audit_object
                        );
    $old_object = get_object_from_policy_record($policy_record);

    $gross_si   = $policy_object->amt_sum_insured;
    $net_si     = $gross_si - $old_object->amt_sum_insured;
}
?>
<div class="box box-solid box-bordered">
    <div class="box-header with-border">
        <h3 class="box-title">Endorsement Premium Distribution</h3>
    </div>
    <div class="box-body bg-gray-light">
        <div class="row">
            <div class="col-md-4">
                <div class="box box-solid box-bordered">
                    <div class="box-header with-border">
                        <h4 class="box-title">Policy Summary</h4>
                    </div>
                    <table class="table table-responsive table-condensed">
                        <tbody>
                            <tr>
                                <th width="40%">Portfolio</th>
                                <td class="text-right"><?php echo $policy_record->portfolio_name_en;?></td>
                            </tr>
                            <tr>
                                <th>Gross Sum Insured (Rs.)</th>
                                <td class="text-right"><?php echo ac_format_number($gross_si, 2);?></td>
                            </tr>
                            <tr>
                                <th>Net Sum Insured (Rs.)</th>
                                <td class="text-right"><?php echo ac_format_number($net_si, 2);?></td>
                            </tr>
                            <tr>
                                <th>Direct Discount</th>
                                <td class="text-right"><?php echo $policy_record->flag_dc === IQB_POLICY_FLAG_DC_DIRECT ? 'Yes' : 'No';?></td>
                            </tr>
                            <tr>
                                <th>Premium Computation Basis</th>
                                <td class="text-right"><?php //echo $premium_compute_text;?></td>
                            </tr>
                            <tr>
                                <th>Refund Computation Basis</th>
                                <td class="text-right"><?php // echo $refund_compute_text;?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-md-8">
                <div class="box box-solid box-bordered">
                    <div class="box-header with-border">
                        <h4 class="box-title">Premium Summary</h4>
                    </div>
                    <table class="table table-responsive table-condensed table-hover table-bordered">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th class="text-right">Gross Full</th>
                                <th class="text-right">Gross Computed</th>
                                <th class="text-right">Refund Computed</th>
                                <th class="text-right">Net (Gross - Refund)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Basic Premium (Rs.)</td>
                                <td class="text-right"><?php echo ac_format_number($record->gross_full_amt_basic_premium, 2);?></td>
                                <td class="text-right"><?php echo ac_format_number($record->gross_computed_amt_basic_premium, 2);?></td>
                                <td class="text-right"><?php echo ac_format_number($record->refund_amt_basic_premium, 2);?></td>
                                <td class="text-right"><?php echo ac_format_number($record->net_amt_basic_premium, 2);?></td>
                            </tr>
                            <tr>
                                <td>Pool Premium (Rs.)</td>
                                <td class="text-right"><?php echo ac_format_number($record->gross_full_amt_pool_premium, 2);?></td>
                                <td class="text-right"><?php echo ac_format_number($record->gross_computed_amt_pool_premium, 2);?></td>
                                <td class="text-right"><?php echo ac_format_number($record->refund_amt_pool_premium, 2);?></td>
                                <td class="text-right"><?php echo ac_format_number($record->net_amt_pool_premium, 2);?></td>
                            </tr>
                            <tr>
                                <td colspan="4">Cancellation Fee (Rs.)</td>
                                <td class="text-right"><?php echo ac_format_number($record->net_amt_cancellation_fee, 2);?></td>
                            </tr>
                            <tr>
                                <td colspan="4">Ownership Transfer Fee (Rs.)</td>
                                <td class="text-right"><?php echo ac_format_number($record->net_amt_transfer_fee, 2);?></td>
                            </tr>
                            <tr>
                                <td colspan="4">No Claim Discount Fee (Rs.)</td>
                                <td class="text-right"><?php echo ac_format_number($record->net_amt_transfer_ncd, 2);?></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4">Gross Total (Rs.)</th>
                                <th class="text-right"><?php echo ac_format_number($total_premium, 2);?></th>
                            </tr>
                            <tr>
                                <th colspan="4">Stamp Duty (Rs.)</th>
                                <th class="text-right"><?php echo ac_format_number($record->net_amt_stamp_duty, 2);?></th>
                            </tr>
                            <tr>
                                <th colspan="4">VAT (Rs.)</th>
                                <th class="text-right"><?php echo ac_format_number($record->net_amt_vat, 2);?></th>
                            </tr>
                            <tr>
                                <th colspan="4">Grand Total (Rs.)</th>
                                <th class="text-right"><?php echo ac_format_number($grand_total, 2);?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
