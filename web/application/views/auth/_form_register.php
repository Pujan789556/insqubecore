<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
$username = array(
	'name'	=> 'username',
	'id'	=> 'username',
	'size'	=> 30,
	'value' =>  set_value('username'),
	'class' => 'form-control',
	'placeholder' => 'Username'
);

$password = array(
	'name'	=> 'password',
	'id'	=> 'password',
	'size'	=> 30,
	'value' => set_value('password'),
	'class' => 'form-control',
	'placeholder' => 'Password'
);

$confirm_password = array(
	'name'	=> 'confirm_password',
	'id'	=> 'confirm_password',
	'size'	=> 30,
	'value' => set_value('confirm_password'),
	'class' => 'form-control',
	'placeholder' => 'Confirm Password'
);

$email = array(
	'name'	=> 'email',
	'id'	=> 'email',
	'maxlength'	=> 80,
	'size'	=> 30,
	'value'	=> set_value('email'),
	'class' => 'form-control',
	'placeholder' => 'Email Address'
);
?>

<?php echo form_open($this->uri->uri_string())?>
	<div class="form-group has-feedback <?php echo form_error($username['name']) ? 'has-error' : '';?>">
		<label for="<?php echo $username['id'];?>"><?php echo $username['placeholder'];?></label>
        <?php echo form_input($username)?>
        <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
        <?php if(form_error($username['name'])):?><span class="help-block"><?php echo form_error($username['name']); ?></span><?php endif?>
    </div>

    <div class="form-group has-feedback <?php echo form_error($password['name']) ? 'has-error' : '';?>">
		<label for="<?php echo $password['id'];?>"><?php echo $password['placeholder'];?></label>
        <?php echo form_password($password)?>
        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
        <?php if(form_error($password['name'])):?><span class="help-block"><?php echo form_error($password['name']); ?></span><?php endif?>
    </div>

    <div class="form-group has-feedback <?php echo form_error($confirm_password['name']) ? 'has-error' : '';?>">
		<label for="<?php echo $confirm_password['id'];?>"><?php echo $confirm_password['placeholder'];?></label>
        <?php echo form_password($confirm_password)?>
        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
        <?php if(form_error($confirm_password['name'])):?><span class="help-block"><?php echo form_error($confirm_password['name']); ?></span><?php endif?>
    </div>

    <div class="form-group has-feedback <?php echo form_error($email['name']) ? 'has-error' : '';?>">
		<label for="<?php echo $email['id'];?>"><?php echo $email['placeholder'];?></label>
        <?php echo form_input($email)?>
        <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
        <?php if(form_error($email['name'])):?><span class="help-block"><?php echo form_error($email['name']); ?></span><?php endif?>
    </div>

    <?php if ($this->dx_auth->captcha_registration): ?>
    	<div class="form-group <?php echo form_error('recaptcha_response_field') ? 'has-error' : '';?>">
    		<?php 
				// Show recaptcha imgage
				echo $this->dx_auth->get_recaptcha_image(); 
				// Show reload captcha link
				echo $this->dx_auth->get_recaptcha_reload_link(); 
				// Show switch to image captcha or audio link
				echo $this->dx_auth->get_recaptcha_switch_image_audio_link(); 
			?>
			<label for="recaptcha_response_field"><?php echo $this->dx_auth->get_recaptcha_label(); ?></label>
			<?php echo $this->dx_auth->get_recaptcha_input(); ?>

			<?php if( form_error('recaptcha_response_field') ):?>
				<span class="help-block"><?php echo form_error('recaptcha_response_field'); ?></span>
			<?php endif?>
				
			<?php 
			// Get recaptcha javascript and non javasript html
			echo $this->dx_auth->get_recaptcha_html();
			?>
    	</div>
    <?php endif;?>

    <div class="form-group">
        <button type="submit" class="btn btn-primary btn-block btn-flat">Register</button>
    </div>
<?php echo form_close()?>
<p>Already have an Account? <?php echo anchor(site_url($this->dx_auth->login_uri), 'Login');?></p>
