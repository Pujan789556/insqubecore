<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Object - Marine
 */
?>
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

        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">Transit Details</h4>
            </div>
            <div class="box-body form-horizontal">
                <?php
                $section_elements   = $form_elements['transit'];
                $section_object     = $record->transit ?? NULL;
                $this->load->view('templates/_common/_form_components_horz', [
                    'form_elements' => $section_elements,
                    'form_record'   => $section_object
                ]);
                ?>
            </div>
        </div>
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">Surveyor Details</h4>
            </div>
            <div class="box-body form-horizontal">
                <?php
                $section_elements   = $form_elements['surveyor'];
                $section_object     = $record->surveyor ?? NULL;
                $this->load->view('templates/_common/_form_components_horz', [
                    'form_elements' => $section_elements,
                    'form_record'   => $section_object
                ]);
                ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">Sum Insured Details</h4>
            </div>
            <div class="box-body form-horizontal">
                <?php
                $section_elements   = $form_elements['sum_insured'];
                $section_object     = $record->sum_insured ?? NULL;
                $this->load->view('templates/_common/_form_components_horz', [
                    'form_elements' => $section_elements,
                    'form_record'   => $section_object
                ]);
                ?>
            </div>
        </div>

        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">Risk Details</h4>
            </div>
            <div class="box-body form-horizontal">
                <?php
                $section_elements   = $form_elements['risk'];
                $section_object     = $record->risk ?? NULL;

                /**
                 * Prepare Clauses Data (Edit)
                 */
                if($section_object)
                {
                    $section_elements[ count($section_elements) - 1 ]['_checkbox_value'] = $section_object->clauses;
                }
                $this->load->view('templates/_common/_form_components_horz', [
                    'form_elements' => $section_elements,
                    'form_record'   => $section_object
                ]);
                ?>
            </div>
        </div>


    </div>
</div>