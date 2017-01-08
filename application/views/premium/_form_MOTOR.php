<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : MOTOR Policy Premium
 */
$object_attributes = $policy_object->attributes ? json_decode($policy_object->attributes) : NULL;

// echo '<pre>';
// print_r($object_attributes);
// // print_r($policy_record);
// // print_r($tariff_record);
// echo '</pre>';
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

    <div class="form-group">
        <label class="col-sm-2 control-label">Sub-Portfolio</label>
        <div class="col-sm-10">
        <p class="form-control-static"><?php echo _PO_MOTOR_sub_portfolio_dropdown(FALSE)[$object_attributes->sub_portfolio]?></p>
        </div>
    </div>
    <?php if($object_attributes->sub_portfolio === IQB_SUB_PORTFOLIO_COMMERCIAL_VEHICLE_CODE):?>
        <div class="form-group">
            <label class="col-sm-2 control-label">CVC Type</label>
            <div class="col-sm-10">
            <p class="form-control-static"><?php echo $object_attributes->cvc_type ? _PO_MOTOR_CVC_type_dropdown(FALSE)[$object_attributes->cvc_type] : '-'?></p>
            </div>
        </div>
    <?php endif?>

    <div class="form-group">
        <label class="col-sm-2 control-label">Ownership</label>
        <div class="col-sm-10">
        <p class="form-control-static"><?php echo _PO_MOTOR_ownership_dropdown(FALSE)[$object_attributes->ownership]?></p>
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
        <p class="form-control-static"><?php echo _PO_policy_package_dropdown($policy_record->portfolio_id)[$policy_record->policy_package]?></p>
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
        <label class="col-sm-2 control-label">Vehicle Price</label>
        <div class="col-sm-10">
        <p class="form-control-static">Rs. <?php echo $object_attributes->price_vehicle + $object_attributes->price_accessories;?></p>
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
     * Load Form Components
     */
    $this->load->view('templates/_common/_form_components_horz', [
        'form_elements'         => $form_elements,
        'form_policy_record'    => $policy_record
    ]);
    ?>

    <button type="submit" class="hide">Submit</button>
<?php echo form_close();?>

