<?php defined('BASEPATH') OR exit('No direct script access allowed');
$username = array(
	'name'	=> 'username',
	'id'	=> 'username',
	'size'	=> 30,
	'value' => set_value('username'),
	'class' => 'form-control',
	'placeholder' => 'Username or Email'
);

$password = array(
	'name'	=> 'password',
	'id'	=> 'password',
	'size'	=> 30,
	'class' => 'form-control',
	'placeholder' => 'Password'
);

$remember = array(
	'name'	=> 'remember',
	'id'	=> 'remember',
	'value'	=> 1,
	'checked'	=> set_value('remember') ? set_value('remember') : 1	
);

$confirmation_code = array(
	'name'	=> 'captcha',
	'id'	=> 'captcha',
	'maxlength'	=> 8
);
?>

<?php 
/**
 * Load Auth Error If any
 */
$this->load->view('auth/_auth_error');
?>

<?php echo form_open($this->uri->uri_string())?>
	<div class="form-group has-feedback <?php echo form_error($username['name']) ? 'has-error' : '';?>">
        <?php echo form_input($username)?>
        <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
        <?php if(form_error($username['name'])):?><span class="help-block"><?php echo form_error($username['name']); ?></span><?php endif?>
    </div>

    <div class="form-group has-feedback <?php echo form_error($password['name']) ? 'has-error' : '';?>">
        <?php echo form_password($password)?>
        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
        <?php if(form_error($password['name'])):?><span class="help-block"><?php echo form_error($password['name']); ?></span><?php endif?>
    </div>
    <?php if ($show_captcha): ?>
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
    <?php endif ?>

    <div class="form-group">
        <button type="submit" class="btn btn-primary btn-block btn-flat">Login</button>
    </div>

    <div class="row">
        <div class="col-xs-12">
            <div class="checkbox icheck">
                <label>
                    <?php echo form_checkbox($remember);?> Remember Me
                </label>
            </div>
        </div>
        <!-- /.col -->
        <div class="col-xs-12">
        	<p>
	        	<?php echo anchor($this->dx_auth->forgot_password_uri, 'Forgot password');?>
	        	<?php if ($this->dx_auth->allow_registration):?>
	        		&nbsp;|&nbsp;<?php echo anchor($this->dx_auth->register_uri, 'Register');?>        		
	    		<?php endif?>
    		</p>
        </div>
        <!-- /.col -->
    </div>
<?php echo form_close()?>