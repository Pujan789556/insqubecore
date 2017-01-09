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
}

(function($){
    _po_motor_change_sub_portfolio('#_motor-sub-portfolio');
})(jQuery);
</script>
