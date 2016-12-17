<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends MY_Controller
{
	// Used for registering and changing password form validation
	var $min_username = 4;
	var $max_username = 20;
	var $min_password = 4;
	var $max_password = 40;

	function __construct()
	{
		parent::__construct();

		// Form Validation
		$this->load->library('Form_validation');

		// Set Template for this controller
        $this->template->set_template('login');
	}

	function index()
	{
		$this->login();
	}


	/* Callback function */

	function username_check($username)
	{
		$result = $this->dx_auth->is_username_available($username);
		if ( ! $result)
		{
			$this->form_validation->set_message('username_check', 'Username already exist. Please choose another username.');
		}

		return $result;
	}

	function email_check($email)
	{
		$result = $this->dx_auth->is_email_available($email);
		if ( ! $result)
		{
			$this->form_validation->set_message('email_check', 'Email is already used by another user. Please choose another email address.');
		}

		return $result;
	}


	function recaptcha_check()
	{
		$result = $this->dx_auth->is_recaptcha_match();
		if ( ! $result)
		{
			$this->form_validation->set_message('recaptcha_check', 'Your confirmation code does not match the one in the image. Try again.');
		}

		return $result;
	}

	/* End of Callback function */


	function login()
	{

		if ( $this->dx_auth->is_logged_in())
		{
			redirect('dashboard');
		}

		$val = $this->form_validation;

		// Set form validation rules
		$val->set_rules('username', 'Username', 'trim|required');
		$val->set_rules('password', 'Password', 'trim|required');
		$val->set_rules('remember', 'Remember me', 'integer');

		// Set captcha rules if login attempts exceed max attempts in config
		if ($this->dx_auth->is_max_login_attempts_exceeded())
		{
			//  $val->set_rules('captcha', 'Confirmation Code', 'trim|required|callback_captcha_check');

			// Set recaptcha rules.
			// IMPORTANT: Do not change 'recaptcha_response_field' because it's used by reCAPTCHA API,
			// This is because the limitation of reCAPTCHA, not DX Auth library
			$val->set_rules('recaptcha_response_field', 'Confirmation Code', 'trim|required|callback_recaptcha_check');
		}

		if ($val->run() AND $this->dx_auth->login($val->set_value('username'), $val->set_value('password'), $val->set_value('remember')))
		{
			// Redirect to homepage
			// redirect('', 'location');

			// login success, redirect to dashboard
			redirect('dashboard');
		}
		else
		{
			// Check if the user is failed logged in because user is banned user or not
			if ( $this->dx_auth->is_banned() )
			{
				// Redirect to banned uri
				// $this->dx_auth->deny_access('banned');
				$this->banned();
			}
			else
			{
				// Default is we don't show captcha until max login attempts eceeded
				$data['show_captcha'] = FALSE;


				// Show captcha if login attempts exceed max attempts in config
				if ($this->dx_auth->is_max_login_attempts_exceeded())
				{
					// Create catpcha
					// $this->dx_auth->captcha();

					// Set view data to show captcha on view file
					$data['show_captcha'] = TRUE;
				}

				// Load login page view
				// $this->load->view($this->dx_auth->login_view, $data);
				$this->template->partial('body', $this->dx_auth->login_view, $data)
						        ->render([
						        	'site_title' => 'Login',
						        	'page_title' => 'Login'
						    	]);
			}
		}
	}

	function logout()
	{
		$this->dx_auth->logout();

		// redirect
		redirect('');
	}


	function register()
	{
		// Check logged in?
		if ( $this->dx_auth->is_logged_in())
		{
			redirect('dashboard');
		}

		// Registration Allowed?
		if ( !$this->dx_auth->allow_registration )
		{
			$this->template->render_404();
		}

		// View data
		$data = [];

		$val = $this->form_validation;

		// Set form validation rules
		$val->set_rules('username', 'Username', 'trim|required|min_length['.$this->min_username.']|max_length['.$this->max_username.']|callback_username_check|alpha_dash');
		$val->set_rules('password', 'Password', 'trim|required|min_length['.$this->min_password.']|max_length['.$this->max_password.']|matches[confirm_password]');
		$val->set_rules('confirm_password', 'Confirm Password', 'trim|required');
		$val->set_rules('email', 'Email', 'trim|required|valid_email|callback_email_check');

		// Is registration using captcha
		if ($this->dx_auth->captcha_registration)
		{
			// Set recaptcha rules.
			// IMPORTANT: Do not change 'recaptcha_response_field' because it's used by reCAPTCHA API,
			// This is because the limitation of reCAPTCHA, not DX Auth library
			$val->set_rules('recaptcha_response_field', 'Confirmation Code', 'trim|required|callback_recaptcha_check');
		}

		// Run form validation and register user if it's pass the validation
		if ($val->run() AND $this->dx_auth->register($val->set_value('username'), $val->set_value('password'), $val->set_value('email')))
		{
			// Set success message accordingly
			if ($this->dx_auth->email_activation)
			{
				$data['auth_message'] = '<strong>Congratulations!</strong><br/><br/>You have successfully registered. Please check your email address to activate your account.';
			}
			else
			{
				$data['auth_message'] = '<strong>Congratulations!</strong><br/><br/>You have successfully registered. '.anchor(site_url($this->dx_auth->login_uri), 'Login');
			}

			$data['alert_type'] = 'success';

			// Load registration success page
			// $this->load->view($this->dx_auth->register_success_view, $data);
			$view = $this->dx_auth->register_success_view;
		}
		else
		{
			// Load registration page
			$view = $this->dx_auth->register_view;
		}

		// Render form
    	$this->template->partial(
        					'body',
        					$view,
    						$data
						)
        				->render([
				        	'site_title' => 'Register',
				        	'page_title' => 'Register'
				    	]);
	}

	function activate()
	{
		if ( $this->dx_auth->is_logged_in())
		{
			redirect('dashboard');
		}

		// Get username and key
		$username = $this->uri->segment(3);
		$key = $this->uri->segment(4);

		// Activate user
		if ($this->dx_auth->activate($username, $key))
		{
			$data['auth_message'] = '<strong>Congratulations!</strong><br/><br/>Your account have been successfully activated. '.anchor(site_url($this->dx_auth->login_uri), 'Login');
			$data['alert_type'] = 'success';

			$view = $this->dx_auth->activate_success_view;
			// $this->load->view($this->dx_auth->activate_success_view, $data);
		}
		else
		{
			$data['auth_message'] = '<strong>OOPS!</strong><br/><br/>The activation link is either incorrect or expoired. Please check your email again.';
			$data['alert_type'] = 'danger';
			// $this->load->view($this->dx_auth->activate_failed_view, $data);
			$view = $this->dx_auth->activate_failed_view;
		}

		// Render form
    	$this->template->partial(
        					'body',
        					$view,
    						$data
						)
        				->render([
				        	'site_title' => 'Register',
				        	'page_title' => 'Register'
				    	]);
	}

	function forgot_password()
	{
		// Check logged in?
		if ( $this->dx_auth->is_logged_in())
		{
			redirect('dashboard');
		}

		$val = $this->form_validation;

		// Set form validation rules
		$val->set_rules('login', 'Username or Email address', 'trim|required');

		// Validate rules and call forgot password function
		if ($val->run() AND $this->dx_auth->forgot_password($val->set_value('login')))
		{
			$data['auth_message'] = 'An email has been sent to your email with instructions with how to activate your new password.';

			// $this->load->view($this->dx_auth->forgot_password_success_view, $data);

			// Render form
	        $this->template->partial(
	        					'body',
	        					$this->dx_auth->forgot_password_success_view,
	    						$data
							)
	        				->render([
					        	'site_title' => 'Account Recovery',
					        	'page_title' => 'Check your email'
					    	]);

		}
		else
		{
			// $this->load->view($this->dx_auth->forgot_password_view);

			// Render form
	        $this->template->partial(
	        					'body',
	        					$this->dx_auth->forgot_password_view,
	    						[]
							)
	        				->render([
					        	'site_title' => 'Account Recovery',
					        	'page_title' => 'Forgot Your Account?'
					    	]);
		}
	}

	function reset_password()
	{
		// Check logged in?
		if ( $this->dx_auth->is_logged_in())
		{
			redirect('dashboard');
		}

		// Get username and key
		$username = $this->uri->segment(3);
		$key = $this->uri->segment(4);

		// Reset password
		if ($this->dx_auth->reset_password($username, $key))
		{
			$page_title = "Congratulations!";

			$data['auth_message'] = 'You have successfully reset you password, '. anchor(site_url($this->dx_auth->login_uri), 'Login') . ' with your new credentials now.';
			$data['alert_type'] = 'success';

			$view = $this->dx_auth->reset_password_success_view;

			// $this->load->view($this->dx_auth->reset_password_success_view, $data);
		}
		else
		{
			$page_title = 'Oops! Something Went Wrong!!';

			$data['auth_message'] = 'Reset failed. Your reset link might have already expired or your username and key are incorrect. Please check your email again and follow the instructions. <br/> Or <br/>' . anchor(site_url($this->dx_auth->forgot_password_uri), 'Forget Password');
			$data['alert_type'] = 'danger';

			$view = $this->dx_auth->reset_password_failed_view;

			// $this->load->view($this->dx_auth->reset_password_failed_view, $data);
		}

		$this->template->partial(
        					'body',
        					$view,
    						$data
						)
        				->render([
				        	'site_title' => 'Account Recovery',
				        	'page_title' => $page_title
				    	]);
	}

	function change_password()
	{
		// Check logged in?
		if ( !$this->dx_auth->is_logged_in())
		{
			$this->dx_auth->deny_access('login');
		}

		$val = $this->form_validation;

		// Set form validation
		$val->set_rules('old_password', 'Old Password', 'trim|required|min_length['.$this->min_password.']|max_length['.$this->max_password.']');
		$val->set_rules('new_password', 'New Password', 'trim|required|min_length['.$this->min_password.']|max_length['.$this->max_password.']|matches[confirm_new_password]');
		$val->set_rules('confirm_new_password', 'Confirm new Password', 'trim|required');

		// Validate rules and change password
		if ($val->run() AND $this->dx_auth->change_password($val->set_value('old_password'), $val->set_value('new_password')))
		{
			$data['auth_message'] = 'Your password has successfully been changed.';
			// $this->load->view($this->dx_auth->change_password_success_view, $data);
			$view = $this->dx_auth->change_password_success_view;
		}
		else
		{
			// $this->load->view($this->dx_auth->change_password_view);
			$data = [];
			$view = $this->dx_auth->change_password_view;
		}

		// Render form
    	$this->template->partial(
        					'body',
        					$view,
    						$data
						)
        				->render([
				        	'site_title' => 'Change Password',
				        	'page_title' => 'Change Password'
				    	]);
	}

	function cancel_account()
	{
		// Check logged in?
		if ( !$this->dx_auth->is_logged_in())
		{
			$this->dx_auth->deny_access('login');
		}

		$val = $this->form_validation;

		// Set form validation rules
		$val->set_rules('password', 'Password', "trim|required");

		// Validate rules and change password
		if ($val->run() AND $this->dx_auth->cancel_account($val->set_value('password')))
		{
			// Redirect to homepage
			redirect('');
		}

		// Render form
    	$this->template->partial(
        					'body',
        					$this->dx_auth->cancel_account_view
						)
        				->render([
				        	'site_title' => 'Cancel Account',
				        	'page_title' => 'Cancel Account'
				    	]);
	}

	/**
	 * Show Banned Message
	 *
	 * This function will yield banned message only if
	 * when user supplies the login credentials and "login"
	 * method identifies it is banned.
	 *
	 * When you directly call this method, it yields 404
	 *
	 * @return mixed
	 */
	function banned()
	{
		if ( !$this->dx_auth->is_banned() )
		{
			$this->template->render_404();
		}

		$this->template->partial(
        					'body',
        					$this->dx_auth->banned_view,
    						[
    							'auth_message' => 'Your account has been temporarily banned. <br/> This may be due to excessive login attempts or some other unauthorized actvities from your network. <br/><br/>Please contact Administrator for further assistance.<br/><br/>Thank you.',
    							'alert_type' => 'danger'
    						]
						)
        				->render([
				        	'site_title' => 'Account Banned',
				        	'page_title' => 'Account Banned'
				    	]);
	}


	/**
	 * Show Access Denied Message
	 *
	 * This function will yield banned message only if
	 * when user supplies the login credentials and "login"
	 * method identifies it is banned.
	 *
	 * When you directly call this method, it yields 404
	 *
	 * @return mixed
	 */
	function deny()
	{
		// Check logged in?
		if ( !$this->dx_auth->is_logged_in())
		{
			$this->dx_auth->deny_access('login');
		}

		// Check Deny Flag Set?
		$flag = $this->session->flashdata('auth_flag_access_deny');

		if( !$flag )
		{
			$this->template->render_404();
		}

		// Render regular deny message
		$this->template->partial(
        					'body',
        					$this->dx_auth->deny_view,
    						[
    							'auth_message' => '<strong>OOPS!</strong><br/><br/>You do not have sufficient permission to view this resource.<br/><br/> Go to your ' . anchor('dashboard', 'Dashboard'),
    							'alert_type' => 'danger'
    						]
						)
        				->render([
				        	'site_title' => 'Permission Denied',
				        	'page_title' => 'Permission Denied'
				    	], 403);
	}

	// Example how to get permissions you set permission in /backend/custom_permissions/
	function custom_permissions()
	{
		if ($this->dx_auth->is_logged_in())
		{
			echo 'My role: '.$this->dx_auth->get_role_name().'<br/>';
			echo 'My permission: <br/>';

			if ($this->dx_auth->get_permission_value('edit') != NULL AND $this->dx_auth->get_permission_value('edit'))
			{
				echo 'Edit is allowed';
			}
			else
			{
				echo 'Edit is not allowed';
			}

			echo '<br/>';

			if ($this->dx_auth->get_permission_value('delete') != NULL AND $this->dx_auth->get_permission_value('delete'))
			{
				echo 'Delete is allowed';
			}
			else
			{
				echo 'Delete is not allowed';
			}
		}
	}
}