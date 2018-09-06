<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form Horizontal Components
 *
 * This is for regular form fields, But no array/json form fields
 *
 * Required Variable:
 *      $form_elements
 *      $form_record
 *      $grid_label = 'col-sm-2'
 *      $grid_form_control = 'col-sm-10'
 */
$grid_label = $grid_label ?? 'col-sm-2';
$grid_form_control = $grid_form_control ?? 'col-sm-10';
foreach($form_elements as $element):?>
    <div class="form-group <?php echo form_error($element['field']) ? 'has-error' : '';?>">
        <?php
        /**
         * Show/Hide Label
         */
        $show_label = $element['_show_label'] ?? TRUE; // Default : True
        if($show_label):
        ?>
            <label class="<?php echo $grid_label; ?> control-label">
                <?php
                $_show_label = $element['_show_label'] ?? FALSE;
                if( $_show_label === TRUE || !in_array($element['_type'], ['checkbox', 'radio']))
                {
                    echo $element['label'] . field_compulsary_text( $element['_required'] ?? FALSE );
                }
                else
                {
                    echo '&nbsp;';
                }
                ?>
            </label>
        <?php endif;?>

        <div class="<?php echo $grid_form_control; ?>">
            <?php
            /**
             * Load Single Element
             */
            $this->load->view('templates/_common/_form_components_single', ['element' => $element]);
            ?>
        </div>
    </div>
<?php endforeach?>