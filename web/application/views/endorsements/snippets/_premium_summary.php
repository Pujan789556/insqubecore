<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Endorsement - Premium Summary on Premium Form
*/
$old_object = get_object_from_policy_record($policy_record);
$new_object = NULL;
$computation_basis_text = '';
if( !_ENDORSEMENT_is_first( $endorsement_record->txn_type) && $endorsement_record->refund_compute_reference )
{
    $computation_basis_text = _ENDORSEMENT_compute_reference_dropdown(FALSE)[$endorsement_record->refund_compute_reference];
    try {
        $new_object = get_object_from_object_audit($policy_record, $endorsement_record->audit_object);
    } catch (Exception $e) {
    }
}

if( _ENDORSEMENT_is_premium_computable_by_type($endorsement_record->txn_type) )
{
    $grand_total =  floatval($endorsement_record->amt_basic_premium) +
                    floatval($endorsement_record->amt_pool_premium) +
                    floatval($endorsement_record->amt_cancellation_fee);
}
else
{
    $grand_total =  floatval($endorsement_record->amt_transfer_fee) +
                    floatval($endorsement_record->amt_transfer_ncd) ;
}
$grand_total +=  floatval($endorsement_record->amt_stamp_duty) +
                floatval($endorsement_record->amt_vat) +
                floatval($endorsement_record->amt_cancellation_fee);

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
    $gross_si   = $old_object->amt_sum_insured;
    $net_si     = _OBJ_si_net($old_object, $new_object);
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
                        <td class="text-right"><?php echo number_format($gross_si, 2);?></td>
                    </tr>
                    <tr>
                        <th>Net Sum Insured (Rs.)</th>
                        <td class="text-right"><?php echo number_format($net_si, 2);?></td>
                    </tr>
                    <tr>
                        <th>Direct Discount</th>
                        <td class="text-right"><?php echo $policy_record->flag_dc === IQB_POLICY_FLAG_DC_DIRECT ? 'Yes' : 'No';?></td>
                    </tr>
                    <tr>
                        <th>Premium Computation Basis</th>
                        <td class="text-right"><?php echo $computation_basis_text;?></td>
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
                    <?php if( _ENDORSEMENT_is_premium_computable_by_type($endorsement_record->txn_type) ): ?>
                        <tr>
                            <td>Basic Premium (Rs.)</td>
                            <td class="text-right"><?php echo number_format($endorsement_record->amt_basic_premium, 2);?></td>
                        </tr>
                        <tr>
                            <td>Pool Premium (Rs.)</td>
                            <td class="text-right"><?php echo number_format($endorsement_record->amt_pool_premium, 2);?></td>
                        </tr>
                        <tr>
                            <td>Stamp Duty (Rs.)</td>
                            <td class="text-right"><?php echo number_format($endorsement_record->amt_stamp_duty, 2);?></td>
                        </tr>
                        <tr>
                            <td>Cancellation Fee (Rs.)</td>
                            <td class="text-right"><?php echo number_format($endorsement_record->amt_cancellation_fee, 2);?></td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td>Ownership Transfer Fee (Rs.)</td>
                            <td class="text-right"><?php echo number_format($endorsement_record->amt_transfer_fee, 2);?></td>
                        </tr>
                        <tr>
                            <td>No Claim Discount Fee (Rs.)</td>
                            <td class="text-right"><?php echo number_format($endorsement_record->amt_transfer_ncd, 2);?></td>
                        </tr>
                    <?php endif ?>

                    <tr>
                        <td>VAT (Rs.)</td>
                        <td class="text-right"><?php echo number_format($endorsement_record->amt_vat, 2);?></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <th>Grand Total (Rs.)</th>
                        <th class="text-right"><?php echo number_format($grand_total, 2);?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
