<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Group Elements
 */
$__box_id = isset($__box_id) ? 'id="'.$__box_id.'"' : '';
$__show_remove = $__show_remove ?? FALSE;
?>

<div class="box-body bg-gray-light box-removable">
    <div class="box box-solid box-bordered">
        <div class="box-body" <?php echo $__box_id?>>
            <?php
            /**
             * Load Form Components
             */
            $this->load->view('templates/_common/_form_components_inline', [
                'form_elements'     => $form_elements,
                'form_record'       => NULL,
                'inline_grid_width' => $inline_grid_width
            ]);
            ?>
        </div>
        <?php if($__show_remove === TRUE):?>
        <div class="box-footer text-right">
            <a href="#" class="btn btn-danger btn-sm" onclick="$(this).closest('.box-removable').fadeOut('fast', function(){$(this).remove()})">Remove</a>
        </div>
        <?php endif?>
    </div>
</div>


