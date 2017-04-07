<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Setup - RI - Treaties - Commission Scale
 */
?>
<?php echo form_open( $this->uri->uri_string(),
                        [
                            'class' => 'form-iqb-general',
                            'id'    => '__form-treaty-setup-commission-scale',
                            'data-pc' => '.bootbox-body' // parent container ID
                        ],
                        // Hidden Fields
                        isset($record) ? ['id' => $record->id] : []); ?>

    <div class="box box-solid box-bordered">
        <div class="box-header with-border">
            <h4 class="box-title">Commission Scale Table</h4>
        </div>
        <div class="box-body">
            <table class="table table-bordered table-condensed">
                <thead>
                    <tr>
                        <th>Title<?php echo field_compulsary_text( TRUE )?></th>
                        <th>Minimum Scale(%)<?php echo field_compulsary_text( TRUE )?></th>
                        <th>Maximum Scale(%)<?php echo field_compulsary_text( TRUE )?></th>
                        <th>Rate(%)<?php echo field_compulsary_text( TRUE )?></th>
                        <th>Action</th>
                    </tr>
                </thead>

                <?php
                $commission_scale_form_elements = $form_elements['commission_scale'];
                $commission_scales              = $record->commission_scales ? json_decode( $record->commission_scales ) : NULL;
                ?>
                <tbody class="form-inline">
                    <?php
                    $i = 0;
                    if($commission_scales):
                        foreach ($commission_scales as $scale):?>
                        <tr <?php echo $i == 0 ? 'id="__commission_scales_row"' : '' ?>>
                            <?php foreach($commission_scale_form_elements as $single_element):?>
                                <td>
                                    <?php
                                    /**
                                     * Load Single Element
                                     */
                                    $single_element['_default']    = $scale->{$single_element['_field']} ?? '';
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
                        $i++;
                        endforeach;
                    else:?>
                        <tr id="__commission_scales_row">
                            <?php foreach($commission_scale_form_elements as $single_element):?>
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
            <a href="#" class="btn bg-teal" onclick="__duplicate_tr('#__commission_scales_row', this)">Add More</a>
        </div>
    </div>

    <button type="submit" class="hide">Submit</button>
<?php echo form_close();?>
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
        $row.append('<td width="10%" align="right"><a href="#" class="btn btn-danger btn-sm" onclick=\'$(this).closest("tr").remove();__compute_sum();\'>Remove</a></td>');

        // Append to table body
        $box.append($row);
    }
</script>