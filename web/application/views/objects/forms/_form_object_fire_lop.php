<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Object - FIRE - LOSS OF PROFIT
 */
?>
<div class="box box-solid box-bordered">
    <div class="box-header with-border">
        <h4 class="box-title">Basic Information</h4>
    </div>
    <div class="box-body form-horizontal">
        <?php
        $section_elements = $form_elements['basic'];
        $this->load->view('templates/_common/_form_components_horz', [
            'form_elements' => $section_elements,
            'form_record'   => $record
        ]);
        ?>
    </div>
</div>