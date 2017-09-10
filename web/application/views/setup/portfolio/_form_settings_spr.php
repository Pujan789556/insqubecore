<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Portfolio Settings - Short-term Policy Rate
 */

$spr_objects = json_decode($record->short_term_policy_rate ?? '[]');

echo form_open( $this->uri->uri_string(),
                        [
                            'class' => 'form-iqb-general',
                            // 'id'    => '__testform',
                            'data-pc' => '.bootbox-body' // parent container ID
                        ],
                        // Hidden Fields
                        ['id' => $record->id]); ?>
<div class="box box-solid box-bordered">
    <table class="table table-bordered table-condensed no-margin">
        <thead>
            <tr>
                <th>Title<?php echo field_compulsary_text( TRUE )?></th>
                <th>Duration (Days)<?php echo field_compulsary_text( TRUE )?></th>
                <th>Rate(%)<?php echo field_compulsary_text( TRUE )?></th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $item_count  = count( $spr_objects );
            if($item_count):
                for($i = 0; $i < $item_count; $i++):
            ?>
                    <tr <?php echo $i == 0 ? 'id="__spr_row"' : '' ?>>
                        <?php
                        /**
                         * Load Form Components
                         */
                        $this->load->view('templates/_common/_form_components_table', [
                            'form_elements' => $form_elements,
                            'form_record'   => $spr_objects[$i]
                        ]);

                        if($i != 0 ):?>
                            <td width="10%" align="right"><a href="#" class="btn btn-danger btn-sm" onclick='$(this).closest("tr").remove()'>Remove</a></td>
                        <?php else: ?>
                            <td>&nbsp;</td>
                        <?php endif ?>
                    </tr>
                <?php endfor ?>
            <?php else: ?>
                <tr id="__spr_row">
                    <?php
                    /**
                     * Load Form Components
                     */
                    $this->load->view('templates/_common/_form_components_table', [
                        'form_elements' => $form_elements,
                        'form_record'   => $spr_objects[$i]
                    ]);
                    ?>
                    <td>&nbsp;</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <div class="box-footer bg-info">
        <a href="#" class="btn bg-teal" onclick="__duplicate_tr('#__spr_row', this)">Add More</a>
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
        $row.append('<td width="10%" align="right"><a href="#" class="btn btn-danger btn-sm" onclick=\'$(this).closest("tr").remove();\'>Remove</a></td>');

        // Append to table body
        $box.append($row);
    }
</script>
