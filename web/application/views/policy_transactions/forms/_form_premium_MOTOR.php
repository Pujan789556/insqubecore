<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : MOTOR Policy Premium
 */
$object_attributes = $policy_object->attributes ? json_decode($policy_object->attributes) : NULL;
?>
<?php echo form_open( $this->uri->uri_string(),
        [
            'class' => 'form-iqb-general',
            'id'    => '_form-premium',
            'data-pc' => '.bootbox-body' // parent container ID
        ],
        // Hidden Fields
        isset($policy_record) ? ['id' => $policy_record->id] : []);
?>

    <div class="box box-solid box-bordered">
        <div class="box-header with-border">
            <h4 class="box-title">Policy Summary</h4>
        </div>
        <table class="table table-responsive table-condensed">
            <tbody>
                <tr>
                    <th>Portfolio</th>
                    <td><?php echo $policy_record->portfolio_name;?></td>
                </tr>

                <?php if($policy_record->portfolio_id === IQB_SUB_PORTFOLIO_COMMERCIAL_VEHICLE_ID):?>
                    <tr>
                        <th>CVC Type</th>
                        <td>
                            <?php echo $object_attributes->cvc_type ? _OBJ_MOTOR_CVC_type_dropdown(FALSE)[$object_attributes->cvc_type] : '-'?>
                        </td>
                    </tr>
                <?php endif?>

                <tr>
                    <th>Ownership</th>
                    <td><?php echo _OBJ_MOTOR_ownership_dropdown(FALSE)[$object_attributes->ownership]?></td>
                </tr>

                <tr>
                    <th>Engine Capacity</th>
                    <td><?php echo $object_attributes->engine_capacity . ' ' . $object_attributes->ec_unit?></td>
                </tr>

                <tr>
                    <th>Policy Package</th>
                    <td><?php echo _OBJ_policy_package_dropdown($policy_record->portfolio_id)[$policy_record->policy_package]?></td>
                </tr>

                <?php
                // Find the Thirdparty Discount
                $tariff = json_decode($tariff_record->tariff, true);
                $third_party_premium = 0.00;
                foreach ($tariff as $t)
                {
                    if( $object_attributes->engine_capacity >= $t['ec_min'] && $object_attributes->engine_capacity <= $t['ec_max'])
                    {
                        $third_party_premium = $t['third_party'];
                        break;
                    }
                }
                ?>
                <tr>
                    <th>Third Party Premium</th>
                    <td>Rs. <?php echo $third_party_premium?></td>
                </tr>

                <tr>
                    <th>Sum Insured Value (Rs.)</th>
                    <td>
                        <p class="form-control-static">Rs. <?php echo $policy_object->amt_sum_insured;?></p>
                        <p class="help-box">
                            When Sum Insured Value is below or equal to Rs. <strong>1 Lakh (100000.00)</strong> then the Stamp Duty = should be <strong>Rs. 10</strong>.
                            If its greater it should be <strong>Rs. 20</strong>.<br/><br/>
                            <code>
                                IF Sum Insured <= 100000 Then  <strong>Stamp Duty = Rs. 10</strong> <br/>
                                Else <strong>Stamp Duty = Rs. 10</strong>
                            </code>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th>Direct Discount</th>
                    <td><?php echo $policy_record->flag_dc === IQB_POLICY_FLAG_DC_DIRECT ? 'Yes' : 'No';?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="box box-solid box-bordered">
        <div class="box-header with-border">
            <h4 class="box-title">Premium Information</h4>
        </div>
        <div class="box-body form-horizontal">
            <?php
            /**
             * Comprehensive Premium Information
             */
            if( $policy_record->policy_package == IQB_POLICY_PACKAGE_MOTOR_COMPREHENSIVE )
            {
                $premium_computation_table = $txn_record->premium_computation_table ? json_decode($txn_record->premium_computation_table) : NULL;

                /**
                 * Load Form Components
                 */
                $this->load->view('templates/_common/_form_components_horz', [
                    'form_elements'     => $form_elements['premium'],
                    'form_record'       => $premium_computation_table
                ]);
            }
            ?>
        </div>
    </div>

    <?php
    /**
     * Load TXN Common Elements
     */
    $this->load->view('policy_transactions/forms/_form_txn_common', [
        'txn_record'        => $txn_record,
        'form_elements'     => $form_elements['basic']
    ]);

    /**
     * Other Common Components
     *  1. Premium Installments
     */
    echo $common_components;
    ?>

    <button type="submit" class="hide">Submit</button>
<?php echo form_close();?>
<script type="text/javascript">
// Load Txn Details from Endorsement Template
$('#template-reference').on('change', function(){
    var v = parseInt(this.value);
    if(v){
        // Load template body from the reference supplied
        $.getJSON('<?php echo base_url()?>endorsement_templates/body/'+v, function(r){
            // Update dropdown
            if(r.status == 'success'){
                $('#txn-details').val(r.body);
            }
            else{
                toastr[r.status](r.message);
            }
        });
    }
})
</script>

