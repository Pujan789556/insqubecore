<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Role Permissions
 */
$old_permissions = ( isset($record->permissions) && !empty($record->permissions) ) 
                    ? json_decode($record->permissions) : [];
?>
<?php echo form_open( $this->uri->uri_string(), 
                        [
                            'class' => 'form-horizontal form-iqb-general',
                            'data-pc' => '.bootbox-body' // parent container ID
                        ], 
                        isset($record) ? ['id' => $record->id] : []); ?>
    
    <?php foreach($all_permissions as $module => $actions):?>        
        <div class="form-group">
            <label for="<?php echo $module ?>" class="col-sm-2 control-label"><?php echo ucfirst($module) . ' Module' ?></label>
            <div class="col-sm-10">
                <?php foreach($actions as $action):?>
                    <label>
                        <?php
                        $element_config = array(
                            'name'          => $module.'[]',
                            'class'         => 'icheck'
                        );
                        $value = $action;
                        $old_actions = isset( $old_permissions->{$module} ) && !empty($old_permissions->{$module}) ? $old_permissions->{$module} : [];  
                        $checked = !empty( $old_actions ) && in_array($value, $old_actions);

                         echo form_checkbox($element_config, $value, $checked);
                         echo permission_text($action);
                         ?>
                    </label><div class="clearfix"></div>
                <?php endforeach;?>
            </div>
        </div>
    <?php endforeach?>   
    <button type="submit" class="hide">Submit</button> 
<?php echo form_close();?>
