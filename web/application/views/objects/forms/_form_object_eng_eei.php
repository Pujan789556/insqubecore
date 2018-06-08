<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Object - ELECTRONIC EQUIPMENT INSURANCE (Eng)
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

                // Items Cols
                $items_cols = $form_elements['items'];
                ?>
                <p class="help-block">Please note that the excel file must have the following format:</p>
                <table class="table table-condensed table-bordered text-danger">
                    <thead>
                        <tr>
                            <?php foreach($items_cols as $single): ?>
                                <th><?php echo $single['label'] ?></th>
                            <?php endforeach ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <?php foreach($items_cols as $single): ?>
                                <th>...</th>
                            <?php endforeach ?>
                        </tr>
                    </tbody>
                </table>
                <p class="text-red"><strong>NOTE:</strong> If you have uploaded item list, please do not fill the <strong>Item Details. It will be overwritten by the excel data.</strong> section below.</p>
            </div>
        </div>
    </div>

    <div class="col-sm-12">
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">Item Details</h4>
            </div>
            <?php
            $section_elements   = $form_elements['items'];
            $items               = $record->items ?? NULL;
            $item_count          = count( $items ?? [] );
            ?>
            <table class="table table-bordered table-condensed no-margin">
                <thead>
                    <tr>
                        <?php foreach($section_elements as $elem): ?>
                            <th><?php echo $elem['label'], field_compulsary_text($elem['_required']) ?></th>
                        <?php endforeach ?>
                        <td>Action</td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        if($item_count):
                            $i = 0;
                            foreach($items as $item_record):?>
                                <tr <?php echo $i++ == 0 ? 'id="__eei_items_row"' : '' ?>>
                                    <?php foreach($section_elements as $single_element):?>
                                        <td>
                                            <?php
                                            /**
                                             * Load Single Element
                                             */
                                            $single_element['_default']    = $item_record->{$single_element['_key']} ?? '';
                                            $single_element['_value']      = $single_element['_default'];
                                            $this->load->view('templates/_common/_form_components_inline', [
                                                'form_elements' => [$single_element],
                                                'form_record'   => NULL
                                            ]);
                                            ?>
                                        </td>
                                    <?php
                                    endforeach;
                                    if($i == 0):?>
                                        <td>&nbsp;</td>
                                    <?php else:?>
                                        <td width="10%"><a href="#" class="btn btn-danger btn-sm" onclick='$(this).closest("tr").remove()'><i class="fa fa-trash"></i></a></td>
                                    <?php endif;?>
                                </tr>
                            <?php
                            endforeach;
                        else:?>
                            <tr id="__eei_items_row">
                                <?php foreach($section_elements as $single_element):?>
                                    <td>
                                        <?php
                                        /**
                                         * Load Single Element
                                         */
                                        $this->load->view('templates/_common/_form_components_inline', [
                                            'form_elements' => [$single_element],
                                            'form_record'   => NULL
                                        ]);
                                        ?>
                                    </td>
                                <?php endforeach?>
                                <td>&nbsp;</td>
                            </tr>
                        <?php endif;?>
                </tbody>
            </table>
            <div class="box-footer bg-info">
                <a href="#" class="btn bg-teal" onclick="__duplicate_tr('#__eei_items_row', this)">Add More</a>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    /**
     * Duplicate Treaty Distribution Row
     */
    function __duplicate_tr(src, a)
    {
        var $src = $(src),
            $box = $src.closest('tbody'),
            html = $src.html(),
            $row  = $('<tr></tr>');

        $row.html(html);

        // remove last blank td
        $row.find('td:last').remove();

        // Add Remover Column
        $row.append('<td width="10%"><a href="#" class="btn btn-danger btn-sm" onclick=\'$(this).closest("tr").remove();\'><i class="fa fa-trash"></i></a></td>');

        // Append to table body
        $box.append($row);
    }
</script>
