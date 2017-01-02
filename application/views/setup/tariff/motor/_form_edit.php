<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Department
 */
$close_anchor = '<div class="row"><div class="col-xs-12 text-right">' .
                         '<a href="#" onclick=\'$(this).closest(".box-body").remove()\'>Remove</a>' .
                     '</div></div>' .
                 '</div>';
?>
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
            <p class="form-control-static"><?php echo _PO_MOTOR_ownership_dropdown(FALSE)[$record->ownership]?></p>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Sub-Portfolio</label>
            <div class="col-sm-10">
            <p class="form-control-static"><?php echo _PO_MOTOR_sub_portfolio_dropdown(FALSE)[$record->sub_portfolio]?></p>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">CVC Type</label>
            <div class="col-sm-10">
            <p class="form-control-static"><?php echo $record->cvc_type ? _PO_MOTOR_CVC_type_dropdown(FALSE)[$record->cvc_type] : '-'?></p>
            </div>
        </div>
    </div>


    <div class="box box-solid box-bordered">
        <div class="box-header with-border">
          <h4 class="box-title">Tariff Details</h4>
        </div>
        <?php
        /**
         * Load Form Components
         */
        $tariff = $record->tariff ? json_decode($record->tariff, TRUE) : NULL;

        $partial_form_elements = $form_elements['tariff'];
        $i = 0;
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

                if($i > 1)
                {
                    echo '<hr/><div class="box-body">';
                }
                else
                {
                    echo '<div class="box-body" id="_tariff-box">';
                }

                    /**
                     * Load These Elements
                     */
                    $this->load->view('templates/_common/_form_components_inline', [
                        'form_elements' => $partial_form_elements,
                        'form_record'   => NULL,
                        'inline_grid_width' => 'col-sm-6 col-md-4'
                    ]);

                if($i > 1)
                {
                    echo $close_anchor;
                }
                else{
                    echo '</div>';
                }
            }
        }
        else
        {
            echo '<div class="box-body" id="_tariff-box">';

                $this->load->view('templates/_common/_form_components_inline', [
                    'form_elements' => $partial_form_elements,
                    'form_record'   => NULL,
                    'inline_grid_width' => 'col-sm-6 col-md-4'
                ]);
            echo '</div>';
        }
        ?>
        <div class="box-footer bg-gray-light">
            <a href="#" onclick="duplicate('#_tariff-box', 'box-body')">Add More</a>
        </div>
    </div>

    <div class="box box-solid box-bordered">
        <div class="box-header with-border">
          <h4 class="box-title">Disable Friendly Discount (Motorcycle Only)t</h4>
        </div>
        <div class="box-body">
            <?php
            /**
             * Load Form Components
             */
            $dr_disabled_friendly = $form_elements['dr_disabled_friendly'];
            $this->load->view('templates/_common/_form_components_inline', [
                'form_elements' => $dr_disabled_friendly,
                'form_record'   => $record,
                'inline_grid_width' => 'col-sm-6 col-md-4'
            ]);
            ?>
        </div>
    </div>

    <div class="box box-solid box-bordered">
        <div class="box-header with-border">
          <h4 class="box-title">No Claim Discount</h4>
        </div>

        <?php
        /**
         * Load Form Components
         */
        $no_claim_discount = $record->no_claim_discount ? json_decode($record->no_claim_discount, TRUE) : NULL;

        $partial_form_elements = $form_elements['no_claim_discount'];
        $i = 0;
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

                if($i > 1)
                {
                    echo '<hr/><div class="box-body">';
                }
                else
                {
                    echo '<div class="box-body" id="_no-claim-discount-box">';
                }

                    /**
                     * Load These Elements
                     */
                    $this->load->view('templates/_common/_form_components_inline', [
                        'form_elements' => $partial_form_elements,
                        'form_record'   => NULL,
                        'inline_grid_width' => 'col-sm-6 col-md-4'
                    ]);

                if($i > 1)
                {
                    echo $close_anchor;
                }
                else{
                    echo '</div>';
                }
            }
        }
        else
        {
            echo '<div class="box-body" id="_no-claim-discount-box">';
                $this->load->view('templates/_common/_form_components_inline', [
                    'form_elements' => $partial_form_elements,
                    'form_record'   => NULL,
                    'inline_grid_width' => 'col-sm-6 col-md-4'
                ]);
            echo '</div>';
        }
        ?>
        <div class="box-footer bg-gray-light">
            <a href="#" onclick="duplicate('#_no-claim-discount-box', 'box-body')">Add More</a>
        </div>
    </div>

    <div class="box box-solid box-bordered">
        <div class="box-header with-border">
          <h4 class="box-title">Voluntary Excess Discount</h4>
        </div>
        <?php
        /**
         * Load Form Components
         */
        $dr_voluntary_excess = $record->dr_voluntary_excess ? json_decode($record->dr_voluntary_excess, TRUE) : NULL;

        $partial_form_elements = $form_elements['dr_voluntary_excess'];
        $i = 0;
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

                if($i > 1)
                {
                    echo '<hr/><div class="box-body">';
                }
                else
                {
                    echo '<div class="box-body" id="_no-dr-voluntary-excess">';
                }

                    /**
                     * Load These Elements
                     */
                    $this->load->view('templates/_common/_form_components_inline', [
                        'form_elements' => $partial_form_elements,
                        'form_record'   => NULL,
                        'inline_grid_width' => 'col-sm-6 col-md-4'
                    ]);

                if($i > 1)
                {
                    echo $close_anchor;
                }
                else{
                    echo '</div>';
                }
            }
        }
        else
        {
            echo '<div class="box-body" id="_no-dr-voluntary-excess">';
                $this->load->view('templates/_common/_form_components_inline', [
                    'form_elements' => $partial_form_elements,
                    'form_record'   => NULL,
                    'inline_grid_width' => 'col-sm-6 col-md-4'
                ]);
            echo '</div>';
        }
        ?>

        <div class="box-footer bg-gray-light">
            <a href="#" onclick="duplicate('#_no-dr-voluntary-excess', 'box-body')">Add More</a>
        </div>
    </div>

    <div class="box box-solid box-bordered">
        <div class="box-header with-border">
          <h4 class="box-title">Compulsory Excess Amount</h4>
        </div>
        <?php
        /**
         * Load Form Components
         */
        $pramt_compulsory_excess = $record->pramt_compulsory_excess ? json_decode($record->pramt_compulsory_excess, TRUE) : NULL;

        $partial_form_elements = $form_elements['pramt_compulsory_excess'];
        $i = 0;
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

                if($i > 1)
                {
                    echo '<hr/><div class="box-body">';
                }
                else
                {
                    echo '<div class="box-body" id="_no-prmt-compulsory-excess">';
                }

                    /**
                     * Load These Elements
                     */
                    $this->load->view('templates/_common/_form_components_inline', [
                        'form_elements' => $partial_form_elements,
                        'form_record'   => NULL,
                        'inline_grid_width' => 'col-sm-6 col-md-4'
                    ]);

                if($i > 1)
                {
                    echo $close_anchor;
                }
                else{
                    echo '</div>';
                }
            }
        }
        else
        {
            echo '<div class="box-body" id="_no-prmt-compulsory-excess">';
                $this->load->view('templates/_common/_form_components_inline', [
                    'form_elements' => $partial_form_elements,
                    'form_record'   => NULL,
                    'inline_grid_width' => 'col-sm-6 col-md-4'
                ]);
            echo '</div>';
        }
        ?>
        <div class="box-footer bg-gray-light">
            <a href="#" onclick="duplicate('#_no-prmt-compulsory-excess', 'box-body')">Add More</a>
        </div>
    </div>

    <div class="box box-solid box-bordered">
        <div class="box-header with-border">
          <h4 class="box-title">Additional Premium</h4>
        </div>
        <div class="box-body">
            <?php
            /**
             * Load Form Components
             */
            $additional_premium = $record->additional_premium ? json_decode($record->additional_premium, TRUE) : NULL;

            $partial_form_elements = $form_elements['additional_premium'];
            if($additional_premium)
            {
                foreach($additional_premium as $key=>$value)
                {
                    // Field Name
                    $field_name = 'additional_premium['.$key . ']';
                    // Search for the Keys
                    $index = array_search($field_name, array_column($partial_form_elements, 'field'));
                    $partial_form_elements[$index]['_default'] = $value;
                }
            }

            $this->load->view('templates/_common/_form_components_inline', [
                'form_elements' => $partial_form_elements,
                'form_record'   => NULL,
                'inline_grid_width' => 'col-sm-6 col-md-4'
            ]);
            ?>
        </div>
    </div>

    <div class="box box-solid box-bordered">
        <div class="box-header with-border">
          <h4 class="box-title">Risk Group</h4>
        </div>
        <div class="box-body">
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
                'form_elements' => $partial_form_elements,
                'form_record'   => null,
                'inline_grid_width' => 'col-sm-6 col-md-4'
            ]);
            ?>
        </div>
    </div>



    <button type="submit" class="hide">Submit</button>
<?php echo form_close();?>
<script type="text/javascript">
    function duplicate(src, box_classes)
    {
        var html = '<hr/>' + $(src).html() + '<div class="row"><div class="col-xs-12 text-right">' +
                                        '<a href="#" onclick=\'$(this).closest(".box-body").remove()\'>Remove</a>' +
                                    '</div></div>';
         $("<div/>")   // creates a div element
             // .attr("id", "someID")  // adds the id
             .addClass(box_classes)   // add a class
             .html(html)
             .insertAfter(src);
    }
</script>
