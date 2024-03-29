<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Search Filter Form Components
 *
 * Required Variable:
 *      $filters
 */
foreach($filters as $element):?>
    <div class="form-group">
        <?php
        /**
         * Label Extra Information Such as tooltip etc
         */
        $label_extra = $element['_label_extra'] ?? '';
        ?>
        <label for="" <?php echo $label_extra?>><?php echo $element['label'] . field_compulsary_text( $element['_required'] ?? FALSE );?></label> <span class="clearfix"></span>
        <?php
        /**
         * Load Form Element
         */
        $element_config = array(
            'name'          => $element['field'],
            'class'         => $element['_class'] ?? 'form-control'
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

        // Let's check if we have default value (might have on date fields)
        $value = $element['_default'] ?? '';

        switch($element['_type'])
        {
            case 'text':
                echo form_input($element_config, $value, $extra_attributes);
                break;

            case 'email':
                echo form_email($element_config, $value, $extra_attributes);
                break;

            // case 'date':
            //         echo form_date($element_config, $value, $extra_attributes);
            //         break;

            case 'date':
                echo    '<div class="input-group date">' .
                            form_input($element_config, $value, $extra_attributes) .
                            '<span class="input-group-addon"><i class="fa fa-calendar pointer"></i></span>' .
                        '</div>';
                break;

            case 'url':
                echo form_url($element_config, $value, $extra_attributes);
                break;

            case 'textarea':
                echo form_textarea($element_config, $value, $extra_attributes);
                break;

            case 'dropdown':
                // Let's check if we have default value
                $value = $value ? $value : ($element['_default'] ?? '');
                $dropdown_data = $element['_data'] ?? ['' => 'Select...'];
                echo form_dropdown($element_config, $dropdown_data, $value, $extra_attributes);
                break;

            default:
                break;
        }
        ?>
        <?php if(isset($element['_help_text'])):?><p class="help-block"><?php echo $element['_help_text']; ?></p><?php endif?>
    </div>
<?php endforeach?>