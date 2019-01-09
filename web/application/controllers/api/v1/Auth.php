<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Accounts Controller
 *
 * This controller falls under "Master Setup" category.
 *
 * @category 	Master Setup
 * @sub-category Account
 */

// --------------------------------------------------------------------

class Auth extends Base_API_Controller
{

	function __construct()
	{
		parent::__construct();

		// $this->check_authorized();

		$this->load->library('form_validation');

	}

	// --------------------------------------------------------------------

	/**
	 * Default Method
	 *
	 * Nothing Available
	 *
	 * @return type
	 */
	function index()
	{
		// print date('YmdHis');
		// print "\r\n";

		$this->response([
			'status' => FALSE,
			'message' => 'Resource Not Found!'
		], 403);


		$data = [
			'id' => 'test',
			'auth_type' => 'test',
			'auth_type_id' => 'test'
		];

		// This is your id token
		// $jwt = JWT::encode($token_payload, base64_decode(strtr($key, '-_', '+/')), 'HS256');
	 	$jwt = $this->api_auth->build_token($data);
		// print "<pre>";
		// print "JWT:\n";
		// print_r($jwt);
		// // $decoded = JWT::decode($jwt, base64_decode(strtr($key, '-_', '+/')), ['HS256']);
		// $decoded = $this->api_auth->validated_token($jwt);
		// print "\n\n";
		// print "Decoded:\n";
		// print_r($decoded);

		$this->response(['token' => $jwt]);


	}

	// --------------------------------------------------------------------

	/**
	 * Verify Resources
	 * 	- Existing User
	 * 	- Pincode
	 *
	 * @return type
	 */
	function verify()
	{
		/**
		 * Ask for User Mobiel and Validate Against Our Database
		 */
		if($this->input->post())
		{
			$action = $this->input->post('action');
			switch($action)
			{
				/**
				 * Verify Mobile
				 */
				case 'mobile':
					$this->_verify_mobile();
					break;

				/**
				 * Verify Pincode
				 */
				case 'pincode':
					$this->_verify_pincode();
					break;

				default:
					$this->response_404();
					break;
			}
		}
		$this->response_404();
	}

	// --------------------------------------------------------------------

	/**
	 * Verify Mobile
	 *
	 * @return void
	 */
	private function _verify_mobile()
	{
		/**
		 * Ask for User Mobiel and Validate Against Our Database
		 */
		if($this->input->post())
		{
			$rules = $this->app_user_model->v_rules('verify_mobile');

			$this->form_validation->set_rules($rules);
			if($this->form_validation->run() === TRUE )
        	{
        		/**
        		 * If Mobile Exists - SEND SMS for Next Step (To create Password)
        		 */
        		$mobile = $this->input->post('mobile');
        		if( $this->app_user_model->check_duplicate(['mobile' => $mobile]) )
        		{
        			$this->response([
	                    $this->config->item('api_status_field') 	=> TRUE,
	                    $this->config->item('api_message_field') 	=> $this->lang->line('api_text_user_found'),
	                ], self::HTTP_OK);
        		}
        		else
        		{
        			$this->__err_user_not_found();
        		}
        	}
        	else
        	{
        		$this->__err_validation();
        	}
		}
		$this->response_404();
	}

	// --------------------------------------------------------------------

