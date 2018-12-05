<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Object - Fire
 */
$item_form_elements     = $form_elements['items_manual'];
$old_document = $record->document ?? NULL;
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
    </div>

    <div class="col-md-8" id="_ib_upload">
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">Upload Item List</h4>
            </div>
            <div class="box-body form-horizontal">
                <?php
                $section_elements = $form_elements['items_file'];
                $this->load->view('templates/_common/_form_components_horz', [
                    'form_elements' => $section_elements,
                    'form_record'   => $record
                ]);

                if($old_document):
                ?>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Download existing Item List</label>
                    <div class="col-sm-10">
                        <p class="form-control-static">
                            <?php echo anchor('objects/download/'.$old_document, 'Download', ['target' => '_blank']); ?>
                        </p>
                    </div>
                </div>
                <?php endif; ?>



            </div>
        </div>
    </div>

    <div class="col-md-8" id="_ib_manual">
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
                <h4 class="box-title">Item Details</h4>
            </div>
            <table class="table table-bordered table-condensed no-margin">
                <thead>
                    <tr>
                        <?php foreach($item_form_elements as $elem): ?>
                            <th>
                                <?php echo $elem['label'] . field_compulsary_text($elem['_required']) ?>
                            </th>
                        <?php endforeach; ?>
                        <th>Action</th>
                    </tr>
                </thead>
                <?php
                $items                  = $record->items ?? NULL;
                $item_count             = count( $items ?? [] );
                ?>
                <tbody class="form-inline">
                    <?php
                        if($item_count):
                            $i = 0;
                            foreach($items as $item_record):?>
                            <tr <?php echo $i++ == 0 ? 'id="__fire_items_row"' : '' ?>>
                                <?php foreach($item_form_elements as $single_element):?>
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
                                    <td width="10%" align="right"><a href="#" class="btn btn-danger btn-sm" onclick='$(this).closest("tr").remove()'>Remove</a></td>
                                <?php endif;?>
                            </tr>
                        <?php
                            endforeach;
                        else:?>
                            <tr id="__fire_items_row">
                                <?php foreach($item_form_elements as $single_element):?>
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
                <a href="#" class="btn bg-teal" onclick="__duplicate_tr('#__fire_items_row', this)">Add More</a>
            </div>
        </div>
    </div>
</div>

<div class="box box-solid box-bordered">
    <div class="box-header with-border">
        <h4 class="box-title">Land/Building Details</h4>
    </div>
    <div class="box-body" style="overflow-x: scroll;">
        <table class="table table-bordered table-condensed no-margin" >
            <thead>
                <tr>
                    <th>Owner Name(s)</th>
                    <th>Owner Address</th>
                    <th>Owner Contacts (Mobile/Phone)</th>
                    <th>Land Plot No.</th>
                    <th>House No.</th>
                    <th>Tole/Street Address</th>
                    <th>District</th>
                    <th>VDC/Municipality</th>
                    <th>Ward No.</th>
                    <th>No. of Storeys</th>
                    <th>Construction Category</th>
                    <th>Used For</th>
                    <th>Action</th>
                </tr>
            </thead>
            <?php
            $land_building_owner_elements = $form_elements['land_building_owner'];
            $land_building      = $record->land_building ?? NULL;
            $item_count         = count( $land_building->owner_name ?? [] );
            ?>
            <tbody class="form-inline">
                <?php
                    if($item_count):
                        for ($i=0; $i < $item_count; $i++):?>
                        <tr <?php echo $i == 0 ? 'id="__fire_land_building_row"' : '' ?>>
                            <?php foreach($land_building_owner_elements as $single_element):?>
                                <td>
                                    <?php
                                    /**
                                     * Load Single Element
                                     */
                                    $single_element['_default']    = $land_building->{$single_element['_key']}[$i] ?? '';
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
                                <td width="10%" align="right"><a href="#" class="btn btn-danger btn-sm" onclick='$(this).closest("tr").remove()'>Remove</a></td>
                            <?php endif;?>
                        </tr>
                    <?php
                        endfor;
                    else:?>
                        <tr id="__fire_land_building_row">
                            <?php foreach($land_building_owner_elements as $single_element):?>
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
    </div>

    <div class="box-footer bg-info">
        <a href="#" class="btn bg-teal" onclick="__duplicate_tr('#__fire_land_building_row', this)">Add More</a>
    </div>
</div>

<script type="text/javascript">
(function($){

    var $ref_u_box = $('#_ib_upload'),
        $ref_m_box = $('#_ib_manual'),
        initial_type = $('input[type=radio][name="object[item_attached]"]:checked').val();

    if(typeof initial_type === 'undefined'){
        // hide both types
        $ref_u_box.hide();
        $ref_m_box.hide();
    }
    else if(initial_type == 'N'){
        $ref_u_box.hide();
    }else{
        $ref_m_box.hide();
    }

    // Toggle fields according to type
    $('input[type=radio][name="object[item_attached]"]').on('ifChanged', function() {
        if (this.value == 'N') {
            $ref_u_box.hide();
            $ref_m_box.show();
        }
        else {
            $ref_u_box.show();
            $ref_m_box.hide();
        }
    });

})(jQuery);
</script>

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
        $row.append('<td width="10%" align="right"><a href="#" class="btn btn-danger btn-sm" onclick=\'$(this).closest("tr").remove();\'>Remove</a></td>');

        // Append to table body
        $box.append($row);
    }
</script>
