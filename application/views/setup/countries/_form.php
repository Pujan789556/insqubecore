<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Countries
 */
?>
<?php echo form_open( $this->uri->uri_string(), 
                                [
                                    'class' => 'form-horizontal form-iqb-general',
                                    'data-pc' => '.bootbox-body' // parent container ID
                                ]); ?>
    
    <?php foreach($form_elements as $element):?>        
        <div class="form-group <?php echo form_error($element['name']) ? 'has-error' : '';?>">
            <label for="<?php echo $element['_id'] ?>" class="col-sm-2 control-label"><?php echo $element['label'] . field_compulsary_text( $element['_required']);?></label>
            <div class="col-sm-10">
                <?php 
                /**
                 * Load Form Element
                 */
                $element_config = array(
                    'name'          => $element['name'],
                    'id'            => $element['_id'],
                    'class'         => 'form-control',
                    'placeholder'   => $element['label']
                );
                $value = set_value($element['name']) ? set_value($element['name'], '', FALSE) : $record->{$element['name']};

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
                        echo form_dropdown($element_config, $element['_data'], $value);
                        break;

                    case 'switch':
                        $element_config['class'] = 'switch-checkbox';
                        $element_config['switch-type'] = 'switch-primary';
                        echo form_switch($element_config, $element['_data'], set_value($element['name']) || $record->{$element['name']});
                        break;
                }
                ?>
                <?php if(form_error($element_config['name'])):?><span class="help-block"><?php echo form_error($element_config['name']); ?></span><?php endif?>
            </div>
        </div>
    <?php endforeach?>   
    <button type="submit" class="hide">Submit</button> 
<?php echo form_close();?>
