<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Object - MISCELLANEOUS - BANKER'S BLANKET(BB)
 */
?>
<style type="text/css">
    td > .form-group{margin-bottom: 0}
</style>
<div class="row">
    <div class="col-md-6">
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
    </div>

    <div class="col-md-6">
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">Sum Insured Information</h4>
            </div>
            <div class="box-body form-horizontal">
                <?php
                $section_elements   = $form_elements['sum_insured'];
                $section_record     = $record->sum_insured ?? NULL;
                $this->load->view('templates/_common/_form_components_horz', [
                    'form_elements' => $section_elements,
                    'form_record'   => $section_record
                ]);
                ?>
            </div>
        </div>
    </div>
</div>
