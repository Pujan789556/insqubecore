<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Object - Burglary (Jewelry, Housebreaking, Cash in Safe)
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
                        <tr <?php echo $i == 0 ? 'id="__brg_land_building_row"' : '' ?>>
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
                        <tr id="__brg_land_building_row">
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
        <a href="#" class="btn bg-teal" onclick="__duplicate_tr('#__brg_land_building_row', this)">Add More</a>
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
        $row.append('<td width="10%" align="right"><a href="#" class="btn btn-danger btn-sm" onclick=\'$(this).closest("tr").remove();\'>Remove</a></td>');

        // Append to table body
        $box.append($row);
    }
</script>
