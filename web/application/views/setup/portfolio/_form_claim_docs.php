<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Portfolio - Claim Docs JSON
 */

$claim_docs_config   = json_decode($record->claim_docs ?? '[]');
$claim_docs          = $claim_docs_config->claim_docs ?? [];
?>
<style type="text/css">
    td .form-group{margin-bottom: 0;}
</style>
<?php echo form_open( $this->uri->uri_string(),
                        [
                            'class' => 'form-iqb-general',
                            'data-pc' => '.bootbox-body' // parent container ID
                        ],
                        // Hidden Fields
                        isset($record) ? ['id' => $record->id] : []); ?>

    <div class="box box-solid box-bordered">
        <div class="box-header with-border">
            <h4 class="box-title">Portfolio Claim Document Setup Table - <?php echo $record->name_en; ?></h4>
        </div>
        <div class="box-body">
            <table class="table table-bordered table-condensed">
                <thead>
                    <tr>
                        <th>Doc Code <?php echo field_compulsary_text( TRUE )?></th>
                        <th>Doc Name (EN) <?php echo field_compulsary_text( TRUE )?></th>
                        <th>Doc Name (ने) <?php echo field_compulsary_text( TRUE )?></th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    $i = 0;
                    if($claim_docs):
                        foreach ($claim_docs as $single):
                            ?>
                        <tr <?php echo $i == 0 ? 'id="__claim_doc_heading_row"' : '' ?>>
                            <?php foreach($form_elements as $element):?>
                                <td class="<?php echo $element['_type'] == 'hidden' ? 'hide' : '' ?>">
                                    <?php
                                    /**
                                     * Load Single Element
                                     */
                                    $element['_default']    = $single->{$element['_key']} ?? '';
                                    $element['_value']      = $element['_default'];
                                    $this->load->view('templates/_common/_form_components_inline', [
                                        'form_elements' => [$element],
                                        'form_record'   => NULL
                                    ]);
                                    ?>
                                </td>
                            <?php
                            endforeach;
                            if($i == 0):?>
                                <td>&nbsp;</td>
                            <?php else:?>
                                <td width="10%" align="right"><a href="#" class="btn btn-danger btn-sm" onclick='$(this).closest("tr").remove();'>Remove</a></td>
                            <?php endif;?>
                        </tr>
                    <?php
                        $i++;
                        endforeach;
                    else:?>
                        <tr id="__claim_doc_heading_row">
                            <?php foreach($form_elements as $element):?>
                                <td class="<?php echo $element['_type'] == 'hidden' ? 'hide' : '' ?>">
                                    <?php
                                    /**
                                     * Load Single Element
                                     */
                                    $this->load->view('templates/_common/_form_components_inline', [
                                        'form_elements' => [$element],
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
            <a href="#" class="btn bg-teal" onclick="__duplicate_tr('#__claim_doc_heading_row', this)">Add More</a>
        </div>
    </div>
    <button type="submit" class="hide">Submit</button>
<?php echo form_close();?>


<script type="text/javascript">

    /**
     * Duplicate Row
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