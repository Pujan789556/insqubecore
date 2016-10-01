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
        <label for="" class=""><?php echo $element['label'] . field_compulsary_text( $element['_required'] ?? FALSE );?></label> <span class="clearfix"></span>      
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
                $value = $value ? $value : ($element['_default'] ?? '');
                echo form_dropdown($element_config, $element['_data'], $value);
                break;

            default:
                break;                
        }
        ?>
        <?php if(isset($element['_help_text'])):?><p class="help-block"><?php echo $element['_help_text']; ?></p><?php endif?>
    </div>
<?php endforeach?>