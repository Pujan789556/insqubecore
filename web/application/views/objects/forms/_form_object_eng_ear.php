<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Object - Erection All Risk (Eng)
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
                <h4 class="box-title">Section II - Third Party Liability</h4>
            </div>
            <?php
            $section_elements           = $form_elements['third_party'];
            $tbl_liabilities_dropdown   = $section_elements[0]['_data'];
            $tpl_objects                = $record->third_party ?? NULL;
            ?>
            <table class="table table-bordered table-condensed no-margin">
                <thead>
                    <tr>
                        <?php foreach($section_elements as $elem): ?>
                            <th><?php echo $elem['label'] ?></th>
                        <?php endforeach ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 0;
                    foreach($tbl_liabilities_dropdown as $key=>$label): ?>
                        <tr>
                            <?php
                            /**
                             * Create a Record for Third Single Row object, (edit mode)
                             */
                            $tpl_row_record = NULL;
                            if($tpl_objects)
                            {
                                $tpl_row_record = (object)[];
                                foreach ($section_elements as $elem)
                                {
                                    $tpl_row_record->{$elem['_key']} = $tpl_objects->{$elem['_key']}[$i];
                                }
                            }
                            else
                            {
                                $section_elements[0]['_default'] = $key;
                            }
                            $section_elements[0]['_extra_html_below']   = $label;

                            $this->load->view('templates/_common/_form_components_table', [
                                'form_elements' => $section_elements,
                                'form_record'   => $tpl_row_record
                            ]);

                            $i++;
                            ?>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>

        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">Other Details</h4>
            </div>
            <div class="box-body form-horizontal">
                <?php
                $section_elements   = $form_elements['others'];
                $section_object     = $record->others ?? NULL;
                $this->load->view('templates/_common/_form_components_horz', [
                    'form_elements' => $section_elements,
                    'form_record'   => $section_object
                ]);
                ?>
            </div>
        </div>
    </div>

    <div class="col-sm-12">

        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">Section I - Material Damage</h4>
            </div>
            <?php
            $section_elements           = $form_elements['items'];
            $insured_items_dropdown   = $section_elements[0]['_data'];
            $item_objects                = $record->items ?? NULL;
            ?>
            <table class="table table-bordered table-condensed table-hover no-margin">
                <thead>
                    <tr>
                        <?php foreach($section_elements as $elem): ?>
                            <th><?php echo $elem['label'] ?></th>
                        <?php endforeach ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                     $i = 0;
                    foreach($insured_items_dropdown as $group_title=>$item_groups): ?>
                        <tr>
                            <th colspan="3"><strong><?php echo $group_title ?></strong></th>
                        </tr>
                        <?php
                        foreach($item_groups as $key=>$label): ?>
                            <tr>
                                <?php
                                /**
                                 * Create a Record for Third Single Row object, (edit mode)
                                 */
                                $item_row_record = NULL;
                                if($item_objects)
                                {
                                    $item_row_record = (object)[];
                                    foreach ($section_elements as $elem)
                                    {
                                        $item_row_record->{$elem['_key']} = $item_objects->{$elem['_key']}[$i];
                                    }
                                }
                                else
                                {
                                    $section_elements[0]['_default'] = $key;
                                }
                                $section_elements[0]['_extra_html_below']   = $label;

                                $this->load->view('templates/_common/_form_components_table', [
                                    'form_elements' => $section_elements,
                                    'form_record'   => $item_row_record
                                ]);

                                $i++;
                                ?>
                            </tr>
                        <?php endforeach ?>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>

        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">Risk Deductibles</h4>
            </div>
            <div class="box-body form-horizontal">
                <?php
                $section_elements   = $form_elements['risk'];
                $section_object     = $record->risk ?? NULL;
                $this->load->view('templates/_common/_form_components_horz', [
                    'form_elements' => $section_elements,
                    'form_record'   => $section_object
                ]);
                ?>
            </div>
        </div>
    </div>
</div>