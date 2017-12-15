<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form Components - Single
 *
 */

/**
 * Load Form Element
 */
$element_config = array(
    'name'          => $element['field'],
    'class'         => 'form-control',
    'placeholder'   => $element['_placeholder'] ?? $element['label']
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

// Do we have _value field? It should be exactly BLANK (ZERO might come as defualt value)
if( $value  === '' )
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
    case 'hidden':
        echo form_hidden($element_config['name'], $value);
        break;

    case 'file':
        echo form_file($element_config, $value, $extra_attributes);
        break;

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
        // Show/Hide element Label
        $show_label = $element['_show_label'] ?? TRUE; // Default : True
        echo '<label>';
            echo form_checkbox($element_config, $element['_checkbox_value'], $checked, $extra_attributes);
            echo $show_label ? $element['label'] : '';
        echo '</label>';
        break;

    case 'checkbox-group':
        $element_config['class'] = 'icheck'; // Add icheck style
        // unset placeholder
        unset($element_config['placeholder']);
        $checkbox_data = $element['_data'];
        $checked_values = $element['_checkbox_value']; // Must be an array of value
        $label_class = isset($element['_list_inline']) && $element['_list_inline'] == true ? 'margin-r-10' : 'col-xs-12';
        foreach($checkbox_data as $key=>$label_text)
        {
            $checked = in_array($key, $checked_values);
            echo '<label class="'.$label_class.'">' .
                    form_checkbox($element_config, $key, $checked, $extra_attributes) .
                    $label_text .
                 '</label>';
        }
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