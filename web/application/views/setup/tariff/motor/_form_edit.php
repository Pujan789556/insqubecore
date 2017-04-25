<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Tariff - Motor
 */
$anchor_remove = '<div class="row remove-row"><div class="col-xs-12 text-right">' .
                         '<a href="#" onclick=\'$(this).closest(".box-body").remove()\'>Remove</a>' .
                     '</div></div>' .
                 '</div>';
?>
<style type="text/css">
.remove-row{margin-top: 10px; margin-bottom: 10px; border-top:1px solid #ccc;}
.box-body.with-bordered{border: 1px solid #eee;}
</style>
<?php echo form_open( $this->uri->uri_string(),
                        [
                            'class' => 'form-iqb-general',
                            'data-pc' => '.bootbox-body' // parent container ID
                        ],
                        // Hidden Fields
                        isset($record) ? ['id' => $record->id] : []); ?>

    <div class="form-horizontal">
        <div class="form-group">
            <label class="col-sm-2 control-label">Ownership</label>
            <div class="col-sm-10">
            <p class="form-control-static"><?php echo _OBJ_MOTOR_ownership_dropdown(FALSE)[$record->ownership]?></p>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Portfolio</label>
            <div class="col-sm-10">
            <p class="form-control-static"><?php echo $record->portfolio_name_en;?></p>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">CVC Type</label>
            <div class="col-sm-10">
            <p class="form-control-static"><?php echo $record->cvc_type ? _OBJ_MOTOR_CVC_type_dropdown(FALSE)[$record->cvc_type] : '-'?></p>
            </div>
        </div>

        <?php
        /**
         * Default Configurations
         *
         * Load Form Components
         */
        $default_form_elements = $form_elements['defaults'];
        $this->load->view('templates/_common/_form_components_horz', [
                'form_elements'     => $default_form_elements,
                'form_record'       => $record
        ]);
        ?>

    </div>


    <div class="box box-solid box-bordered box-config">
        <div class="box-header with-border bg-teal">
            <h4 class="box-title">Tariff Details</h4>
        </div>
        <?php
        /**
         * Load Form Components
         */
        $tariff = $record->tariff ? json_decode($record->tariff, TRUE) : NULL;

        $partial_form_elements = $form_elements['tariff'];
        $i = 0;
        $__box_id = '_tariff-box';
        if($tariff)
        {
            foreach($tariff as $single_tarrif)
            {
                foreach($single_tarrif as $key=>$fields)
                {
                    // Field Name
                    $field_name = 'tariff['.$key . ']';
                    if( is_array($fields) )
                    {
                        foreach($fields as $key => $value)
                        {
                            $nested_field = $field_name . '[' . $key . '][]';

                            // Search for the Keys
                            $index = array_search($nested_field, array_column($partial_form_elements, 'field'));
                            $partial_form_elements[$index]['_default'] = $value;
                        }
                    }
                    else{
                        $field_name .= '[]';
                        // Search for the Keys
                        $index = array_search($field_name, array_column($partial_form_elements, 'field'));
                        $partial_form_elements[$index]['_default'] = $fields;
                    }
                }

                $i++;

                $form_data = [
                    'form_elements'     => $partial_form_elements,
                    'form_record'       => NULL,
                    'inline_grid_width' => 'col-sm-6 col-md-4',
                    '__show_remove'     => FALSE
                ];
                if($i === 1)
                {
                    $form_data['__box_id'] = $__box_id;
                }
                else
                {
                    $form_data['__show_remove'] = TRUE;
                }

                /**
                 * Load These Elements
                 */
                $this->load->view('setup/tariff/motor/_form_edit_inline', $form_data);

            }
        }
        else
        {
            $this->load->view('setup/tariff/motor/_form_edit_inline', [
                'form_elements'     => $partial_form_elements,
                'form_record'       => NULL,
                'inline_grid_width' => 'col-sm-6 col-md-4',
                '__box_id'          => $__box_id,
                '__show_remove'     => FALSE
            ]);
        }
        ?>
        <div class="box-footer bg-info">
            <a href="#" class="btn bg-teal" onclick="__duplicate_configs('#<?php echo $__box_id?>', this)">Add More</a>
        </div>
    </div>

    <div class="box box-solid box-bordered box-config">
        <div class="box-header with-border bg-teal">
            <h4 class="box-title">Disable Friendly Discount (Motorcycle Only)</h4>
            <a href="#" class="pull-right btn btn-default btn-sm" onclick="__zerofill('.box-config', this)">Fill Zero</a>
        </div>
        <div class="box-body bg-gray-light">
            <?php
            /**
             * Load Form Components
             */
            $dr_mcy_disabled_friendly = $form_elements['dr_mcy_disabled_friendly'];
            $this->load->view('templates/_common/_form_components_inline', [
                    'form_elements'     => $dr_mcy_disabled_friendly,
                    'form_record'       => $record,
                    'inline_grid_width' => 'col-sm-6 col-md-4'
            ]);
            ?>
            <p class="small text-warning">नोटः माथि (१) सरकारी बाहेक तथा (२) दुवैमा अपाङ्ग मैत्री तीन पाङ्ग्रे मोटरसाइकलको हकमा, माथि उल्लिखित सवै बीमादरमा २५% छुट हुने छ ।</p>
        </div>
    </div>

    <div class="box box-solid box-bordered box-config">
        <div class="box-header with-border bg-teal">
            <h4 class="box-title">Private Hire (Private Vehicle Only)</h4>
            <a href="#" class="pull-right btn btn-default btn-sm" onclick="__zerofill('.box-config', this)">Fill Zero</a>
        </div>
        <div class="box-body bg-gray-light">
            <?php
            /**
             * Load Form Components
             */
            $rate_pvc_on_hire = $form_elements['rate_pvc_on_hire'];
            $this->load->view('templates/_common/_form_components_inline', [
                    'form_elements'     => $rate_pvc_on_hire,
                    'form_record'       => $record,
                    'inline_grid_width' => 'col-sm-6 col-md-4'
            ]);
            ?>
            <p class="small text-warning">कुनै व्यक्ति वा संस्थाको निजी सवारी साधन अर्को व्यक्ति वा संस्थाको निजी प्रयोगको लागि भाडा (प्राइभेट हायर) मा दिइएको भएमा उल्लिखित बीमाशुल्कदरमा १०% थप गरी दर कायम गर्नु पर्नेछ ।</p>
        </div>
    </div>

    <div class="box box-solid box-bordered box-config">
        <div class="box-header with-border bg-teal">
          <h4 class="box-title">Discount on Personal Use (Commercial Vehicle Only)</h4>
          <a href="#" class="pull-right btn btn-default btn-sm" onclick="__zerofill('.box-config', this)">Fill Zero</a>
        </div>
        <div class="box-body bg-gray-light">
            <?php
            /**
             * Load Form Components
             */
            $dr_cvc_on_personal_use = $form_elements['dr_cvc_on_personal_use'];
            $this->load->view('templates/_common/_form_components_inline', [
                    'form_elements'     => $dr_cvc_on_personal_use,
                    'form_record'       => $record,
                    'inline_grid_width' => 'col-sm-6 col-md-4'
            ]);
            ?>
            <p class="small text-warning">निजी प्रयोेजनको लागि प्रयोग गर्ने सवारी साधनको ब्यापक बीमा गर्दा शुरु बीमाशुल्कको २५ प्रतिशत छुटहुनेछ ।</p>
        </div>
    </div>

    <div class="box box-solid box-bordered box-config">
        <div class="box-header with-border bg-teal">
          <h4 class="box-title">No Claim Discount</h4>
        </div>
        <?php
        /**
         * Load Form Components
         */
        $no_claim_discount = $record->no_claim_discount ? json_decode($record->no_claim_discount, TRUE) : NULL;

        $partial_form_elements = $form_elements['no_claim_discount'];
        $i = 0;
        $__box_id = '_no-claim-discount-box';
        if($no_claim_discount)
        {
            foreach($no_claim_discount as $single_tarrif)
            {
                foreach($single_tarrif as $key=>$value)
                {
                    // Field Name
                    $field_name = 'no_claim_discount['.$key . '][]';
                    // Search for the Keys
                    $index = array_search($field_name, array_column($partial_form_elements, 'field'));
                    $partial_form_elements[$index]['_default'] = $value;
                }

                $i++;

                $form_data = [
                    'form_elements'     => $partial_form_elements,
                    'form_record'       => NULL,
                    'inline_grid_width' => 'col-sm-6 col-md-4',
                    '__show_remove'     => FALSE
                ];
                if($i === 1)
                {
                    $form_data['__box_id'] = $__box_id;
                }
                else
                {
                    $form_data['__show_remove'] = TRUE;
                }
                /**
                 * Load These Elements
                 */
                $this->load->view('setup/tariff/motor/_form_edit_inline', $form_data);
            }
        }
        else
        {
            $this->load->view('setup/tariff/motor/_form_edit_inline', [
                'form_elements'     => $partial_form_elements,
                'form_record'       => NULL,
                'inline_grid_width' => 'col-sm-6 col-md-4',
                '__box_id'          => $__box_id,
                '__show_remove'     => FALSE
            ]);
        }
        ?>
        <div class="box-footer bg-info">
            <a href="#" class="btn bg-teal" onclick="__duplicate_configs('#<?php echo $__box_id?>', this)">Add More</a>
        </div>
    </div>

    <div class="box box-solid box-bordered box-config">
        <div class="box-header with-border bg-teal">
          <h4 class="box-title">Voluntary Excess Discount</h4>
        </div>

        <?php
        /**
         * Load Form Components
         */
        $dr_voluntary_excess = $record->dr_voluntary_excess ? json_decode($record->dr_voluntary_excess, TRUE) : NULL;

        $partial_form_elements = $form_elements['dr_voluntary_excess'];
        $i = 0;
        $__box_id = '_dr-voluntary-excess';
        if($dr_voluntary_excess)
        {
            foreach($dr_voluntary_excess as $single_tarrif)
            {
                foreach($single_tarrif as $key=>$value)
                {
                    // Field Name
                    $field_name = 'dr_voluntary_excess['.$key . '][]';
                    // Search for the Keys
                    $index = array_search($field_name, array_column($partial_form_elements, 'field'));
                    $partial_form_elements[$index]['_default'] = $value;
                }

                $i++;
                $form_data = [
                    'form_elements'     => $partial_form_elements,
                    'form_record'       => NULL,
                    'inline_grid_width' => 'col-sm-6 col-md-4',
                    '__show_remove'     => FALSE
                ];
                if($i === 1)
                {
                    $form_data['__box_id'] = $__box_id;
                }
                else
                {
                    $form_data['__show_remove'] = TRUE;
                }
                /**
                 * Load These Elements
                 */
                $this->load->view('setup/tariff/motor/_form_edit_inline', $form_data);
            }
        }
        else
        {
            $this->load->view('setup/tariff/motor/_form_edit_inline', [
                'form_elements'     => $partial_form_elements,
                'form_record'       => NULL,
                'inline_grid_width' => 'col-sm-6 col-md-4',
                '__box_id'          => $__box_id,
                '__show_remove'     => FALSE
            ]);
        }
        ?>
        <div class="box-footer bg-info">
            <a href="#" class="btn bg-teal" onclick="__duplicate_configs('#<?php echo $__box_id?>', this)">Add More</a>
        </div>
    </div>

    <div class="box box-solid box-bordered box-config">
        <div class="box-header with-border bg-teal">
          <h4 class="box-title">Compulsory Excess Amount</h4>
        </div>
        <?php
        /**
         * Load Form Components
         */
        $pramt_compulsory_excess = $record->pramt_compulsory_excess ? json_decode($record->pramt_compulsory_excess, TRUE) : NULL;

        $partial_form_elements = $form_elements['pramt_compulsory_excess'];
        $i = 0;
        $__box_id = '_prmt-compulsory-excess';
        if($pramt_compulsory_excess)
        {
            foreach($pramt_compulsory_excess as $single_tarrif)
            {
                foreach($single_tarrif as $key=>$value)
                {
                    // Field Name
                    $field_name = 'pramt_compulsory_excess['.$key . '][]';
                    // Search for the Keys
                    $index = array_search($field_name, array_column($partial_form_elements, 'field'));
                    $partial_form_elements[$index]['_default'] = $value;
                }

                $i++;
                $form_data = [
                    'form_elements'     => $partial_form_elements,
                    'form_record'       => NULL,
                    'inline_grid_width' => 'col-sm-6 col-md-4',
                    '__show_remove'     => FALSE
                ];
                if($i === 1)
                {
                    $form_data['__box_id'] = $__box_id;
                }
                else
                {
                    $form_data['__show_remove'] = TRUE;
                }
                /**
                 * Load These Elements
                 */
                $this->load->view('setup/tariff/motor/_form_edit_inline', $form_data);
            }
        }
        else
        {
            $this->load->view('setup/tariff/motor/_form_edit_inline', [
                'form_elements'     => $partial_form_elements,
                'form_record'       => NULL,
                'inline_grid_width' => 'col-sm-6 col-md-4',
                '__box_id'          => $__box_id,
                '__show_remove'     => FALSE
            ]);
        }
        ?>
        <div class="box-footer bg-info">
            <a href="#" class="btn bg-teal" onclick="__duplicate_configs('#<?php echo $__box_id?>', this)">Add More</a>
        </div>
    </div>

    <div class="box box-solid box-bordered box-config">
        <div class="box-header with-border bg-teal">
            <h4 class="box-title">Trolly/Trailer Tariff (ट्रेलर/ट्रलीको बीमाशुल्क दर)</h4>
            <a href="#" class="pull-right btn btn-default btn-sm" onclick="__zerofill('.box-config', this)">Fill Zero</a>
        </div>
        <div class="box-body bg-gray-light">
            <?php
            /**
             * Load Form Components
             */
            $trolly_tariff = $record->trolly_tariff ? json_decode($record->trolly_tariff, TRUE) : NULL;

            $partial_form_elements = $form_elements['trolly_tariff'];
            if($trolly_tariff)
            {
                foreach($trolly_tariff as $key=>$value)
                {
                    // Field Name
                    $field_name = 'trolly_tariff['.$key . ']';
                    // Search for the Keys
                    $index = array_search($field_name, array_column($partial_form_elements, 'field'));
                    $partial_form_elements[$index]['_default'] = $value;
                }
            }

            $this->load->view('templates/_common/_form_components_inline', [
                'form_elements'     => $partial_form_elements,
                'form_record'       => NULL,
                'inline_grid_width' => 'col-sm-6 col-md-4'
            ]);
            ?>
        </div>
    </div>

    <div class="box box-solid box-bordered box-config">
        <div class="box-header with-border bg-teal">
          <h4 class="box-title">Motor Accident Premium (अतिरिक्त बीमाशुल्कदर - १. मोटर बीमालेख अन्तर्गतको दुर्घटना बीमाको बीमाशुल्क दर)</h4>
          <a href="#" class="pull-right btn btn-default btn-sm" onclick="__zerofill('.box-config', this)">Fill Zero</a>
        </div>
        <div class="box-body bg-gray-light">
            <?php
            /**
             * Load Form Components
             */
            $accident_premium = $record->accident_premium ? json_decode($record->accident_premium, TRUE) : NULL;

            $partial_form_elements = $form_elements['accident_premium'];
            if($accident_premium)
            {
                foreach($accident_premium as $key=>$value)
                {
                    // Field Name
                    $field_name = 'accident_premium['.$key . ']';
                    // Search for the Keys
                    $index = array_search($field_name, array_column($partial_form_elements, 'field'));
                    $partial_form_elements[$index]['_default'] = $value;
                }
            }

            $this->load->view('templates/_common/_form_components_inline', [
                'form_elements'     => $partial_form_elements,
                'form_record'       => NULL,
                'inline_grid_width' => 'col-sm-6 col-md-4'
            ]);
            ?>
        </div>
    </div>

    <div class="box box-solid box-bordered box-config">
        <div class="box-header with-border bg-teal">
          <h4 class="box-title">Risk Group (Pool) (अतिरिक्त बीमाशुल्कदर - २. जोखिम समूहको बीमाशुल्क दर)</h4>
        </div>
        <div class="box-body bg-gray-light">
            <?php
            /**
             * Load Form Components
             */
            $riks_group = $record->riks_group ? json_decode($record->riks_group, TRUE) : NULL;

            $partial_form_elements = $form_elements['riks_group'];
            if($riks_group)
            {
                foreach($riks_group as $key=>$value)
                {
                    // Field Name
                    $field_name = 'riks_group['.$key . ']';
                    // Search for the Keys
                    $index = array_search($field_name, array_column($partial_form_elements, 'field'));
                    $partial_form_elements[$index]['_default'] = $value;
                }
            }
            $this->load->view('templates/_common/_form_components_inline', [
                'form_elements'     => $partial_form_elements,
                'form_record'       => null,
                'inline_grid_width' => 'col-sm-6 col-md-4'
            ]);
            ?>
        </div>
    </div>

    <div class="box box-solid box-bordered box-config">
        <div class="box-header with-border bg-teal">
          <h4 class="box-title">Towing Premium Amount(Private &amp; Commercial Vehicles) (अतिरिक्त बीमाशुल्कदर - ३. दुर्घटना भई सडकबाट बाहिर गएको सवारी साधनलाई सडकसम्म निकाल्दा लाग्ने खर्चको बीमाशुल्क दर)</h4>
          <a href="#" class="pull-right btn btn-default btn-sm" onclick="__zerofill('.box-config', this)">Fill Zero</a>
        </div>
        <div class="box-body bg-gray-light">
            <?php
            /**
             * Load Form Components
             */
            $pramt_towing = $form_elements['pramt_towing'];
            $this->load->view('templates/_common/_form_components_inline', [
                    'form_elements'     => $pramt_towing,
                    'form_record'       => $record,
                    'inline_grid_width' => 'col-sm-6 col-md-4'
            ]);
            ?>
        </div>
    </div>

    <div class="box box-solid box-bordered box-config">
        <div class="box-header with-border bg-teal">
          <h4 class="box-title">Accident Covered Tariff (प्रति व्यक्ति बिमांक)</h4>
        </div>
        <div class="box-body bg-gray-light">
            <?php
            /**
             * Load Form Components
             */
            $insured_value_tariff = $record->insured_value_tariff ? json_decode($record->insured_value_tariff, TRUE) : NULL;

            $partial_form_elements = $form_elements['insured_value_tariff'];
            if($insured_value_tariff)
            {
                foreach($insured_value_tariff as $key=>$value)
                {
                    // Field Name
                    $field_name = 'insured_value_tariff[' . $key . ']';
                    // Search for the Keys
                    $index = array_search($field_name, array_column($partial_form_elements, 'field'));
                    $partial_form_elements[$index]['_default'] = $value;
                }
            }
            $this->load->view('templates/_common/_form_components_inline', [
                'form_elements'     => $partial_form_elements,
                'form_record'       => null,
                'inline_grid_width' => 'col-sm-6 col-md-4'
            ]);
            ?>
        </div>
    </div>

    <button type="submit" class="hide">Submit</button>
<?php echo form_close();?>

<script type="text/javascript">

    /**
     * Duplicate Configuration Group
     */
    function __zerofill(src, a)
    {
        var $box = $(a).closest(src);
        $('input', $box).val(0);
    }

    /**
     * Duplicate Configuration Group
     */
    function __duplicate_configs(src, a)
    {
        var $src = $(src),
        html = '<div class="box-body bg-gray-light box-removable">' +
                    '<div class="box box-solid box-bordered">' +
                        '<div class="box-body">' +
                            $src.html() +
                        '</div>' +
                        '<div class="box-footer text-right">' +
                            '<a href="#" class="btn btn-danger btn-sm" onclick="$(this).closest(\'.box-removable\').fadeOut(\'fast\', function(){$(this).remove()})">Remove</a>' +
                        '</div>' +
                    '</div>' +
                '</div>';

        $(html).insertBefore($(a).closest('.box-footer'));
    }
</script>
