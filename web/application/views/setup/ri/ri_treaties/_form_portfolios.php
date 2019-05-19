<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Setup - RI - Treaties - Portfolios
 */
?>
<style type="text/css">
input.form-control, select.form-control{height:24px; max-width: 120px;}
.apply-all-box.focus{min-width: 300px;}
.apply-all-toggler{padding: 2px 3px;}
</style>
<?php echo form_open( $this->uri->uri_string(),
                        [
                            'class' => 'form-iqb-general',
                            'id'    => '__form-treaty-setup-distribution',
                            'data-pc' => '.bootbox-body' // parent container ID
                        ],
                        // Hidden Fields
                        isset($record) ? ['treaty_id' => $record->id] : []); ?>
    <div class="row">
        <div class="col-md-4">
            <?php
            /**
             * Basic Overview
             */
            $this->load->view($this->data['_view_base'] . '/snippets/_ri_basic');
            ?>
        </div>
    </div>
    <div style="overflow-x: scroll;">
        <table class="table table-bordered table-hover table-condensed">
            <thead style="background: #f9f9f9;">
                <tr>
                    <th>Portfolio</th>
                    <?php foreach($form_elements as $element):?>
                        <th class="<?php echo $element['_type'] == 'hidden' ? 'hide' : ''?>">
                            <?php echo $element['label']?>
                            <?php
                            $form_field = $element['field'];
                            $input_type = FALSE;
                            if($element['_type'] == 'text'){
                                $input_type = 'input';
                            }
                            if($element['_type'] == 'textarea'){
                                $input_type = 'textarea';
                            }
                            if($element['_type'] == 'dropdown'){
                                $input_type = 'select';
                            }
                            ?>
                            <?php if($input_type): ?>

                                <div class="form-inline apply-all-box hide ">
                                    <div class="input-group input-group-sm">
                                        <input type="text" class="form-control apply-all" autocomplete="off"
                                            data-field-type="<?php echo $input_type ?>"
                                            data-field-name="<?php echo $form_field ?>",
                                        >
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-sm btn-default btn-flat btn-apply-all">Apply All</button>
                                        </span>
                                    </div>
                                </div>
                                <p class="no-margin">
                                    <a href="#" class="btn-info btn-sm apply-all-toggler" data-toggle="tooltip" title="Click here show/hide">Apply All</a>
                                </p>
                            <?php endif; ?>
                        </th>
                    <?php endforeach?>
                </tr>
            </thead>

            <tbody class="form-inline">
                <?php foreach ($portfolios as $portfolio):?>
                    <tr>
                        <td><?php echo $portfolio->portfolio_name_en;?> (<?php echo $portfolio->treaty_distribution_for ? IQB_PORTFOLIO_LIABILITY_OPTION__LIST[$portfolio->treaty_distribution_for]: '-'?>)</td>
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

    $('.apply-all-toggler').on('click', function(e){
        e.preventDefault();

        // This box
        var $this = $(this),
        $box = $this.closest('th').find('.apply-all-box');

        // hide all apply-all-box
        $('.apply-all-box').addClass('hide');

        // all text same
        $('.apply-all-toggler').html('Apply All');

        if($box.hasClass('open')){
            // Hide The Box
            $box.removeClass('open')
                .addClass('hide');

            $this.html('Apply All');
        }else{

            // remove open from any other box
            $('.apply-all-box').removeClass('open');

            // Open current Box
            $box.removeClass('hide');
            $box.addClass('open');
            $('input', $box).focus().select().trigger('focus');
            $(this).html('Hide');
        }
    });

    $('.apply-all').on('focus', function(e){
        var $this = $(this),
            $box = $this.closest('.apply-all-box');

            // Remove focus if any on any other apply-all-box
            $('.apply-all-box').removeClass('focus');
            $box.addClass('focus');
    });

    // Prevent Form Submission on Enter
    $('.apply-all').on('keydown', function(e){
        if(e.keyCode == 13) {
            e.preventDefault();

            // Apply That Value to all corresponding fields
            $(this).closest('.apply-all-box').find('.btn-apply-all').trigger('click');
            return false;
        }
    });

    // Apply Value to all
    $('.btn-apply-all').on('click', function(e){
        e.preventDefault();

        var $this = $(this),
            $input = $this.closest('.apply-all-box').find('input.apply-all');
            field = $input.data('field-name'),
            type  = $input.data('field-type'),
            val   = $input.val();

        var frm_elem = type + '[name="' + field + '"]'; // column input field's name

        $(frm_elem).val(val); // apply the supplied value to all
    });
</script>
