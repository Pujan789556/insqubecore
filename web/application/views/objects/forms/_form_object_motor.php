<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Object - Motor
 */
?>
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
    </div>
</div>


<script type="text/javascript">

    function _po_to_be_intimated(d, et){
        var $this       = $(d), // the source element
        $reg_box        = $('#_motor-registration-no').closest('.form-group'),
        $reg_date_box   = $('#_motor-registration-date').closest('.form-group'),
        $reg_field      = $('#_motor-registration-no'),
        $reg_date_field = $('#_motor-registration-date');

        if(et === 'ifChecked' || $this.prop('checked') === true){
            $reg_field.val('TO BE INTIMATED');
            $reg_date_field.val('');
            $reg_box.hide(500);
            $reg_date_box.hide(500);
        }else if(typeof et === 'undefined' || et === 'ifUnchecked'){
            $reg_box.show(500);
            $reg_date_box.show(500);
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
