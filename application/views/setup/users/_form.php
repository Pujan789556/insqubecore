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
<style>
.select2-dropdown .select2-search__field:focus, .select2-search--inline .select2-search__field:focus{
    border:none;
}
</style>
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

                    $value = '';
                    // For Basic Form with Scope
                    if($element['field'] == 'scope[scope]')
                    {
                        $scope_list = [];
                        if(set_value($element['field']))
                        {
                            $value = set_value($element['field']);
                        }
                        else if(isset($record))
                        {
                            $scope = json_decode($record->scope); 
                            $value = $scope ? $scope->scope : '';
                            $scope_list = isset($scope->list) ? $scope->list : []; 
                        }                        
                    }                    
                    else if($element['_type'] != 'password')
                    {
                        $value = set_value($element['field']) ? set_value($element['field'], '', FALSE) : ( isset($form_record) ? $form_record->{$element['field']} : '' );
                    }                    

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
                            else if($element_config['name'] == 'department_id')
                            {
                                $_dropdown_list = [''=>'Select ...'] + $departments;
                            }
                            else if($element_config['name'] == 'role_id')
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

            <?php 
            if($element_config['name'] == 'scope[scope]'):
                $branch_config = [
                    'name' => 'scope[list][]',
                    'multiple' => 'multiple',
                    'class'     => 'form-control select-multiple',
                    'id'        => 'select-multiple',
                    'data-placeholder' => 'Select branch(es)',
                    'style'     => 'width:100%'
                ];
            ?>
                <div class="form-group <?php echo form_error($branch_config['name']) ? 'has-error' : '';?>" id="scope-list" style="display:<?php echo $value == 'branch' ? 'block' : 'none';?>">
                    <div class="col-sm-10 col-sm-offset-2">
                        <label>Select Branch(es)<?php echo field_compulsary_text(true);?></label>
                        <?php 

                        echo form_dropdown($branch_config, $branches, $scope_list);?>  
                        <?php if(form_error($branch_config['name'])):?><span class="help-block"><?php echo form_error($branch_config['name']); ?></span><?php endif?>                      
                    </div>
                </div>
            <?php endif?>

        <?php endforeach?>  
    </div>     
    <button type="submit" class="hide">Submit</button> 
<?php echo form_close();?>

<!-- Select2 -->
<script>    
    $.getScript( "<?php echo THEME_URL; ?>plugins/select2/select2.full.min.js", function( data, textStatus, jqxhr ) {
        //Initialize Select2 Elements
        $(".select-multiple").select2();

        $('select[name="scope[scope]"]').on('change', function(e){
            var v = $(this).val(),
            $list = $('#select-multiple');
            if(v === 'branch'){
                $('#scope-list').fadeIn();
            }else{
                $('#scope-list').fadeOut();
                $list.val('').trigger('change'); // reset the list
            }
        });
    });
</script>