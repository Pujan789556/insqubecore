<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Benefits - TMI Plans
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
        isset($record) ? ['id' => $record->id] : []); ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-solid box-bordered scroll-x">
                <?php
                $benefit_json        = $record->benefits;
                $benefit_records     = json_decode($benefit_json ?? '[]');
                $item_count         = count( $benefit_records);
                ?>
                <table class="table table-bordered table-condensed no-margin">
                    <thead>
                        <tr>
                            <?php foreach($form_elements as $elem): ?>
                                <th><?php echo $elem['label'] ?></th>
                            <?php endforeach ?>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            if($item_count):
                                for ($i=0; $i < $item_count; $i++):?>
                                <tr <?php echo $i == 0 ? 'id="__tmi_benefit_row"' : '' ?>>
                                    <?php foreach($form_elements as $single_element):?>
                                        <td>
                                            <?php
                                            /**
                                             * Load Single Element
                                             */
                                            $single_element['_default']    = $benefit_records[$i]->{$single_element['_key']} ?? '';
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
                            <?php endfor;?>
                            <?php else:?>
                                <tr id="__tmi_benefit_row">
                                    <?php foreach($form_elements as $single_element):?>
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
                    <a href="#" class="btn bg-teal" onclick="__duplicate_tr('#__tmi_benefit_row', this)">Add More</a>
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

        // remove last blank td
        $row.find('td:last').remove();

        // Add Remover Column
        $row.append('<td width="10%"><a href="#" class="btn btn-danger btn-sm" onclick=\'$(this).closest("tr").remove();\'><i class="fa fa-trash"></i></a></td>');

        // Append to table body
        $box.append($row);
    }
</script>