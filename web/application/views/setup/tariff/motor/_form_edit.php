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
td .form-group {margin-bottom: 0px;}
</style>
<?php echo form_open( $this->uri->uri_string(),
                        [
                            'class' => 'form-iqb-general',
                            'data-pc' => '.bootbox-body' // parent container ID
                        ],
                        // Hidden Fields
                        isset($record) ? ['id' => $record->id] : []); ?>

    <div class="box box-solid box-bordered">
        <div class="box-header with-border">
            <h4 class="box-title">Portfolio Summary</h4>
        </div>
        <table class="table table-responsive table-condensed">
            <tbody>
                <tr>
                    <th>Ownership</th>
                    <td><?php echo _OBJ_MOTOR_ownership_dropdown(FALSE)[$record->ownership]?></td>
                </tr>
                <tr>
                    <th>Portfolio</th>
                    <td><?php echo $record->portfolio_name_en;?></td>
                </tr>
                <?php if ($record->cvc_type):?>
                    <tr>
                        <th>CVC Type</th>
                        <td><?php echo _OBJ_MOTOR_CVC_type_dropdown(FALSE)[$record->cvc_type] ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="box box-solid box-bordered">
        <div class="box-header with-border">
            <h4 class="box-title">Basic Information</h4>
        </div>
        <div class="box-body form-horizontal">
            <div class="row">
                <div class="col-md-6">
                    <?php
                    /**
                     * Portfolio Specific Premium Fields
                     */
                    $default_form_elements = $form_elements['defaults'];
                    $this->load->view('templates/_common/_form_components_horz', [
                            'form_elements'     => $default_form_elements,
                            'form_record'       => $record
                    ]);
                    ?>
                </div>
            </div>
        </div>
    </div>


    <div class="box box-solid box-bordered" style="overflow-x: scroll;">
        <div class="box-header with-border">
            <h4 class="box-title">Tariff Details</h4>
        </div>
        <table class="table table-bordered table-condensed no-margin">
            <thead>
                <tr>
                    <?php
                    $section_elements   = $form_elements['tariff'];
                    $tariff             = $record->tariff ? json_decode($record->tariff) : [];
                    foreach($section_elements as $elem): ?>
                        <th>
                            <?php echo $elem['label'] . field_compulsary_text($elem['_required']) ?>
                        </th>
                    <?php endforeach; ?>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody class="form-inline">
                <?php
                    if($tariff):
                        for ($i=0; $i < count($tariff); $i++):?>
                        <tr <?php echo $i == 0 ? 'id="__tariff_row"' : '' ?>>
                            <?php foreach($section_elements as $elem):?>
                                <td>
                                    <?php
                                    /**
                                     * Load Single Element
                                     */
                                    $single_tariff      = $tariff[$i];
                                    $elem['_default']    = $single_tariff->{$elem['_key']} ?? '';
                                    $elem['_value']      = $elem['_default'];
                                    $this->load->view('templates/_common/_form_components_inline', [
                                        'form_elements' => [$elem],
                                        'form_record'   => NULL
                                    ]);
                                    ?>
                                </td>
                            <?php
                            endforeach;
                            if($i == 0):?>
                                <td>&nbsp;</td>
                            <?php else:?>
                                <td width="10%" align="right"><a href="#" class="btn btn-danger btn-sm" onclick='$(this).closest("tr").remove()'>Remove</a></td>
                            <?php endif;?>
                        </tr>
                    <?php
                        endfor;
                    else:?>
                        <tr id="__tariff_row">
                            <?php foreach($section_elements as $elem):?>
                                <td>
                                    <?php
                                    /**
                                     * Load Single Element
                                     */
                                    $this->load->view('templates/_common/_form_components_inline', [
                                        'form_elements' => [$elem],
                                        'form_record'   => NULL
                                    ]);
                                    ?>
                                </td>
                            <?php endforeach?>
                            <td>&nbsp;</td>
                        </tr>
                    <?php endif;?>
            </tbody>
        </table>
        <div class="box-footer bg-info">
            <a href="#" class="btn bg-teal" onclick="__duplicate_tr('#__tariff_row', this)">Add More</a>
        </div>
    </div>

    <div class="box box-solid box-bordered">
        <div class="box-header with-border">
            <h4 class="box-title">No Claim Discount</h4>
        </div>
        <table class="table table-bordered table-condensed no-margin">
            <thead>
                <tr>
                    <?php
                    $section_elements   = $form_elements['no_claim_discount'];
                    $no_claim_discount  = $record->no_claim_discount ? json_decode($record->no_claim_discount) : [];
                    foreach($section_elements as $elem): ?>
                        <th>
                            <?php echo $elem['label'] . field_compulsary_text($elem['_required']) ?>
                        </th>
                    <?php endforeach; ?>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    if($no_claim_discount):
                        for ($i=0; $i < count($no_claim_discount); $i++):?>
                        <tr <?php echo $i == 0 ? 'id="__no_claim_discount_row"' : '' ?>>
                            <?php foreach($section_elements as $elem):?>
                                <td>
                                    <?php
                                    /**
                                     * Load Single Element
                                     */
                                    $single_item        = $no_claim_discount[$i];
                                    $elem['_default']   = $single_item->{$elem['_key']} ?? '';
                                    $elem['_value']     = $elem['_default'];
                                    $this->load->view('templates/_common/_form_components_inline', [
                                        'form_elements' => [$elem],
                                        'form_record'   => NULL
                                    ]);
                                    ?>
                                </td>
                            <?php
                            endforeach;
                            if($i == 0):?>
                                <td>&nbsp;</td>
                            <?php else:?>
                                <td width="10%" align="right"><a href="#" class="btn btn-danger btn-sm" onclick='$(this).closest("tr").remove()'>Remove</a></td>
                            <?php endif;?>
                        </tr>
                    <?php
                        endfor;
                    else:?>
                        <tr id="__no_claim_discount_row">
                            <?php foreach($section_elements as $elem):?>
                                <td>
                                    <?php
                                    /**
                                     * Load Single Element
                                     */
                                    $this->load->view('templates/_common/_form_components_inline', [
                                        'form_elements' => [$elem],
                                        'form_record'   => NULL
                                    ]);
                                    ?>
                                </td>
                            <?php endforeach?>
                            <td>&nbsp;</td>
                        </tr>
                    <?php endif;?>
            </tbody>
        </table>
        <div class="box-footer bg-info">
            <a href="#" class="btn bg-teal" onclick="__duplicate_tr('#__no_claim_discount_row', this)">Add More</a>
        </div>
    </div>

    <div class="box box-solid box-bordered">
        <div class="box-header with-border">
            <h4 class="box-title">Voluntary Excess Discount</h4>
        </div>
        <table class="table table-bordered table-condensed no-margin">
            <thead>
                <tr>
                    <?php
                    $section_elements   = $form_elements['dr_voluntary_excess'];
                    $dr_voluntary_excess  = $record->dr_voluntary_excess ? json_decode($record->dr_voluntary_excess) : [];
                    foreach($section_elements as $elem): ?>
                        <th>
                            <?php echo $elem['label'] . field_compulsary_text($elem['_required']) ?>
                        </th>
                    <?php endforeach; ?>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    if($dr_voluntary_excess):
                        for ($i=0; $i < count($dr_voluntary_excess); $i++):?>
                        <tr <?php echo $i == 0 ? 'id="__dr_voluntary_excess_row"' : '' ?>>
                            <?php foreach($section_elements as $elem):?>
                                <td>
                                    <?php
                                    /**
                                     * Load Single Element
                                     */
                                    $single_item        = $dr_voluntary_excess[$i];
                                    $elem['_default']   = $single_item->{$elem['_key']} ?? '';
                                    $elem['_value']     = $elem['_default'];
                                    $this->load->view('templates/_common/_form_components_inline', [
                                        'form_elements' => [$elem],
                                        'form_record'   => NULL
                                    ]);
                                    ?>
                                </td>
                            <?php
                            endforeach;
                            if($i == 0):?>
                                <td>&nbsp;</td>
                            <?php else:?>
                                <td width="10%" align="right"><a href="#" class="btn btn-danger btn-sm" onclick='$(this).closest("tr").remove()'>Remove</a></td>
                            <?php endif;?>
                        </tr>
                    <?php
                        endfor;
                    else:?>
                        <tr id="__dr_voluntary_excess_row">
                            <?php foreach($section_elements as $elem):?>
                                <td>
                                    <?php
                                    /**
                                     * Load Single Element
                                     */
                                    $this->load->view('templates/_common/_form_components_inline', [
                                        'form_elements' => [$elem],
                                        'form_record'   => NULL
                                    ]);
                                    ?>
                                </td>
                            <?php endforeach?>
                            <td>&nbsp;</td>
                        </tr>
                    <?php endif;?>
            </tbody>
        </table>
        <div class="box-footer bg-info">
            <a href="#" class="btn bg-teal" onclick="__duplicate_tr('#__dr_voluntary_excess_row', this)">Add More</a>
        </div>
    </div>

    <div class="box box-solid box-bordered">
        <div class="box-header with-border">
            <h4 class="box-title">Compulsory Excess Amount</h4>
        </div>
        <table class="table table-bordered table-condensed no-margin">
            <thead>
                <tr>
                    <?php
                    $section_elements   = $form_elements['pramt_compulsory_excess'];
                    $pramt_compulsory_excess  = $record->pramt_compulsory_excess ? json_decode($record->pramt_compulsory_excess) : [];
                    foreach($section_elements as $elem): ?>
                        <th>
                            <?php echo $elem['label'] . field_compulsary_text($elem['_required']) ?>
                        </th>
                    <?php endforeach; ?>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    if($pramt_compulsory_excess):
                        for ($i=0; $i < count($pramt_compulsory_excess); $i++):?>
                        <tr <?php echo $i == 0 ? 'id="__pramt_compulsory_excess_row"' : '' ?>>
                            <?php foreach($section_elements as $elem):?>
                                <td>
                                    <?php
                                    /**
                                     * Load Single Element
                                     */
                                    $single_item        = $pramt_compulsory_excess[$i];
                                    $elem['_default']   = $single_item->{$elem['_key']} ?? '';
                                    $elem['_value']     = $elem['_default'];
                                    $this->load->view('templates/_common/_form_components_inline', [
                                        'form_elements' => [$elem],
                                        'form_record'   => NULL
                                    ]);
                                    ?>
                                </td>
                            <?php
                            endforeach;
                            if($i == 0):?>
                                <td>&nbsp;</td>
                            <?php else:?>
                                <td width="10%" align="right"><a href="#" class="btn btn-danger btn-sm" onclick='$(this).closest("tr").remove()'>Remove</a></td>
                            <?php endif;?>
                        </tr>
                    <?php
                        endfor;
                    else:?>
                        <tr id="__pramt_compulsory_excess_row">
                            <?php foreach($section_elements as $elem):?>
                                <td>
                                    <?php
                                    /**
                                     * Load Single Element
                                     */
                                    $this->load->view('templates/_common/_form_components_inline', [
                                        'form_elements' => [$elem],
                                        'form_record'   => NULL
                                    ]);
                                    ?>
                                </td>
                            <?php endforeach?>
                            <td>&nbsp;</td>
                        </tr>
                    <?php endif;?>
            </tbody>
        </table>
        <div class="box-footer bg-info">
            <a href="#" class="btn bg-teal" onclick="__duplicate_tr('#__pramt_compulsory_excess_row', this)">Add More</a>
        </div>
    </div>

    <div class="box box-solid box-bordered box-config">
        <div class="box-header with-border">
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
        <div class="box-header with-border">
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

    <div class="box box-solid box-bordered box-config">
        <div class="box-header with-border">
          <h4 class="box-title">Pool Risk Rate (अतिरिक्त बीमाशुल्कदर - २. जोखिम समूहको बीमाशुल्क दर)</h4>
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

    <?php
    $section_elements = $form_elements['dr_mcy_disabled_friendly'] ?? NULL;
    if($section_elements):
    ?>
        <div class="box box-solid box-bordered box-config">
            <div class="box-header with-border">
                <h4 class="box-title">Disable Friendly Discount (Motorcycle Only)</h4>
                <a href="#" class="pull-right btn btn-default btn-sm" onclick="__zerofill('.box-config', this)">Fill Zero</a>
            </div>
            <div class="box-body">
                <?php
                /**
                 * Load Form Components
                 */
                $this->load->view('templates/_common/_form_components_inline', [
                        'form_elements'     => $section_elements,
                        'form_record'       => $record,
                        'inline_grid_width' => 'col-sm-6 col-md-4'
                ]);
                ?>
            </div>

        </div>
    <?php endif; ?>

    <?php
    $section_elements = $form_elements['rate_pvc_on_hire'] ?? NULL;
    if($section_elements):
    ?>
            <div class="box box-solid box-bordered box-config">
                <div class="box-header with-border">
                    <h4 class="box-title">Private Hire (Private Vehicle Only)</h4>
                    <a href="#" class="pull-right btn btn-default btn-sm" onclick="__zerofill('.box-config', this)">Fill Zero</a>
                </div>
                <div class="box-body bg-gray-light">
                    <?php
                    /**
                     * Load Form Components
                     */
                    $this->load->view('templates/_common/_form_components_inline', [
                            'form_elements'     => $section_elements,
                            'form_record'       => $record,
                            'inline_grid_width' => 'col-sm-6 col-md-4'
                    ]);
                    ?>
                </div>
                <div class="box-footer">
                    <p class="small text-warning">कुनै व्यक्ति वा संस्थाको निजी सवारी साधन अर्को व्यक्ति वा संस्थाको निजी प्रयोगको लागि भाडा (प्राइभेट हायर) मा दिइएको भएमा उल्लिखित बीमाशुल्कदरमा १०% थप गरी दर कायम गर्नु पर्नेछ ।</p>
                </div>
            </div>
    <?php endif; ?>

    <?php
    $section_elements = $form_elements['dr_cvc_on_personal_use'] ?? NULL;
    if($section_elements):
    ?>
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
              <h4 class="box-title">Discount on Personal Use (Commercial Vehicle Only)</h4>
              <a href="#" class="pull-right btn btn-default btn-sm" onclick="__zerofill('.box-config', this)">Fill Zero</a>
            </div>
            <div class="box-body bg-gray-light">
                <?php
                /**
                 * Load Form Components
                 */
                $this->load->view('templates/_common/_form_components_inline', [
                        'form_elements'     => $section_elements,
                        'form_record'       => $record,
                        'inline_grid_width' => 'col-sm-6 col-md-4'
                ]);
                ?>
            </div>
            <div class="box-footer">
                <p class="small text-warning">निजी प्रयोेजनको लागि प्रयोग गर्ने सवारी साधनको ब्यापक बीमा गर्दा शुरु बीमाशुल्कको २५ प्रतिशत छुटहुनेछ ।</p>
            </div>
        </div>
    <?php endif; ?>


    <?php
    $section_elements = $form_elements['trolly_tariff'] ?? NULL;
    if($section_elements):
    ?>
        <div class="box box-solid box-bordered box-config">
            <div class="box-header with-border">
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
    <?php endif; ?>


    <?php
    $section_elements = $form_elements['pramt_towing'] ?? NULL;
    if($section_elements):
    ?>
        <div class="box box-solid box-bordered box-config">
            <div class="box-header with-border">
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
    <?php endif; ?>



    <button type="submit" class="hide">Submit</button>
<?php echo form_close();?>

<script type="text/javascript">
    /**
     * Duplicate Treaty Distribution Row
     */
    function __duplicate_tr(src, a)
    {
        var $src = $(src),
            $box = $src.closest('tbody'),
            html = $src.html(),
            $row  = $('<tr></tr>');

        $row.html(html);

        // remove last blank td
        $row.find('td:last').remove();

        // Add Remover Column
        $row.append('<td width="10%" align="right"><a href="#" class="btn btn-danger btn-sm" onclick=\'$(this).closest("tr").remove();\'>Remove</a></td>');

        // Append to table body
        $box.append($row);
    }
</script>

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
