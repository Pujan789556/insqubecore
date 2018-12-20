<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Endorsement - Premium Summary on Premium Form
*/
$old_object = get_object_from_policy_record($policy_record);
$new_object = NULL;
$premium_compute_text = '';
$refund_compute_text = '';
if( !_ENDORSEMENT_is_first( $endorsement_record->txn_type) )
{
    $compute_ref_dd = _ENDORSEMENT_compute_reference_dropdown(FALSE);
    $premium_compute_text   = $compute_ref_dd[$endorsement_record->premium_compute_reference] ?? '';
    $refund_compute_text    = $compute_ref_dd[$endorsement_record->refund_compute_reference] ?? '';
}

$grand_total = _ENDORSEMENT__compute_total_amount($endorsement_record);


/**
 * If active, use endorsement record else from object information
 */
if($endorsement_record->status == IQB_POLICY_ENDORSEMENT_STATUS_ACTIVE )
{
    $gross_si   = $endorsement_record->amt_sum_insured_object;
    $net_si     = $endorsement_record->amt_sum_insured_net;
}
else
{
    $policy_object  =   _OBJ__get_latest(
                            $policy_record->object_id,
                            $endorsement_record->txn_type,
                            $endorsement_record->audit_object
                        );
    $old_object = get_object_from_policy_record($policy_record);

    $gross_si   = $policy_object->amt_sum_insured;
    $net_si     = $gross_si - $old_object->amt_sum_insured;
}
?>

<div class="row">
    <div class="col-md-6">
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">Policy Summary</h4>
            </div>
            <table class="table table-responsive table-condensed">
                <tbody>
                    <tr>
                        <th>Portfolio</th>
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
                        <td class="text-right"><?php echo $premium_compute_text;?></td>
                    </tr>
                    <tr>
                        <th>Refund Computation Basis</th>
                        <td class="text-right"><?php echo $refund_compute_text;?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-md-6">
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">Premium Summary</h4>
            </div>
            <table class="table table-responsive table-condensed">
                <tbody>
                    <tr>
                        <td>Basic Premium (Rs.)</td>
                        <td class="text-right"><?php echo ac_format_number($endorsement_record->net_amt_basic_premium, 2);?></td>
                    </tr>
                    <tr>
                        <td>Pool Premium (Rs.)</td>
                        <td class="text-right"><?php echo ac_format_number($endorsement_record->net_amt_pool_premium, 2);?></td>
                    </tr>
                    <tr>
                        <td>Stamp Duty (Rs.)</td>
                        <td class="text-right"><?php echo ac_format_number($endorsement_record->net_amt_stamp_duty, 2);?></td>
                    </tr>
                    <tr>
                        <td>Cancellation Fee (Rs.)</td>
                        <td class="text-right"><?php echo ac_format_number($endorsement_record->net_amt_cancellation_fee, 2);?></td>
                    </tr>
                    <tr>
                        <td>Ownership Transfer Fee (Rs.)</td>
                        <td class="text-right"><?php echo ac_format_number($endorsement_record->net_amt_transfer_fee, 2);?></td>
                    </tr>
                    <tr>
                        <td>No Claim Discount Fee (Rs.)</td>
                        <td class="text-right"><?php echo ac_format_number($endorsement_record->net_amt_transfer_ncd, 2);?></td>
                    </tr>
                    <tr>
                        <td>VAT (Rs.)</td>
                        <td class="text-right"><?php echo ac_format_number($endorsement_record->net_amt_vat, 2);?></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <th>Grand Total (Rs.)</th>
                        <th class="text-right"><?php echo ac_format_number($grand_total, 2);?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
