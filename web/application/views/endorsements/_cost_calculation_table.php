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
            <span class="text-left">Transaction Details</span>
            <span class="pull-right">
                <?php if( _POLICY_is_editable($policy_record->status, FALSE) ):
                    $update_premium_url = site_url('endorsements/premium/' . $endorsement_record->id);
                ?>
                        <a href="#"
                            class="action trg-dialog-edit btn btn-primary btn-sm"
                            title="Update Premium"
                            data-toggle="tooltip"
                            data-box-size="large"
                            data-title='<i class="fa fa-pencil-square-o"></i> Update Premium - <?php echo $policy_record->code?>'
                            data-url="<?php echo $update_premium_url;?>"
                            data-form="#_form-premium">
                            <i class="fa fa-pencil-square-o"></i>
                        </a>
                <?php endif?>
            </span>
        </h3>
    </div>
    <div class="box-body no-padding-b"><h4 class="no-margin">Premium Calculation Table</h4></div>

    <?php
    /**
     * Load Partial Overview Card
     */
    $this->load->view($cost_calculation_table_view, ['endorsement_record' => $endorsement_record, 'policy_record' => $policy_record]);
    ?>

    <div class="box-footer">
        <h4>सम्पुष्टि विवरण</h4>
        <table class="table table-responsive table-bordered">
            <tbody>
                <tr>
                    <td><?php echo htmlspecialchars(nl2br($endorsement_record->txn_details)); ?></td>
                </tr>
            </tbody>
        </table>
    </div>

</div>