<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Portfolio - Risk JSON
 */

$risks_config   = json_decode($record->risks ?? '[]');
$risks          = $risks_config->risks ?? [];
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
            <h4 class="box-title">Portfolio Risk Table - <?php echo $record->name_en; ?></h4>
        </div>
        <div class="box-body">
            <div class="form-horizontal">
                <?php
                /**
                 * Premium Computation Reference
                 * The first element of the validation rules
                 */
                $default_premium_computation = array_shift($form_elements);
                $object = (object)['default_premium_computation' => $risks_config->default_premium_computation ?? NULL ];
                $this->load->view('templates/_common/_form_components_horz', [
                    'form_elements' => [$default_premium_computation],
                    'form_record'   => $object
                ]);
                ?>
                <p class="help-block">
                    <strong>!!! NOTE !!!</strong> <br>
                    If Minimum Premium Computation is selected as <span class="text-red">"Cumulative (Per Risk Type)"</span>, <br>
                    the default minimum premium is govenred by <span class="text-red">"Portfolio Settings" -> Default Basic Premium( Rs) , Default Pool Premium (Rs)</span>. <br>
                    In that case, please set <span class="text-red">Default Minimum Premium (Rs.) = 0</span> for all risks.
                </p>
            </div>



            <table class="table table-bordered table-condensed">
                <thead>
                    <tr>
                        <th>Risk Code <?php echo field_compulsary_text( TRUE )?></th>
                        <th>Risk Name <?php echo field_compulsary_text( TRUE )?></th>
                        <th>Risk Type <?php echo field_compulsary_text( TRUE )?></th>
                        <th>Default Minimum Premium (Rs.) <?php echo field_compulsary_text( TRUE )?></th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    $i = 0;
                    if($risks):
                        foreach ($risks as $single):
                            ?>
                        <tr <?php echo $i == 0 ? 'id="__risk_heading_row"' : '' ?>>
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
                        <tr id="__risk_heading_row">
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
            <a href="#" class="btn bg-teal" onclick="__duplicate_tr('#__risk_heading_row', this)">Add More</a>
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