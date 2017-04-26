<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : MOTOR Policy Premium
 */
$object_attributes = $policy_object->attributes ? json_decode($policy_object->attributes) : NULL;
?>
<?php echo form_open( $this->uri->uri_string(),
        [
            'class' => 'form-horizontal form-iqb-general',
            'id'    => '_form-premium',
            'data-pc' => '.bootbox-body' // parent container ID
        ],
        // Hidden Fields
        isset($policy_record) ? ['id' => $policy_record->id] : []);
?>
    <div class="form-group">
        <label class="col-sm-2 control-label">Portfolio</label>
        <div class="col-sm-10">
            <p class="form-control-static"><?php echo $policy_record->portfolio_name;?></p>
        </div>
    </div>
    <?php if($policy_record->portfolio_id === IQB_SUB_PORTFOLIO_COMMERCIAL_VEHICLE_ID):?>
        <div class="form-group">
            <label class="col-sm-2 control-label">CVC Type</label>
            <div class="col-sm-10">
            <p class="form-control-static"><?php echo $object_attributes->cvc_type ? _OBJ_MOTOR_CVC_type_dropdown(FALSE)[$object_attributes->cvc_type] : '-'?></p>
            </div>
        </div>
    <?php endif?>

    <div class="form-group">
        <label class="col-sm-2 control-label">Ownership</label>
        <div class="col-sm-10">
        <p class="form-control-static"><?php echo _OBJ_MOTOR_ownership_dropdown(FALSE)[$object_attributes->ownership]?></p>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">Engine Capacity</label>
        <div class="col-sm-10">
        <p class="form-control-static"><?php echo $object_attributes->engine_capacity . ' ' . $object_attributes->ec_unit?></p>
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-2 control-label">Policy Package</label>
        <div class="col-sm-10">
        <p class="form-control-static"><?php echo _OBJ_policy_package_dropdown($policy_record->portfolio_id)[$policy_record->policy_package]?></p>
        </div>
    </div>

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
    <div class="form-group">
        <label class="col-sm-2 control-label">Third Party Premium</label>
        <div class="col-sm-10">
        <p class="form-control-static">Rs. <?php echo $third_party_premium?></p>
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-2 control-label">Sum Insured Value (Rs.)</label>
        <div class="col-sm-10">
        <p class="form-control-static">Rs. <?php echo $policy_object->amt_sum_insured;?></p>
        <p class="help-box">
            When Sum Insured Value is below or equal to Rs. <strong>1 Lakh (100000.00)</strong> then the Stamp Duty = should be <strong>Rs. 10</strong>.
            If its greater it should be <strong>Rs. 20</strong>.<br/><br/>
            <code>
                IF Sum Insured <= 100000 Then  <strong>Stamp Duty = Rs. 10</strong> <br/>
                Else <strong>Stamp Duty = Rs. 10</strong>
            </code>
        </p>
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-2 control-label">Direct Discount</label>
        <div class="col-sm-10">
        <p class="form-control-static"><?php echo $policy_record->flag_dc === 'D' ? 'Yes' : 'No';?></p>
        </div>
    </div>

    <?php
    /**
     * Old Premium Record Data
     */
    $premium_extra_fields_object = $crf_record->other_cost_fields ? json_decode($crf_record->other_cost_fields) : NULL;
    if($premium_extra_fields_object)
    {
        $premium_extra_fields_object->stamp_duty_amount = $crf_record->stamp_duty_amount;
    }

    /**
     * Load Form Components
     */
    $this->load->view('templates/_common/_form_components_horz', [
        'form_elements'     => $form_elements,
        'form_record'       => $premium_extra_fields_object
    ]);
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

