<?php defined('BASEPATH') OR exit('No direct script access allowed');
$password = array(
	'name'	=> 'password',
	'id'		=> 'password',
	'size' 	=> 30,
	'class' => 'form-control',
	'placeholder' => 'Password'
);
?>

<div class="alert alert-warning">
	<h4>Warning!!!</h4>
	<p>Canceling this account will completly delete your account and data associated with it.</p>
	<p class="alert alert-danger">Are you sure you want to perform this action? It can not be UNDONE!</p>
</div>
<?php 
/**
 * Load Auth Error If any
 */
$this->load->view('auth/_auth_error');
?>

<?php echo form_open($this->uri->uri_string())?>
	<div class="form-group has-feedback <?php echo form_error($password['name']) ? 'has-error' : '';?>">
        <?php echo form_password($password)?>
        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
        <?php if(form_error($password['name'])):?><span class="help-block"><?php echo form_error($password['name']); ?></span><?php endif?>
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-primary btn-block btn-flat">Delete Account</button>
    </div>
<?php echo form_close();?>