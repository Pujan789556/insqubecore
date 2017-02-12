<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$login = array(
	'name'	=> 'login',
	'id'	=> 'login',
	'maxlength'	=> 80,
	'size'	=> 30,
	'value' => set_value('login'),
	'class' => 'form-control',
	'placeholder' => 'Your Username or Email Address'
);

?>

<div class="alert alert-warning">
	<p>If you've forgotten your password and/or username, enter the email address used for your account, and we will send you an e-mail with instructions on how to access your account.</p>
</div>

<?php 
/**
 * Load Auth Error If any
 */
$this->load->view('auth/_auth_error');
?>

<?php echo form_open($this->uri->uri_string()); ?>
	<div class="form-group has-feedback <?php echo form_error($login['name']) ? 'has-error' : '';?>">
        <?php echo form_input($login)?>
        <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
        <?php if(form_error($login['name'])):?><span class="help-block"><?php echo form_error($login['name']); ?></span><?php endif?>
    </div>

    <div class="form-group">
    	<button type="submit" class="btn btn-primary btn-block btn-flat">Send Email</button>
    </div>
<?php echo form_close()?>