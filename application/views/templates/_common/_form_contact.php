<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Contact Form
 * 
 * 	Required Variables:
 * 
 * 		$contact_record
 */
?>
<div class="box-header with-border">
  <h3 class="box-title">Contact Address</h3>
</div>
<div class="box-body">        
    <?php 
    $contact_form_elements = get_contact_form_fields();
    foreach($contact_form_elements as $element):?>        
        <div class="form-group <?php echo form_error($element['name']) ? 'has-error' : '';?>">
            <label for="" class="col-sm-2 control-label"><?php echo $element['label'] . field_compulsary_text( $element['_required']);?></label>
            <div class="col-sm-10">
                <?php 
                /**
                 * Load Form Element
                 */
                $element_config = array(
                    'name'          => $element['name'],
                    'class'         => 'form-control',
                    'placeholder'   => $element['label']
                );
                $value = set_value($element['name']) 
                        ? set_value($element['name'], '', FALSE) 
                        : ( isset($contact_record) ? ($contact_record->{$element['_key']} ?? '') : '' );

                switch($element['_type'])
                {
                    case 'text':
                        echo form_input($element_config, $value);
                        break;

                    case 'email':
                        echo form_email($element_config, $value);
                        break;

                    case 'url':
                        echo form_url($element_config, $value);
                        break;

                    case 'textarea':
                        echo form_textarea($element_config, $value);
                        break;

                    case 'dropdown':
                        // Let's check if we have default value
                        $value = $value ? $value : $element['_default'];
                        echo form_dropdown($element_config, $element['_data'], $value);
                        break;

                    case 'switch':
                        $element_config['class'] = 'switch-checkbox';
                        $element_config['switch-type'] = 'switch-primary';
                        echo form_switch($element_config, $element['_data'], set_value($element['name']) || $record->{$element['name']});
                        break;
                }
                ?>
                <?php if(isset($element['_help_text'])):?><p class="help-block"><?php echo $element['_help_text']; ?></p><?php endif?>

                <?php if(form_error($element_config['name'])):?><span class="help-block"><?php echo form_error($element_config['name']); ?></span><?php endif?>
            </div>
        </div>
    <?php endforeach?>
</div>