	/**
	 * Verify Pincode
	 *
	 * @return void
	 */
	private function _verify_pincode()
	{
		/**
		 * Ask for User Mobiel and Validate Against Our Database
		 */
		if($this->input->post())
		{
			$rules = $this->app_user_model->v_rules('verify_pincode');
			$this->form_validation->set_rules($rules);
			if($this->form_validation->run() === TRUE )
        	{
        		/**
        		 * If Mobile Exists - SEND SMS for Next Step (To create Password)
        		 */
        		$mobile  = $this->input->post('mobile');
        		$pincode = $this->input->post('pincode');
        		if( $this->app_user_model->verify_pincode($mobile, $pincode) )
        		{
        			$this->response([
	                    $this->config->item('api_status_field') 	=> TRUE,
	                    $this->config->item('api_message_field') 	=> $this->lang->line('api_text_valid_pincode'),
	                ], self::HTTP_OK);
        		}
        		else
        		{
        			$this->response([
	                    $this->config->item('api_status_field') 	=> FALSE,
	                    $this->config->item('api_message_field') 	=> $this->lang->line('api_text_invalid_pincode'),
	                ], self::HTTP_BAD_REQUEST);
        		}
        	}
        	else
        	{
        		$this->__err_validation();
        	}
		}
		$this->response_404();
	}

	// --------------------------------------------------------------------



	// --------------------------------------------------------------------

	/**
	 * Send/Resend Mobile User Pin for Verification
	 * 	- Singup
	 * 	- Forgot Password
	 * 	- Vefity Existing User for First time login
	 *
	 * @return void
	 */
	public function pincode()
	{
		/**
		 * Ask for User Mobiel and Validate Against Our Database
		 */
		if($this->input->post())
		{
			$action = $this->input->post('action');
			switch($action)
			{
				/**
				 * For Password Creatiion
				 * For Password Change
				 */
				case 'pwd_create':
				case 'pwd_change':
					$this->_pincode();
					break;


				default:
					$this->response_404();
					break;
			}
		}
		$this->response_404();
	}

	// --------------------------------------------------------------------

	/**
	 * Send/Resend Mobile User Pin for Verification
	 * 	- Singup
	 * 	- Forgot Password
	 * 	- Vefity Existing User for First time login
	 *
	 * @return void
	 */
	private function _pincode()
	{
		$rules = $this->app_user_model->v_rules('pincode');
		$this->form_validation->set_rules($rules);
		if($this->form_validation->run() === TRUE )
    	{
    		$mobile = intval($this->input->post('mobile'));
			$user 	= $this->app_user_model->get_by_mobile($mobile);

			if(!$user)
			{
				$this->__err_user_not_found();
			}
			else
			{
				/**
				 * Reached Maximum Resend Count???
				 */
				if( $this->_pincode_quota_exceeded($user) )
				{
					$this->response([
	                    $this->config->item('api_status_field') 	=> FALSE,
	                    $this->config->item('api_message_field') 	=> $this->lang->line('api_text_sms_resend_quota_exceeded'),
	                ], self::HTTP_SERVICE_UNAVAILABLE);
				}


				/**
				 * SEND SMS
				 */
				try {
					$status = $this->_send_code($user);
				} catch (Exception $e) {
					// this will throw error if api validation period is not configured properly
					$this->__err_sms_api(self::HTTP_INTERNAL_SERVER_ERROR);
				}

				if($status)
				{
					$this->__ok_sms_api();
				}
				else
				{
					$this->__err_sms_api(self::HTTP_SERVICE_UNAVAILABLE);
				}
			}
    	}
    	else
    	{
    		$this->__err_validation();
    	}
	}


	// --------------------------------------------------------------------

