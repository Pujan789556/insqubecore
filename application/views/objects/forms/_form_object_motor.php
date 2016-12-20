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

function _po_motor_change_v_type(d){
    var $this = $(d),
        v = $this.val();
    if(v == 'cv')
    {
        $('#_motor-vehicle-df').closest('.form-group').hide();
        $('#_motor-vehicle-sub-type').closest('.form-group').show();
    }else if(v == 'mc'){
        $('#_motor-vehicle-sub-type').closest('.form-group').hide();
        $('#_motor-vehicle-df').closest('.form-group').show();
    }else{
        $('#_motor-vehicle-sub-type').closest('.form-group').hide();
        $('#_motor-vehicle-df').closest('.form-group').hide();
    }
}

(function($){
    _po_motor_change_v_type('#_motor-vehicle-type');
})(jQuery);
</script>
