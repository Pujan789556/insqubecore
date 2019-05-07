<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : RI FAC Transaction(Distribution)
 */
?>
<?php echo form_open( $this->uri->uri_string(),
                        [
                            'class' => 'form-iqb-general',
                            'id'    => '__form-fac-registration',
                            'data-pc' => '.bootbox-body' // parent container ID
                        ],
                        // Hidden Fields
                        isset($record) ? ['id' => $record->id] : []); ?>

    <div class="box box-solid box-bordered">
        <div class="box-header with-border">
            <h4 class="box-title">FAC Overview</h4>
        </div>
        <table class="table table-responsive table-condensed">
            <tbody>
                <tr>
                    <th>FAC SI (Rs.)</th>
                    <td class="text-right"><?php echo number_format($record->si_treaty_fac, 2);?></td>
                </tr>
                <tr>
                    <th>FAC Premium (Rs.)</th>
                    <td class="text-right"><?php echo number_format($record->premium_treaty_fac, 2);?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="box box-solid box-bordered">
        <div class="box-header with-border">
            <h4 class="box-title">FAC Distribution Table</h4>
        </div>
        <div class="box-body" style="overflow-x: scroll;">
            <table class="table table-bordered table-condensed">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Re-Insurance Company<?php echo field_compulsary_text( TRUE )?></th>
                        <th>Distribution(%)<?php echo field_compulsary_text( TRUE )?></th>
                        <th>Commission(%)<?php echo field_compulsary_text( TRUE )?></th>
                        <th>RI Tax (%)<?php echo field_compulsary_text( TRUE )?></th>
                        <th>IB Tax (%)<?php echo field_compulsary_text( TRUE )?></th>
                        <th>Action</th>
                    </tr>
                </thead>

                <?php
                $total_percentage = 0.00;
                ?>
                <tbody class="form-inline">
                    <?php
                    $i = 0;
                    if($fac_distribution):
                        foreach ($fac_distribution as $distrib):
                            $total_percentage += $distrib->fac_percent;
                            ?>
                        <tr <?php echo $i == 0 ? 'id="__fac_distribution_row"' : '' ?>>
                            <?php foreach($form_elements as $element):?>
                                <td>
                                    <?php
                                    /**
                                     * Load Single Element
                                     */
                                    $element['_default']    = $distrib->{$element['_key']} ?? '';
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
                                <td width="10%" align="right"><a href="#" class="btn btn-danger btn-sm" onclick='$(this).closest("tr").remove();__compute_sum();'>Remove</a></td>
                            <?php endif;?>
                        </tr>
                    <?php
                        $i++;
                        endforeach;
                    else:?>
                        <tr id="__fac_distribution_row">
                            <?php foreach($form_elements as $element):?>
                                <td>
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
                <tfoot>
                    <tr>
                        <th class="text-right">Total(%)</th>
                        <td id="__total_box" class="<?php echo $total_percentage == 100 ? 'text-green' : 'text-red';?>"><?php echo number_format($total_percentage, 2);?></td>
                        <td colspan="4">&nbsp;</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="box-footer bg-info">
            <a href="#" class="btn bg-teal" onclick="__duplicate_tr('#__fac_distribution_row', this)">Add More</a>
        </div>
    </div>

    <button type="submit" class="hide">Submit</button>
<?php echo form_close();?>
<script type="text/javascript">

    /**
     * Do the Math (Show total Percentnage)
     */
     $(document).on('keyup', 'input[name="fac_percent[]"]', function(){
        __compute_sum();
     });

    function __compute_sum()
    {
        var total = 0.00,
            $box = $('#__total_box');
        $('input[name="fac_percent[]').each(function() {
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

        // Reset/empty form input
        $('input, select, textarea', $row).val('');

        // Add Remover Column
        $row.append('<td width="10%" align="right"><a href="#" class="btn btn-danger btn-sm" onclick=\'$(this).closest("tr").remove();__compute_sum();\'>Remove</a></td>');

        // Append to table body
        $box.append($row);

        // Update Sum
        __compute_sum();
    }
</script>