<?php
$old_password = array(
	'name'	=> 'old_password',
	'id'		=> 'old_password',
	'size' 	=> 30,
	'value' => set_value('old_password'),
	'class' => 'form-control',
	'placeholder' => 'Old Password'
);

$new_password = array(
	'name'	=> 'new_password',
	'id'		=> 'new_password',
	'size'	=> 30,
	'class' => 'form-control',
	'placeholder' => 'New Password'
);

$confirm_new_password = array(
	'name'	=> 'confirm_new_password',
	'id'		=> 'confirm_new_password',
	'size' 	=> 30,
	'class' => 'form-control',
	'placeholder' => 'Confirm New Password'
);

?>

<?php echo form_open($this->uri->uri_string()); ?>
	<div class="form-group has-feedback <?php echo form_error($old_password['name']) ? 'has-error' : '';?>">
		<label for="<?php echo $old_password['id'];?>"><?php echo $old_password['placeholder'];?></label>
        <?php echo form_password($old_password)?>
        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
        <?php if(form_error($old_password['name'])):?><span class="help-block"><?php echo form_error($old_password['name']); ?></span><?php endif?>
    </div>
    <div class="form-group has-feedback <?php echo form_error($new_password['name']) ? 'has-error' : '';?>">
    	<label for="<?php echo $new_password['id'];?>"><?php echo $new_password['placeholder'];?></label>
        <?php echo form_password($new_password)?>
        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
        <?php if(form_error($new_password['name'])):?><span class="help-block"><?php echo form_error($new_password['name']); ?></span><?php endif?>
    </div>
    <div class="form-group has-feedback <?php echo form_error($confirm_new_password['name']) ? 'has-error' : '';?>">
    	<label for="<?php echo $confirm_new_password['id'];?>"><?php echo $confirm_new_password['placeholder'];?></label>
        <?php echo form_password($confirm_new_password)?>
        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
        <?php if(form_error($confirm_new_password['name'])):?><span class="help-block"><?php echo form_error($confirm_new_password['name']); ?></span><?php endif?>
    </div>

    <div class="form-group">
        <button type="submit" class="btn btn-primary btn-block btn-flat">Change Password</button>
    </div>
<?php echo form_close();?>