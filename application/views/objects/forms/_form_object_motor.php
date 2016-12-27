<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Customer
 */
$this->load->view('templates/_common/_form_components_horz', [
    'form_elements' => $form_elements,
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
