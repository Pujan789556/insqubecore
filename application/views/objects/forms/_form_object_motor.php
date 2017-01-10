<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Object - Motor
 */
?>

<div class="box-header with-border">
    <h3 class="box-title">Vehicle Information</h3>
</div>
<?php
/**
 * Vehicle Information
 */
$vehicle_elements = $form_elements['vehicle'];
$this->load->view('templates/_common/_form_components_horz', [
    'form_elements' => $vehicle_elements,
    'form_record'   => $record
]);
?>
<div class="box-header with-border">
    <h3 class="box-title">Trailer/Trolly Information (Private/Commercial Vehicle Only)</h3>
</div>
<p class="help-block"><i class="fa fa-info-circle"></i> Please supply trailer/trolly price if this vehicle has any.</p>

<?php
/**
 * Vehicle Information
 */
$trailer_elements = $form_elements['trailer'];
$this->load->view('templates/_common/_form_components_horz', [
    'form_elements' => $trailer_elements,
    'form_record'   => $record
]);
?>

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
        }else{
            $reg_box.show(500);
            $reg_date_box.show(500);
        }
    }

    function _po_motor_change_sub_portfolio(d){
        var $this = $(d),
            v = $this.val();
        if(v == 'CVC')
        {
            $('#_motor-vehicle-df').closest('.form-group').hide();
            $('#_motor-vehicle-cvc-type').closest('.form-group').show();
        }else if(v == 'MCY'){
            $('#_motor-vehicle-cvc-type').closest('.form-group').hide();
            $('#_motor-vehicle-df').closest('.form-group').show();
        }else{
            $('#_motor-vehicle-cvc-type').closest('.form-group').hide();
            $('#_motor-vehicle-df').closest('.form-group').hide();
        }

        // Default Engine Capacity and Carrying Capacity Unit ( Motorcycle & Private Vehicle -> CC | Seat )
        if( v !== '' &&  v !== 'CVC'){

            // We change if not already not anything (add mode)
            var ec_unit = $('#_motor-vehicle-ec-unit').val(),
            cr_unit  = $('#_motor-vehicle-carrying-unit').val();

            if(ec_unit == '')$('#_motor-vehicle-ec-unit').val('CC');
            if(cr_unit == '')$('#_motor-vehicle-carrying-unit').val('S');
        }
    }

    (function($){

        // Subportfolio Change
        _po_motor_change_sub_portfolio('#_motor-sub-portfolio');

        // To Be Intimated Toggle
        _po_to_be_intimated('#_motor-vehicle-to-be-intimated');
        $('#_motor-vehicle-to-be-intimated').on('ifChecked ifUnchecked', function(event){
            _po_to_be_intimated(this, event.type);
        });

    })(jQuery);
</script>
