<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Object - Contractor Plant & Machinary (Eng)
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
    <div class="col-md-12">
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">Item Details</h4>
            </div>
            <?php
            $section_elements           = $form_elements['item'];
            $item_object                = $record->item ?? NULL;
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
                    <tr>
                        <?php
                        /**
                         * Create a Record for Third Single Row object, (edit mode)
                         */
                        $item_row_record = NULL;
                        if($item_object)
                        {
                            $item_row_record = (object)[];
                            foreach ($section_elements as $elem)
                            {
                                $item_row_record->{$elem['_key']} = $item_object->{$elem['_key']};
                            }
                        }
                        $this->load->view('templates/_common/_form_components_table', [
                            'form_elements' => $section_elements,
                            'form_record'   => $item_row_record
                        ]);
                        ?>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>