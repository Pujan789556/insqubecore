<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Policy TXN - Endorsement
 */
?>
<?php echo form_open( $this->uri->uri_string(),
                        [
                            'class'     => 'form-horizontal form-iqb-general',
                            'data-pc'   => '.bootbox-body', // parent container ID
                            'id'        => '_form-policy_txn'
                        ],
                        // Hidden Fields
                        isset($record) ? ['id' => $record->id] : []); ?>
    <div class="box box-solid box-bordered">
        <div class="box-header with-border">
          <h4 class="box-title">Supply Basic Information</h4>
        </div>
        <div class="box-body">
            <?php
            /**
             * Load Form Components
             */
            $this->load->view('templates/_common/_form_components_horz', [
                'form_elements' => $form_elements['basic'],
                'form_record'   => $record
            ]);
            ?>
        </div>
    </div>

    <div class="box box-solid box-bordered <?php echo isset($record->txn_type) && $record->txn_type == IQB_POLICY_TXN_TYPE_ET ? '' : 'hide' ?>" id="box-txn">
        <div class="box-header with-border">
          <h4 class="box-title">Existing Transaction Summary</h4>
        </div>
        <div class="box-body bg-gray">
            <table class="table table-responsive table-condensed">
                <tbody>
                    <tr><th>Sum Insured Amount (Rs)</th><td class="text-right"><?php echo $policy_record->cur_amt_sum_insured;?></td></tr>
                    <tr><th>Total Premium Amount (Rs)</th><td class="text-right"><?php echo $policy_record->cur_amt_total_premium;?></td></tr>
                    <tr><th>Pool Premium Amount (Rs)</th><td class="text-right"><?php echo $policy_record->cur_amt_pool_premium;?></td></tr>
                    <tr><th>Commissionable Amount (Rs)</th><td class="text-right"><?php echo $policy_record->cur_amt_commissionable;?></td></tr>
                    <tr><th>Agent Commission Amount (Rs)</th><td class="text-right"><?php echo $policy_record->cur_amt_agent_commission;?></td></tr>
                    <tr><th>VAT Amount (Rs)</th><td class="text-right"><?php echo $policy_record->cur_amt_vat;?></td></tr>
                </tbody>
            </table>
        </div>
        <div class="box-header with-border">
          <h4 class="box-title">Supply Transactional Information</h4>
        </div>
        <div class="box-body">
            <?php
            /**
             * Load Form Components
             */
            $txn_elements = $form_elements['transaction'];
            $this->load->view('templates/_common/_form_components_horz', [
                'form_elements'     => $txn_elements,
                'form_record'       => $record,
                'grid_label'        => 'col-sm-4',
                'grid_form_control' => 'col-sm-8'
            ]);
            ?>
        </div>
    </div>

    <button type="submit" class="hide">Submit</button>
<?php echo form_close();?>
<script type="text/javascript">

    $('#_txn_type').on('change', function(){
        var $this   = $(this),
            val     = parseInt( $this.val() ),
            $btxn   = $('#box-txn');

        if(val == 3){
            $btxn.hide().removeClass('hide').fadeIn();
        }
        else{
            $btxn.fadeOut('fast',function(){
                $(this).addClass('hide');
            });
        }
    });
</script>