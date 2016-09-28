<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : User
 */

$hidden = [
    'next_wizard' => isset($next_wizard) && $next_wizard ? 1 : 0
];
if (isset($record) )
{
    $hidden['id'] = $record->id;
}
?>
<?php echo form_open( $action_url,  
                        [
                            'class' => 'form-horizontal form-iqb-general',
                            'data-pc' => '.bootbox-body' // parent container ID
                        ], 
                        // Hidden Fields
                        $hidden); ?>
    <div class="box-header with-border">
        <h3 class="box-title"><?php echo $form_title?></h3>
    </div>
    <div class="box-body">    
        <?php foreach($form_elements as $element):?>        
            <div class="form-group <?php echo form_error($element['field']) ? 'has-error' : '';?>">
                <label for="" class="col-sm-2 control-label"><?php echo $element['label'] . field_compulsary_text( $element['_required']);?></label>
                <div class="col-sm-10">
                    <?php 
                    /**
                     * Load Form Element
                     */
                    $element_config = array(
                        'name'          => $element['field'],
                        'class'         => 'form-control',
                        'placeholder'   => $element['label']
                    );

                    $value = set_value($element['field']) ? set_value($element['field'], '', FALSE) : ( isset($form_record) ? $form_record->{$element['_key']} : '' );                   

                    switch($element['_type'])
                    {
                        case 'text':
                            echo form_input($element_config, $value);
                            break;

                        case 'password':
                            $element_config['autocomplete'] = 'off';
                            echo form_password($element_config, $value);
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
                            if($element_config['name'] == 'branch_id')
                            {
                                $_dropdown_list = [''=>'Select ...'] + $branches;
                            }
                            elseif($element_config['name'] == 'role_id')
                            {
                                $_dropdown_list = [''=>'Select ...'] + $roles;
                            }
                            else
                            {
                                $_dropdown_list = $element['_data'];
                            }
                            echo form_dropdown($element_config, $_dropdown_list, $value);
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
    <button type="submit" class="hide">Submit</button> 
<?php echo form_close();?>
