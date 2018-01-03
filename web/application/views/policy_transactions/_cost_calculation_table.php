<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Policy: Details - Policy Cost Calculation Table
*/

// ------------------------------------------------------------------

/**
 * Find The Proper Premium Overview Card
 */
$cost_calculation_table_view = _POLICY__partial_view__cost_calculation_table($policy_record->portfolio_id);
?>
<div class="box box-bordered box-success" id="_premium-card">
    <div class="box-header with-border border-dark">
        <h3 class="no-margin">
        <span class="pull-left">Premium Calculation Table</span>
        <span class="pull-right">
            <?php if( is_policy_editable($policy_record->status, FALSE) ):
                $txn_type = $policy_record->ancestor_id ? IQB_POLICY_TXN_TYPE_RENEWAL:   IQB_POLICY_TXN_TYPE_FRESH;
                $update_premium_url = 'policy_transactions/premium/' . $txn_type . '/' . $policy_record->id;
            ?>
                    <a href="#"
                        class="action trg-dialog-edit btn btn-primary btn-sm"
                        title="Update Premium"
                        data-toggle="tooltip"
                        data-box-size="large"
                        data-title='<i class="fa fa-pencil-square-o"></i> Update Premium - <?php echo $policy_record->code?>'
                        data-url="<?php echo site_url($update_premium_url);?>"
                        data-form="#_form-premium">
                        <i class="fa fa-pencil-square-o"></i>
                    </a>
            <?php endif?>
        </span>
        </h3>
    </div>

    <?php
    /**
     * Load Partial Overview Card
     */
    $this->load->view($cost_calculation_table_view, ['txn_record' => $txn_record, 'policy_record' => $policy_record]);
    ?>

</div>