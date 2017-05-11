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
            <label for="" class="<?php echo $grid_label; ?> control-label">
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
             * Load Form Element
             */
            $element_config = array(
                'name'          => $element['field'],
                'class'         => 'form-control',
                'placeholder'   => $element['label']
            );

            /**
             * Set Element ID if Set
             */
            if( isset($element['_id']) && $element['_id'] != '' )
            {
                $element_config['id'] = $element['_id'];
            }

            /**
             * What About if We have any Extra Attributes?
             */
            $extra_attributes = $element['_extra_attributes'] ?? '';


            $value = '';

            if($this->input->post())
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
                else if( isset($element['_key']) &&  isset($form_record->{$element['_key']}) )
                {
                    $value = $form_record->{$element['_key']};
                }
            }
            else
            {
                // Let's check if we have default value
                $value = $element['_default'] ?? '';
            }

            // Do we have _value field?
            if( $value  == '' )
            {
                $value = $element['_value'] ?? '';
            }


            /**
             * Do We have extra html, Just above the element?
             *
             *  HTML with JS or anything that is required in some cases to perform certain tasks
             */
            echo $element['_extra_html_above'] ?? '';


            /**
             * Let's Render the Element
             */
            switch($element['_type'])
            {
                case 'text':
                    echo form_input($element_config, $value, $extra_attributes);
                    break;

                case 'email':
                    echo form_email($element_config, $value, $extra_attributes);
                    break;

                case 'date':
                        echo    '<div class="input-group date" data-provide="datepicker" data-date-autoclose="true" data-date-format="yyyy-mm-dd" data-date-today-highlight="true">' .
                                    form_input($element_config, $value, $extra_attributes) .
                                    '<span class="input-group-addon"><i class="fa fa-calendar pointer"></i></span>' .
                                '</div>';
                        break;

                case 'datetime':
                        echo    '<div class="input-group datetime">' .
                                    form_input($element_config, $value, $extra_attributes) .
                                    '<span class="input-group-addon"><i class="fa fa-calendar pointer"></i></span>' .
                                '</div>';
                        break;

                case 'url':
                    echo form_url($element_config, $value, $extra_attributes);
                    break;

                case 'textarea':
                    // Rows/Cols
                    $element_config['rows'] = $element['rows'] ?? '10';
                    $element_config['cols'] = $element['cols'] ?? '40';
                    echo form_textarea($element_config, $value, $extra_attributes);
                    break;

                case 'dropdown':
                    $dropdown_data = $element['_data'] ?? ['' => 'Select...'];
                    echo form_dropdown($element_config, $dropdown_data, $value, $extra_attributes);
                    break;

                case 'checkbox':
                    $element_config['class'] = 'icheck'; // Add icheck style
                    // unset placeholder
                    unset($element_config['placeholder']);
                    $checked = $element['_checkbox_value'] == $value;
                    echo '<label>';
                        echo form_checkbox($element_config, $element['_checkbox_value'], $checked, $extra_attributes);
                        echo $element['label'];
                    echo '</label>';
                    break;


                case 'radio':
                    $element_config['class'] = 'icheck'; // Add icheck style
                    // unset placeholder
                    unset($element_config['placeholder']);
                    $radio_data = $element['_data'];
                    foreach($radio_data as $key=>$label_text)
                    {
                        $checked = $key == $value;
                        $element_config['id'] = 'radio-' . $key;
                        echo '<div class="radio-inline"><label for="radio-'.$key.'">' .
                                form_radio($element_config, $key, $checked, $extra_attributes) .
                                $label_text .
                             '</label></div>';
                    }
                    break;


                case 'switch':
                    $element_config['class'] = 'switch-checkbox';
                    $element_config['switch-type'] = 'switch-primary';
                    // unset placeholder
                    unset($element_config['placeholder']);
                    $checked = $element['_checkbox_value'] == $value;
                    echo form_switch($element_config, $element['_checkbox_value'], $checked, $extra_attributes);
                    break;
            }

            /**
             * Do We have extra html, Just below the element?
             *
             *  HTML with JS or anything that is required in some cases to perform certain tasks
             */
            echo $element['_extra_html_below'] ?? '';
            ?>

            <?php if(isset($element['_help_text'])):?><p class="help-block"><?php echo $element['_help_text']; ?></p><?php endif?>
            <?php if(form_error($element_config['name'])):?><span class="help-block"><?php echo form_error($element_config['name']); ?></span><?php endif?>
        </div>
    </div>
<?php endforeach?>