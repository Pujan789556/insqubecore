<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Endorsement - Manual Premium Form
 */
$hidden_fields = ['policy_id' => $policy_record->id];
if(isset($endorsement_record))
{
    $hidden_fields['id'] = $endorsement_record->id;
}
?>
<?php echo form_open( $this->uri->uri_string(),
                        [
                            'class'     => 'form-iqb-general',
                            'data-pc'   => '.bootbox-body', // parent container ID
                            'id'        => '_form-premium'
                        ],
                        // Hidden Fields
                        $hidden_fields); ?>

<div class="row">
    <div class="col-md-6">
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
              <h4 class="box-title">Supply Premium Information</h4>
            </div>
            <div class="box-body form-horizontal">
                <table class="table table-responsive table-hover table-bordered margin-b-10">
                    <tbody>
                        <tr>
                            <th width="30%">Gross Sum Insured (Rs.)</th>
                            <td><?php echo number_format($endorsement_record->amt_sum_insured_object, 2) ?></td>
                        </tr>
                        <tr>
                            <th>Net Sum Insured (Changed) (Rs.)</th>
                            <td><?php echo ac_format_number($endorsement_record->amt_sum_insured_net, 2) ?></td>
                        </tr>
                    </tbody>
                </table>
                <div class="alert alert-warning">
                    NOTE!!!<br/>
                    Basic Premium, Pool Premium must be net actual amount.<br>
                    If you are returning premium, use (-) sign. Example: <strong>-4000.95</strong>
                </div>
                <?php
                /**
                 * Load Form Components
                 */
                $this->load->view('templates/_common/_form_components_horz', [
                    'form_elements' => $form_elements,
                    'form_record'   => $endorsement_record
                ]);
                ?>
            </div>
        </div>

        <?php
        /**
         * Other Common Components
         *  1. Premium Installments
         */
        echo $common_components;
        ?>

        <button type="submit" class="hide">Submit</button>
    </div>
</div>
<?php echo form_close();?>