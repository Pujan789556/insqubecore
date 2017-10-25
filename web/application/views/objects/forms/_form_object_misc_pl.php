<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Object - MISCELLANEOUS - PUBLIC LIABILITY(PL)
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
                <h4 class="box-title">Public Liability</h4>
            </div>
            <?php
            $section_elements           = $form_elements['public_liability'];
            $liabilities_dropdown   = $section_elements[0]['_data'];
            $liability_objects                = $record->public_liability ?? NULL;
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
                    foreach($liabilities_dropdown as $key=>$label): ?>
                        <tr>
                            <?php
                            /**
                             * Create a Record for Third Single Row object, (edit mode)
                             */
                            $tpl_row_record = NULL;
                            if($liability_objects)
                            {
                                $tpl_row_record = (object)[];
                                foreach ($section_elements as $elem)
                                {
                                    $tpl_row_record->{$elem['_key']} = $liability_objects->{$elem['_key']}[$i];
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
</div>