	/**
	 * Check Pincode Quota
	 *
	 * Check if a user sms quota exceeded
	 *
	 * @param object $user
	 * @return bool
	 */
	private function _pincode_quota_exceeded($user)
	{
		$now = now();
		if( $user->pincode_resend_count == $this->settings->api_sms_quota_limit )
		{

			// with in expiry limit? quota exceeded
			$pincode_expires_at = strtotime($user->pincode_expires_at);
			if( $pincode_expires_at > $now  )
			{
				return TRUE;
			}
		}
		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Send SMS Verification Code
	 *
	 * @param int|object $user
	 */
	private function _send_code($user)
	{
		$user  = is_numeric($user) ? $this->app_user_model->find(intval($user)) : $user;
		if(!$user)
		{
			$this->response_404();
		}


		// Build Pincode Data
		$data = $this->_pincode_data($user);

		// Update Database
		$status = $this->app_user_model->pincode($user->id, $data);
		if($status)
		{
			$this->load->helper('sms');

			$pincode 	= $data['pincode'];
			$expires_at = $data['pincode_expires_at'] ?? $user->pincode_expires_at;
			$message 	= 	"Your code for {$this->settings->orgn_name_en} Mobile App is: {$pincode}. \n" .
							"Expires at: " . $expires_at;

			// Fire SMS
			$result = send_sms($user->mobile, $message, 'api');

			/**
			 *
			 * Success Return:
				Array
				(
				    [credit_available] => 31524
				    [message_id] => 549384
				    [response] => 1 mesages has been queued for delivery
				    [response_code] => 200
				    [count] => 1
				    [credit_consumed] => 1
				)
			*/

			$response_code = intval( $result['response_code'] ?? 0 );

			return $response_code === 200;
		}

		return FALSE;
	}

	// --------------------------------------------------------------------

	private function _pincode_data($user)
	{
		$pincode 				= rand(100000, 999999);
		$pincode_resend_count 	= $this->_pincode_resend_count($user);
		$data = [
			'pincode' 				=> $pincode,
			'pincode_resend_count'  => $pincode_resend_count
		];
		// Set Quota Expiry Date
		if( $pincode_resend_count == 1 )
		{
			$data['pincode_expires_at'] = $this->_pincode_expires_at();
		}

		return $data;
	}

	// --------------------------------------------------------------------

	private function _pincode_resend_count($user)
	{
		$count = intval($user->pincode_resend_count);
		if( $user->pincode_resend_count >= $this->settings->api_sms_quota_limit )
		{
			$count = 1;
		}
		else
		{
			$count++;
		}
		return $count;
	}

	// --------------------------------------------------------------------

	private function _pincode_expires_at()
	{
		$validation_period = intval($this->settings->api_sms_validation_period);
		if( !$validation_period )
		{
			throw new Exception('Exception Occured - [Controller: api/v1/Auth][Method: _pincode_expires_at()]: API settings for SMS validation period not configured.');

		}
		return date('Y-m-d H:i:s', now() +  $validation_period );
	}


	// --------------------------------------------------------------------

	/**
	 * Create Password
	 * 	- Singup
	 * 	- Forgot Password
	 * 	- Vefity Existing User for First time login
	 *
	 * @return type
	 */
	function password()
	{
		/**
		 * Ask for User Mobiel and Validate Against Our Database
		 */
		if($this->input->post())
		{
			$action = $this->input->post('action');
			switch($action)
			{
				/**
				 * For Password Creatiion
				 * For Password Change
				 */
				case 'pwd_create':
					$this->_password_create();
					break;

				case 'pwd_change':
					$this->_password_change();
					break;


				default:
					$this->response_404();
					break;
			}
		}
		$this->response_404();
	}

	// --------------------------------------------------------------------

	/**
	 * Create Password
	 * 	- From Signup or Forget Password
	 *
	 * @return void
	 */
	private function _password_create()
	{
		$rules = $this->app_user_model->v_rules('pwd_create');
		$this->form_validation->set_rules($rules);
		if($this->form_validation->run() === TRUE )
    	{
    		/**
    		 * Must Send pincode along with password.
    		 *
    		 * Valid Pincode?
    		 */
    		$mobile  = $this->input->post('mobile');
    		$pincode = $this->input->post('pincode');
    		$user 	 = $this->app_user_model->get_by_mobile($mobile);
    		if( $this->app_user_model->is_valid_pincode($user, $pincode) )
    		{
    			/**
    			 * Let's Create Password, Generate TOKEN and retrun to user
    			 */
    			$new_pass 	= $this->input->post('password');
    			$status 	= $this->app_user_model->change_password($user, $new_pass);
    			if($status)
				{
					$this->response([
	                    $this->config->item('api_status_field') 	=> TRUE,
	                    $this->config->item('api_message_field') 	=> $this->lang->line('api_text_password_create_ok'),
	                ], self::HTTP_OK);
				}
				else
				{
					$this->response([
	                    $this->config->item('api_status_field') 	=> FALSE,
	                    $this->config->item('api_message_field') 	=> $this->lang->line('api_text_password_create_fail'),
	                ], self::HTTP_SERVICE_UNAVAILABLE);
				}
    		}
    		else
    		{
    			$this->response([
                    $this->config->item('api_status_field') 	=> TRUE,
                    $this->config->item('api_message_field') 	=> $this->lang->line('api_text_invalid_pincode'),
                ], self::HTTP_BAD_REQUEST);
    		}
    	}
    	else
    	{
    		$this->__err_validation();
    	}
	}

	// --------------------------------------------------------------------

	/**
	 * Change Password
	 * 	- From Logged in State
	 * i.e. A Valid Token Must be Passed
	 *
	 * @return void
	 */
	private function _password_change()
	{
		/**
		 * AUTHORIZED REQUEST?
		 */
		if( !$this->api_auth->is_authorized() )
		{
			$this->response($this->api_auth->token_error, self::HTTP_UNAUTHORIZED);
		}


		$rules = $this->app_user_model->v_rules('pwd_change');
		$this->form_validation->set_rules($rules);
		if($this->form_validation->run() === TRUE )
    	{

    		/**
    		 * User ID from Token
    		 */
    		$mobile 	= intval($this->api_auth->get_token_data('mobile'));
    		$user 		= $this->app_user_model->get_by_mobile($mobile);
    		if( !$user )
    		{
    			$this->__err_user_not_found();
    		}

    		// Let's change the password
    		$new_pass 	= $this->input->post('password');
    		$status 	= $this->app_user_model->change_password($user, $new_pass);
			if($status)
			{
				$this->response([
                    $this->config->item('api_status_field') 	=> TRUE,
                    $this->config->item('api_message_field') 	=> $this->lang->line('api_text_password_create_ok'),
                ], self::HTTP_OK);
			}
			else
			{
				$this->response([
                    $this->config->item('api_status_field') 	=> FALSE,
                    $this->config->item('api_message_field') 	=> $this->lang->line('api_text_password_create_fail'),
                ], self::HTTP_SERVICE_UNAVAILABLE);
			}

    	}
    	else
    	{
    		$this->__err_validation();
    	}
	}

	// --------------------------------------------------------------------

	/**
	 * Login Mobile User
	 *
	 * @return type
	 */
	function login()
	{
		if($this->input->post())
		{
			$rules = $this->app_user_model->v_rules('login');
			$this->form_validation->set_rules($rules);
			if($this->form_validation->run() === TRUE )
	    	{

	    		$mobile = $this->input->post('mobile');
	    		$password = $this->input->post('password');

	    		// Do login
	    		$result = $this->api_auth->login($mobile, $password);
	    		$this->response($result, $result['status'] == FALSE ? self::HTTP_BAD_REQUEST : self::HTTP_OK);

	    	}
	    	else
	    	{
	    		$this->__err_validation();
	    	}

		}
		$this->response_404();
	}

	// --------------------------------------------------------------------

	/**
	 * Signup/Register Mobile User
	 *
	 * @return type
	 */
	function register()
	{
		if($this->input->post())
		{
			$rules = $this->app_user_model->v_rules('register');
			$this->form_validation->set_rules($rules);
			if($this->form_validation->run() === TRUE )
	    	{
	    		/**
	    		 * Create Mobile User Based on Auth Types
	    		 */
	    		$auth_type = intval( $this->input->post('auth_type') );
	    		switch ($auth_type)
	    		{
	    			/**
	    			 * Core User
	    			 *
	    			 * @TODO: Can We signup from Mbile for Core User????
	    			 * Let's do NOTHING now.
	    			 */
	    			case IQB_API_AUTH_TYPE_USER:
	    				# code...
	    				break;

    				case IQB_API_AUTH_TYPE_CUSTOMER:
	    				return $this->_signup_customer();
	    				break;

	    			default:
	    				# code...
	    				break;
	    		}
	    	}
	    	else
	    	{
	    		$this->__err_validation();
	    	}

		}
		$this->response_404();
	}

	// --------------------------------------------------------------------

	/**
	 * Signup Validation Callback - Check Mobile Duplication
	 *
	 * @param integer $mobile
	 * @return bool
	 */
	public function _cb_mobile_identity_duplicate($mobile)
	{
		if( $this->app_user_model->check_duplicate(['mobile' => $mobile]) )
        {
            $this->form_validation->set_message('_cb_mobile_identity_duplicate', 'The %s already exists.');
            return FALSE;
        }
        return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Signup Mobile User - Customer
	 * @return type
	 */
	private function _signup_customer()
	{
		$this->load->model('customer_model');
		$post_data = $this->input->post();
		$data = [
			'full_name_en' 		=> $post_data['full_name_en'],
			'mobile_identity' 	=> $post_data['mobile'],
			'password' 			=> $post_data['password'],
			'nationality' 		=> 'NP',
			'flag_kyc_verified' => IQB_FLAG_OFF
		];


		if( $this->customer_model->add($data, 'api') )
		{
			$user = $this->app_user_model->get_by_mobile($data['mobile_identity']);
			/**
			 * SEND SMS
			 */
			try {
				$status = $this->_send_code($user);
			} catch (Exception $e) {
				// this will throw error if api validation period is not configured properly
				$this->__err_sms_api(self::HTTP_INTERNAL_SERVER_ERROR);
			}

			if($status)
			{
				$this->__ok_sms_api();
			}
			else
			{
				$this->__err_sms_api(self::HTTP_SERVICE_UNAVAILABLE);
			}
		}
		else
		{
			$this->__err_user_can_not_add();
		}
	}

	// --------------------------------------------------------------------

	private function __ok_sms_api()
	{
		$this->response([
                    $this->config->item('api_status_field') 	=> TRUE,
                    $this->config->item('api_message_field') 	=> $this->lang->line('api_text_sms_send_ok'),
                ], self::HTTP_OK);
	}

	// --------------------------------------------------------------------

	private function __err_sms_api($http_code)
	{
		$this->response([
                    $this->config->item('api_status_field') 	=> FALSE,
                    $this->config->item('api_message_field') 	=> $this->lang->line('api_text_sms_api_error'),
                ], $http_code);
	}

	// --------------------------------------------------------------------

	private function __err_user_can_not_add()
	{
		$this->response([
                    $this->config->item('api_status_field') 	=> FALSE,
                    $this->api_auth->err_code_field 			=> IQB_API_ERR_CODE__USER_CAN_NOT_ADD,
                    $this->config->item('api_message_field') 	=> $this->lang->line('api_text_user_can_not_add'),
                ], self::HTTP_INTERNAL_SERVER_ERROR);
	}

	// --------------------------------------------------------------------

	private function __err_validation()
	{
		$this->response([
                    $this->config->item('api_status_field') 	=> FALSE,
                    $this->api_auth->err_code_field 			=> IQB_API_ERR_CODE__VALIDATION_ERROR,
                    $this->config->item('api_message_field') 	=> strip_tags( validation_errors() ),
                ], self::HTTP_BAD_REQUEST);
	}

	// --------------------------------------------------------------------

	private function __err_user_not_found()
	{
		$this->response([
	                    $this->config->item('api_status_field') 	=> FALSE,
	                    $this->api_auth->err_code_field 			=> IQB_API_ERR_CODE__USER_NOT_FOUND,
	                    $this->config->item('api_message_field') 	=> $this->lang->line('api_text_user_not_found'),
	                ], self::HTTP_BAD_REQUEST);
	}

}