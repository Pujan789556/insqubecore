<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Policy TXN - Installment Configuration
 */
?>
<style type="text/css">
    .table-installment  .form-group{margin-bottom: 0}
</style>
<div class="box box-solid box-bordered">
    <div class="box-header with-border">
        <h4 class="box-title">Premium Installment Information</h4>
    </div>
    <div class="box-body">
        <?php
        $installments = _POLICY_INSTALLMENT_list_by_transaction($endorsement_record->id);
        $item_count          = count( $installments ?? [] );
        ?>
        <table class="table table-bordered table-condensed no-margin table-installment">
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
                    $total_percentage = 0.00;
                    if($item_count):
                        $i = 0;
                        foreach ($installments as $single):?>
                            <tr <?php echo $i == 0 ? 'id="__bl_installment_row"' : '' ?>>
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

                                        // Total Percentage
                                        if($elem['_key'] == 'percent')
                                        {
                                            $total_percentage += $value;
                                        }
                                        ?>
                                    </td>
                                <?php
                                endforeach;
                                if($i == 0):?>
                                    <td width="20%" class="text-success"><i class="fa fa-check"></i> First Installment</td>
                                <?php else:?>
                                    <td width="20%"><a href="#" class="btn btn-danger btn-sm" onclick='$(this).closest("tr").remove();__compute_sum();'><i class="fa fa-trash"></i></a></td>
                                <?php endif;?>
                            </tr>
                    <?php
                            $i++;
                        endforeach;
                    else:?>
                        <tr id="__bl_installment_row">
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
                            <td width="20%" class="text-success"><i class="fa fa-exclamation"></i> First Installment</td>
                        </tr>
                    <?php endif;?>
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-right">Total(%)</th>
                    <td id="__total_box" class="<?php echo $total_percentage == 100 ? 'text-green' : 'text-red';?>"><?php echo number_format($total_percentage, 2, '.', '');?></td>
                    <td>&nbsp;</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="box-footer bg-info">
        <a href="#" class="btn bg-teal" onclick="__duplicate_tr('#__bl_installment_row', this)">Add More</a>
    </div>
</div>

<script type="text/javascript">
    /**
     * Do the Math (Show total Percentnage)
     */
     $(document).on('keyup', 'input[name="percent[]"]', function(){
        __compute_sum();
     });

    function __compute_sum()
    {
        var total = 0.00,
            $box = $('#__total_box');
        $('input[name="percent[]').each(function() {
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

        // blank all field
        $('input, select, textarea', $row).val('');

        // remove last blank td
        $row.find('td:last').remove();

        // Add Remover Column
        $row.append('<td width="20%"><a href="#" class="btn btn-danger btn-sm" onclick=\'$(this).closest("tr").remove();__compute_sum();\'><i class="fa fa-trash"></i></a></td>');

        // Append to table body
        $box.append($row);
    }
</script>