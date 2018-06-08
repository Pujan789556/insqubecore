<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Setup - RI - Treaties - Distribution
 */
?>
<?php echo form_open( $this->uri->uri_string(),
                        [
                            'class' => 'form-iqb-general',
                            'id'    => '__form-treaty-setup-distribution',
                            'data-pc' => '.bootbox-body' // parent container ID
                        ],
                        // Hidden Fields
                        isset($record) ? ['id' => $record->id] : []); ?>

    <div class="box box-solid box-bordered">
        <div class="box-header with-border">
            <h4 class="box-title">RI Distribution Table</h4>
        </div>
        <div class="box-body" style="overflow-x: scroll;">
            <table class="table table-bordered table-condensed">
                <thead>
                    <tr>
                        <th>Broker Company</th>
                        <th>Re-Insurance Company<?php echo field_compulsary_text( TRUE )?></th>
                        <th>% Share<?php echo field_compulsary_text( TRUE )?></th>
                        <th>Action</th>
                    </tr>
                </thead>

                <?php
                $treaty_distribution_form_elements = $form_elements['reinsurers'];
                $total_percentage = 0.00;
                ?>
                <tbody class="form-inline">
                    <?php
                    $i = 0;
                    if($treaty_distribution):
                        foreach ($treaty_distribution as $distrib):
                            $total_percentage += $distrib->distribution_percent;
                            ?>
                        <tr <?php echo $i == 0 ? 'id="__treaty_distribution_row"' : '' ?>>
                            <?php foreach($treaty_distribution_form_elements as $element):?>
                                <td>
                                    <?php
                                    /**
                                     * Load Single Element
                                     */
                                    $element['_default']    = $distrib->{$element['_field']} ?? '';
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
                                <td class="text-success"><i class="fa fa-check"></i> Leader</td>
                            <?php else:?>
                                <td width="10%" align="right"><a href="#" class="btn btn-danger btn-sm" onclick='$(this).closest("tr").remove();__compute_sum();'>Remove</a></td>
                            <?php endif;?>
                        </tr>
                    <?php
                        $i++;
                        endforeach;
                    else:?>
                        <tr id="__treaty_distribution_row">
                            <?php foreach($treaty_distribution_form_elements as $single_element):?>
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
                            <td class="text-success"><i class="fa fa-check"></i> Leader</td>
                        </tr>
                    <?php endif;?>
                </tbody>
                <tfoot>
                    <tr>
                        <th class="text-right" colspan="2">Total(%)</th>
                        <td id="__total_box" class="<?php echo $total_percentage == 100 ? 'text-green' : 'text-red';?>"><?php echo number_format($total_percentage, 2);?></td>
                        <td>&nbsp;</td>
                    </tr>
                </tfoot>
            </table>
            <p class="help-block">Remember, the first company is always <strong>Leader</strong>.</p>
        </div>
        <div class="box-footer bg-info">
            <a href="#" class="btn bg-teal" onclick="__duplicate_tr('#__treaty_distribution_row', this)">Add More</a>
        </div>
    </div>

    <button type="submit" class="hide">Submit</button>
<?php echo form_close();?>
<script type="text/javascript">

    /**
     * Do the Math (Show total Percentnage)
     */
     $(document).on('keyup', 'input[name="distribution_percent[]"]', function(){
        __compute_sum();
     });

    function __compute_sum()
    {
        var total = 0.00,
            $box = $('#__total_box');
        $('input[name="distribution_percent[]').each(function() {
            total += parseFloat($(this).val());
        });

        $box.html(total);
        if( total == 100 ){
            $box.removeClass('text-red')
                .addClass('text-green');
        }else{
            $box.removeClass('text-green')
                .addClass('text-red');
        }
     }

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

        // Update Sum
        __compute_sum();
    }
</script>