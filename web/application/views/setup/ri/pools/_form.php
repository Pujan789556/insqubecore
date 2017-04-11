<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Setup - RI - Pools
 */
?>
<style type="text/css">
table input.form-control, table select.form-control{max-width: 150px;}
</style>
<?php echo form_open( $this->uri->uri_string(),
                        [
                            'class' => 'form-iqb-general',
                            'id'    => '__form-pool-setup',
                            'data-pc' => '.bootbox-body' // parent container ID
                        ],
                        // Hidden Fields
                        isset($record) ? ['id' => $record->id] : []); ?>
    <div class="box box-default box-bordered">
        <div class="box-header with-border">
          <h4 class="box-title">Pool Basic Information</h4>
        </div>
        <div class="box-body form-horizontal">
            <?php
            /**
             * Load Form Components
             */
            $this->load->view('templates/_common/_form_components_horz', [
                'form_elements' => $form_elements['basic'],
                'form_record'   => $record
            ]);
            ?>
        </div>
    </div>

    <?php
    /**
     * Pool Portfolio Table
     */
    $this->load->view(  'setup/ri/pools/_form_portfolios_inline',
                        [
                            'form_elements'     => $form_elements['portfolios'],
                            'pool_portfolios' => $pool_portfolios
                        ]);


    /**
     * Pool Distribution Table
     */
    $this->load->view(  'setup/ri/pools/_form_distribution_inline',
                        [
                            'form_elements'         => $form_elements['reinsurers'],
                            'pool_distribution'     => $pool_distribution
                        ]);
    ?>

    <button type="submit" class="hide">Submit</button>
<?php echo form_close();?>
<script type="text/javascript">
    // Datepicker
    $('.input-group.date').datepicker({
        autoclose: true,
        todayHighlight: true,
        format: 'yyyy-mm-dd'
    });

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