<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Policy: Details - Policy Premium Overview Card
*/

// ------------------------------------------------------------------

/**
 * Find The Proper Premium Overview Card
 */
$_card_partial_view_by_portfolio = _PREMIUM_OVERVIEW_CARD_partial_view_by_portfolio($policy_record->portfolio_id);
?>
<div class="box box-bordered box-success" id="_premium-card">
    <div class="box-header with-border border-dark">
        <h3 class="no-margin">
        <span class="pull-left">Premium Calculation Table</span>
        <span class="pull-right">
            <?php if( in_array($policy_record->status, [IQB_POLICY_STATUS_DRAFT, IQB_POLICY_STATUS_UNVERIFIED]) && $this->dx_auth->is_authorized_any('policies', ['edit.draft.policy', 'edit.unverified.policy']) ): ?>
                    <span class="action divider"></span>
                    <a href="#"
                        class="action trg-dialog-edit"
                        title="Update Premium"
                        data-toggle="tooltip"
                        data-box-size="large"
                        data-title='<i class="fa fa-pencil-square-o"></i> Update Premium - <?php echo $policy_record->code?>'
                        data-url="<?php echo site_url('premium/edit/' . $policy_record->id );?>"
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
    $this->load->view($_card_partial_view_by_portfolio, ['premium_record' => $premium_record, 'policy_record' => $policy_record]);
    ?>

</div>