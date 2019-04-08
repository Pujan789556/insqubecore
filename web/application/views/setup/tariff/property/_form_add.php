<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Tariff Property
 */
?>
<style type="text/css">
    td > .form-group{margin-bottom: 0}
</style>
<?php echo form_open( $this->uri->uri_string(),
                        [
                            'class' => 'form-iqb-general',
                            'data-pc' => '.bootbox-body' // parent container ID
                        ],
                        // Hidden Fields
                        isset($record) ? ['id' => $record->id ?? NULL] : []); ?>


    <div class="row">
        <div class="col-md-6">
            <div class="box box-solid box-bordered">
                <div class="box-header with-border">
                    <h4 class="box-title">Fiscal Year</h4>
                </div>
                <div class="box-body form-horizontal">
                    <?php
                    $section_elements = $form_elements['fiscal_year'];
                    if(!$record)
                    {
                        $this->load->view('templates/_common/_form_components_horz', [
                            'form_elements' => $section_elements,
                            'form_record'   => $record
                        ]);
                    }
                    else
                    {
                        echo $record->fiscal_yr_id;
                    }
                    ?>
                </div>
            </div>
        </div>

        <div class="col-sm-12">
            <div class="box box-solid box-bordered">
                <div class="box-header with-border">
                    <h4 class="box-title">Risk Categories</h4>
                </div>
                <?php
                $section_elements   = $form_elements['risk_categories'];
                $item_count          = count( $risk_categories ?? [] );
                ?>
                <table class="table table-bordered table-condensed no-margin">
                    <thead>
                        <tr>
                            <?php foreach($section_elements as $elem): ?>
                                <th><?php echo $elem['label'], field_compulsary_text($elem['_required']); ?></th>
                            <?php endforeach ?>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            if($item_count):
                                $i = 0;
                                foreach($risk_categories as $single): ?>
                                    <tr <?php echo $i++ == 0 ? 'id="__category_row"' : '' ?>>
                                        <?php foreach($section_elements as $single_element):?>
                                            <td>
                                                <?php
                                                /**
                                                 * Load Single Element
                                                 */
                                                $this->load->view('templates/_common/_form_components_inline', [
                                                    'form_elements' => [$single_element],
                                                    'form_record'   => $single
                                                ]);
                                                ?>
                                            </td>
                                        <?php
                                        endforeach;
                                        if($i++ == 1):?>
                                            <td>&nbsp;</td>
                                        <?php else:?>
                                            <td width="10%"><a href="#" class="btn btn-danger btn-sm" onclick='$(this).closest("tr").remove()'><i class="fa fa-trash"></i></a></td>
                                        <?php endif;?>
                                    </tr>
                                <?php
                                endforeach;
                            else:?>
                                <tr id="__category_row">
                                    <?php foreach($section_elements as $elem):?>
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
                <div class="box-footer bg-info">
                    <a href="#" class="btn bg-teal" onclick="__duplicate_tr('#__category_row', this)">Add More</a>
                </div>
            </div>
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

        // Reset form element (required as id is hidden passed)
        $('input, select', $row).val('');

        // remove last blank td
        $row.find('td:last').remove();

        // Add Remover Column
        $row.append('<td width="10%"><a href="#" class="btn btn-danger btn-sm" onclick=\'$(this).closest("tr").remove();\'><i class="fa fa-trash"></i></a></td>');

        // Append to table body
        $box.append($row);
    }
</script>