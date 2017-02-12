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
                            'class' => 'form-iqb-general form-inline',
                            'data-pc' => '.bootbox-body' // parent container ID
                        ],
                        isset($record) ? ['id' => $record->id] : []); ?>

    <p>
        <a href="#" class="" title="Reset/Clear all permissions." onclick="$('input:checkbox.permission').iCheck('uncheck');">Reset All Permissions</a>
    </p>
    <?php foreach($permission_configs as $section_heading => $permission_modules):?>
        <div class="box box-primary box-bordered">
            <div class="box-header with-border border-dark gray">
                <h3 class="box-title">
                    <?php
                    echo $section_heading;

                    // Section Checkbox Class
                    $chk_cls_section = '_sct_'. bin2hex(random_bytes(8));
                    ?>
                </h3>
                <a href="#" class="pull-right" title="Reset/Clear <?php echo $chk_cls_section ?> permissions." onclick="$('input:checkbox.<?php echo $chk_cls_section;?>').iCheck('uncheck');">Reset</a>
            </div>
            <div class="box-body">
                <?php foreach($permission_modules as $module => $actions):?>
                    <div class="box-header with-border border-dark">
                        <h4 class="box-title text-bold">
                            <?php echo ucfirst($module) . ' Module' ?>
                        </h4>
                        <a href="#" class="pull-right" title="Reset/Clear <?php echo $module ?> permissions." onclick="$('input:checkbox.<?php echo $module;?>').iCheck('uncheck');">Reset</a>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <?php
                            $col_count = 0;
                            foreach($actions as $action):?>
                                <div class="col-sm-6 col-md-4" >
                                    <label style="font-weight:normal;">
                                        <?php
                                        $element_config = array(
                                            'name'          => $module.'[]',
                                            'class'         => 'icheck permission ' . $module . ' ' . $chk_cls_section
                                        );
                                        $value = $action;
                                        $old_actions = isset( $old_permissions->{$module} ) && !empty($old_permissions->{$module}) ? $old_permissions->{$module} : [];
                                        $checked = !empty( $old_actions ) && in_array($value, $old_actions);

                                         echo form_checkbox($element_config, $value, $checked);
                                         echo permission_text($action);
                                         ?>
                                    </label>
                                </div>
                                <?php
                                $col_count++;
                                if( $col_count%2 == 0 && $col_count%3 != 0)
                                {
                                    echo '<div class="clearfix visible-sm-block"></div>';
                                }
                                else if($col_count%3 == 0 && $col_count%2 == 0)
                                {
                                    echo '<div class="clearfix visible-sm-block visible-md-block"></div>';
                                }
                                else if($col_count%3 == 0 )
                                {
                                    echo '<div class="clearfix visible-md-block"></div>';
                                }
                                ?>
                            <?php endforeach;?>
                        </div>
                    </div>
                <?php endforeach?>
            </div>
        </div>
    <?php endforeach?>
    <button type="submit" class="hide">Submit</button>
<?php echo form_close();?>
