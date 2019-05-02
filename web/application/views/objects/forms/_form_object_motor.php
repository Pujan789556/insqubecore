<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Object - Motor
 */
?>
<link rel="stylesheet" href="<?php echo THEME_URL; ?>plugins/typeahead/typeahead.css">
<div class="row">
    <div class="col-md-6">
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">Vehicle Common Information</h4>
            </div>
            <div class="box-body form-horizontal">
                <?php
                /**
                 * Vehicle Information
                 */
                $vehicle_elements = $form_elements['vehicle-common'];
                $this->load->view('templates/_common/_form_components_horz', [
                    'form_elements' => $vehicle_elements,
                    'form_record'   => $record
                ]);
                ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">Vehicle Registration Information</h4>
            </div>
            <div class="box-body form-horizontal">
                <?php
                /**
                 * Vehicle Information
                 */
                $vehicle_elements = $form_elements['vehicle-registration'];
                $this->load->view('templates/_common/_form_components_horz', [
                    'form_elements' => $vehicle_elements,
                    'form_record'   => $record
                ]);
                ?>
            </div>
        </div>

        <?php
        /**
         * CVC Types - CVC
         */
        $section_elements = $form_elements['vehicle-cvc'] ?? NULL;
        if($section_elements):
        ?>
            <div class="box box-solid box-bordered">
                <div class="box-header with-border">
                    <h4 class="box-title">Commercial Vehicle Types</h4>
                </div>
                <div class="box-body form-horizontal">
                    <?php
                    $this->load->view('templates/_common/_form_components_horz', [
                        'form_elements' => $section_elements,
                        'form_record'   => $record,
                        'grid_label'        => 'col-sm-4',
                        'grid_form_control' => 'col-sm-8'
                    ]);
                    ?>
                </div>
            </div>
        <?php endif ?>

        <?php
        /**
         * Seating Capacity - PVC, CVC
         */
        $section_elements = $form_elements['seating-capcity'] ?? NULL;
        if($section_elements):
        ?>
            <div class="box box-solid box-bordered">
                <div class="box-header with-border">
                    <h4 class="box-title">Seating Capacity</h4>
                </div>
                <div class="box-body form-horizontal">
                    <?php
                    $this->load->view('templates/_common/_form_components_horz', [
                        'form_elements' => $section_elements,
                        'form_record'   => $record,
                        'grid_label' => 'col-sm-4',
                        'grid_form_control' => 'col-sm-8'
                    ]);
                    ?>
                </div>
            </div>
        <?php endif ?>

        <?php
        /**
         * Vehicle Specific Information - Staff Count (PVC, CVC)
         */
        $staff_elements = $form_elements['staff'] ?? NULL;
        if($staff_elements):
        ?>
            <div class="box box-solid box-bordered" id="__staff-box">
                <div class="box-header with-border">
                    <h4 class="box-title">Vehicle Specific Information - Staff Count</h4>
                </div>
                <div class="box-body form-horizontal">
                    <p class="help-block"><i class="fa fa-info-circle"></i> Please supply staff count if this is commercial vehicle.</p>
                    <?php
                    /**
                     * Vehicle Information
                     */
                    $this->load->view('templates/_common/_form_components_horz', [
                        'form_elements' => $staff_elements,
                        'form_record'   => $record,
                        'grid_label'        => 'col-sm-4',
                        'grid_form_control' => 'col-sm-8'
                    ]);
                    ?>
                </div>
            </div>
        <?php endif ?>


        <?php
        /**
         * Carrying Capacity
         */
        $section_elements = $form_elements['carrying-capcity'] ?? NULL;
        if($section_elements):
        ?>
            <div class="box box-solid box-bordered">
                <div class="box-header with-border">
                    <h4 class="box-title">Carrying Capacity</h4>
                </div>
                <div class="box-body form-horizontal">
                    <?php
                    $this->load->view('templates/_common/_form_components_horz', [
                        'form_elements' => $section_elements,
                        'form_record'   => $record,
                        'grid_label'        => 'col-sm-4',
                        'grid_form_control' => 'col-sm-8'
                    ]);
                    ?>
                </div>
            </div>
        <?php endif ?>

        <?php
        /**
         * Vehicle Specific Information - Trailer Info
         */
        $trailer_elements = $form_elements['trailer'] ?? NULL;
        if($trailer_elements):
        ?>
            <div class="box box-solid box-bordered" id="__trailer-box">
                <div class="box-header with-border">
                    <h4 class="box-title">Vehicle Specific Information - Trailer/Trolly Information</h4>
                </div>
                <div class="box-body form-horizontal">
                    <p class="help-block"><i class="fa fa-info-circle"></i> Please supply trailer/trolly price if this vehicle has any.</p>
                    <?php
                    /**
                     * Vehicle Information
                     */

                    $this->load->view('templates/_common/_form_components_horz', [
                        'form_elements' => $trailer_elements,
                        'form_record'   => $record
                    ]);
                    ?>
                </div>
            </div>
        <?php endif ?>

        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">Vehicle Custom Information</h4>
            </div>
            <div class="box-body form-horizontal">
                <?php
                /**
                 * Vehicle Information
                 */
                $vehicle_elements = $form_elements['vehicle-custom'];
                $this->load->view('templates/_common/_form_components_horz', [
                    'form_elements' => $vehicle_elements,
                    'form_record'   => $record
                ]);
                ?>
            </div>
        </div>

    </div>
</div>



<script type="text/javascript">
    /**
     * Typeahead Lookup
     */
    $.getScript( "<?php echo THEME_URL; ?>plugins/typeahead/typeahead.bundle.min.js", function( data, textStatus, jqxhr ) {
        var vehicleRegNumberSuggestions = new Bloodhound({
            datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            // prefetch: '../data/films/post_1960.json',
            remote: {
                url: '<?php echo site_url($this->data['_url_base'] . '/motor_lookup_reg_no_prefix') ?>/%QUERY',
                wildcard: '%QUERY'
            }
        });

        $('#_motor-vechicle-reg_no_prefix').typeahead(null, {
            // name: 'best-pictures',
            highlight : true,
            limit: 19,
            display: 'value',
            source: vehicleRegNumberSuggestions
        });

        // Select Bind - Focus Reg No Box
        $('#_motor-vechicle-reg_no_prefix').bind('typeahead:select', function(ev, suggestion) {
            $('#_motor-vechicle-reg_no').focus();
        });
    });
</script>

<script type="text/javascript">
    function _po_to_be_intimated(d, et){
        var $this  = $(d), // the source element
            $boxes = $('input.vehicle-reg-input').closest('.form-group');

        if(et === 'ifChecked' || $this.prop('checked') === true){

            // TO be intimated
            $('input.vehicle-reg-input').val('TO BE INTIMATED');

            // Date Blank
            $('input#_motor-vechicle-reg_date').val('');

            // Hide the boxes
            $boxes.hide(500);


        }else if(typeof et === 'undefined' || et === 'ifUnchecked'){
            $boxes.show(500);

            // If user unchecks, empty these fields
            if(et === 'ifUnchecked'){
                // empty all fields
                $('input.vehicle-reg-input').val('');
                // focus the first field
                $('input.vehicle-reg-input:first').focus();
            }
        }
    }

    (function($){
        // To Be Intimated Toggle
        _po_to_be_intimated('#_motor-vehicle-to-be-intimated');
        $('#_motor-vehicle-to-be-intimated').on('ifChecked ifUnchecked', function(event){
            _po_to_be_intimated(this, event.type);
        });

    })(jQuery);
</script>
