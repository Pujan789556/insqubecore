<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Setup - RI - Treaties - Portfolios
 */
?>
<style type="text/css">
input.form-control, select.form-control{height:24px; max-width: 120px;}
</style>
<?php echo form_open( $this->uri->uri_string(),
                        [
                            'class' => 'form-iqb-general',
                            'id'    => '__form-treaty-setup-distribution',
                            'data-pc' => '.bootbox-body' // parent container ID
                        ],
                        // Hidden Fields
                        isset($record) ? ['id' => $record->id] : []); ?>
    <div class="row">
        <div class="col-md-4">
            <?php
            /**
             * Basic Overview
             */
            $this->load->view('setup/ri/treaties/snippets/_ri_basic');
            ?>
        </div>
    </div>
    <div style="overflow-x: scroll;">
        <table class="table table-bordered table-hover table-condensed">
            <thead style="background: #f9f9f9;">
                <tr>
                    <th>Portfolio</th>
                    <?php foreach($form_elements as $element):?>
                        <th class="<?php echo $element['_type'] == 'hidden' ? 'hide' : ''?>"><?php echo $element['label']?></th>
                    <?php endforeach?>
                </tr>
            </thead>

            <tbody class="form-inline">
                <?php foreach ($portfolios as $portfolio):?>
                    <tr>
                        <td><?php echo $portfolio->portfolio_name_en;?></td>
                        <?php foreach($form_elements as $element):?>
                            <?php if($element['_type'] == 'hidden'):?>
                                <?php echo form_hidden($element['field'], $portfolio->{$element['_field']});?>
                            <?php else:?>
                                <td>
                                    <?php
                                    /**
                                     * Load Single Element
                                     */
                                    $element['_default']    = $portfolio->{$element['_field']} ?? '';
                                    $element['_value']      = $element['_default'];
                                    $this->load->view('templates/_common/_form_components_inline', [
                                        'form_elements' => [$element],
                                        'form_record'   => NULL
                                    ]);
                                    ?>
                                </td>
                                <?php endif?>
                        <?php endforeach;?>
                    </tr>
                <?php endforeach;?>
            </tbody>
        </table>
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