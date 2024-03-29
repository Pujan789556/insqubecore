<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Claim - Surveyors
 */
?>
<style type="text/css">
    .form-inline input, .form-inline select{width: 100% !important}
    .form-inline .form-group{display: block; margin:0;}
</style>
<?php echo form_open( $this->uri->uri_string(),
                        [
                            'class'     => 'form-iqb-general',
                            'data-pc'   => '.bootbox-body', // parent container ID
                            'id'        => '_form-claims'
                        ],
                        // Hidden Fields
                        isset($record) ? ['id' => $record->id] : []); ?>



    <div class="box box-solid box-bordered">
        <div class="box-header with-border">
            <h4 class="box-title">Surveyor(s) Information</h4>
        </div>
        <div class="box-body" style="overflow-x: scroll;">
            <table class="table table-bordered table-condensed">
                <thead>
                    <tr>
                        <?php foreach($form_elements as $elem): ?>
                            <th><?php echo $elem['label'] . field_compulsary_text( $elem['_required'] ?? FALSE ) ?> </th>
                        <?php endforeach ?>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody class="form-inline">
                    <?php
                    $i = 0;
                    if($surveyors):
                        foreach ($surveyors as $single):?>
                            <tr <?php echo $i == 0 ? 'id="__surveyors_row"' : '' ?>>
                                <?php foreach($form_elements as $elem):?>
                                    <td>
                                        <?php
                                        /**
                                         * Load Single Element
                                         */
                                        $value = $single->{$elem['_key']} ?? '';
                                        $elem['_default']    = $value;
                                        $elem['_value']      = $value;
                                        $this->load->view('templates/_common/_form_components_inline', [
                                            'form_elements' => [$elem],
                                            'form_record'   => NULL
                                        ]);
                                        ?>
                                    </td>
                                <?php
                                endforeach;

                                // claim_surveyor table's PK
                                echo '<td class="hide">', form_hidden('ids[]', $single->id), '</td>';

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
                        <tr id="__surveyors_row">
                            <?php foreach($form_elements as $elem):?>
                                <td>
                                    <?php
                                    /**
                                     * Load Single Element
                                     */
                                    $this->load->view('templates/_common/_form_components_inline', [
                                        'form_elements' => [$elem],
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
            <a href="#" class="btn bg-teal" onclick="__duplicate_tr('#__surveyors_row', this)">Add More</a>
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

        // Empty Row
        $row.find('input').val('');
        $row.find('select').val('');

        // remove last blank td
        $row.find('td:last').remove();

        // Add Remover Column
        $row.append('<td width="10%" align="right"><a href="#" class="btn btn-danger btn-sm" onclick=\'$(this).closest("tr").remove();\'>Remove</a></td>');

        // Append to table body
        $box.append($row);
    }

</script>