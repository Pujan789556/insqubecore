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
        <label for="" class="<?php echo $grid_label; ?> control-label"><?php echo $element['label'] . field_compulsary_text( $element['_required'] ?? FALSE );?></label>
        <div class="<?php echo $grid_form_control; ?>">
            <?php 
            /**
             * Load Form Element
             */
            $element_config = array(
                'name'          => $element['field'],
                'class'         => 'form-control',
                'placeholder'   => $element['label']
            );

            $value = '';
            if(set_value($element['field']))
            {
                $value = set_value($element['field'], '', FALSE);
            }
            else if( isset($form_record) )
            {
                // Regular Form Field
                if( isset( $form_record->{$element['field']} ) )
                {
                    $value = $form_record->{$element['field']};
                }
                // Json Object Key
                else if( isset($form_record->{$element['_key']}) )
                {
                    $value = $form_record->{$element['_key']};
                }
            }

            switch($element['_type'])
            {
                case 'text':
                    echo form_input($element_config, $value);
                    break;

                case 'email':
                    echo form_email($element_config, $value);
                    break;

                case 'date':
                        echo form_date($element_config, $value);
                        break;

                case 'url':
                    echo form_url($element_config, $value);
                    break;

                case 'textarea':
                    echo form_textarea($element_config, $value);
                    break;

                case 'dropdown':
                    // Let's check if we have default value
                    $value = isset($value) ? $value : ($element['_default'] ?? '');
                    echo form_dropdown($element_config, $element['_data'], $value);
                    break;

                case 'checkbox':
                    $element_config['class'] = 'icheck'; // Add icheck style
                    $checked = $element['_default'] == $value;
                    echo form_checkbox($element_config, $value, $checked);
                    break;

                case 'switch':
                    $element_config['class'] = 'switch-checkbox';
                    $element_config['switch-type'] = 'switch-primary';
                    echo form_switch($element_config, $element['_data'], set_value($element['field']) || $record->{$element['field']});
                    break;
            }
            ?>
            <?php if(isset($element['_help_text'])):?><p class="help-block"><?php echo $element['_help_text']; ?></p><?php endif?>

            <?php if(form_error($element_config['name'])):?><span class="help-block"><?php echo form_error($element_config['name']); ?></span><?php endif?>
        </div>
    </div>
<?php endforeach?>