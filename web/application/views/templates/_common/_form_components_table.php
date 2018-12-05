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
foreach($form_elements as $element):?>
    <td class="<?php echo form_error($element['field']) ? 'has-error' : '';?>">
       <?php
        /**
         * Load Single Element
         */
        $this->load->view('templates/_common/_form_components_single', ['element' => $element]);
       ?>
    </td>
<?php endforeach?>