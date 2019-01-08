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
	 * Verify Existing User
	 *
	 * 	Vefity Existing User for First time login using mobile number.
	 *  If user exists, it will automatically send pin code as SMS to
	 * 	user's mobile number
	 *
	 * @return type
	 */
	function verify()
	{

		return $this->pincode();
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
	public function pincode()
	{
		/**
		 * Ask for User Mobiel and Validate Against Our Database
		 */
		if($this->input->post())
		{
			$rules = $this->app_user_model->v_rules('verify');

			$this->form_validation->set_rules($rules);
			if($this->form_validation->run() === TRUE )
        	{
        		/**
        		 * If Mobile Exists - SEND SMS for Next Step (To create Password)
        		 */
        		$mobile = $this->input->post('mobile');
        		if( $this->app_user_model->check_duplicate(['mobile' => $mobile]) )
        		{
        			// Send Verification Code
        			return $this->_pincode($mobile);
        		}
        		else
        		{
        			/**
        			 * User NOT FOUND!
        			 */
        			$this->response([
	                    $this->config->item('api_status_field') 	=> FALSE,
	                    $this->api_auth->err_code_field 			=> IQB_API_ERR_CODE__USER_NOT_FOUND,
	                    $this->config->item('api_message_field') 	=> $this->lang->line('api_text_user_not_found'),
	                ], self::HTTP_BAD_REQUEST);
        		}
        	}
        	else
        	{
        		$this->response([
                    $this->config->item('api_status_field') 	=> FALSE,
                    $this->api_auth->err_code_field 			=> IQB_API_ERR_CODE__VALIDATION_ERROR,
                    $this->config->item('api_message_field') 	=> strip_tags( validation_errors() ),
                ], self::HTTP_BAD_REQUEST);
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
	private function _pincode($mobile)
	{
		$mobile = intval($mobile);
		$user 	= $this->app_user_model->get_by_mobile($mobile);
		if( $user )
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
				$this->response([
                    $this->config->item('api_status_field') 	=> FALSE,
                    $this->config->item('api_message_field') 	=> $this->lang->line('api_text_sms_api_error'),
                ], self::HTTP_INTERNAL_SERVER_ERROR);
			}

			if($status)
			{
				$this->response([
                    $this->config->item('api_status_field') 	=> TRUE,
                    $this->config->item('api_message_field') 	=> $this->lang->line('api_text_sms_send_ok'),
                ], self::HTTP_OK);
			}
			else
			{
				$this->response([
                    $this->config->item('api_status_field') 	=> FALSE,
                    $this->config->item('api_message_field') 	=> $this->lang->line('api_text_sms_api_error'),
                ], self::HTTP_SERVICE_UNAVAILABLE);
			}

		}
		else
		{
			/**
			 * User NOT FOUND!
			 */
			$this->response([
                $this->config->item('api_status_field') 	=> FALSE,
                $this->api_auth->err_code_field 			=> IQB_API_ERR_CODE__USER_NOT_FOUND,
                $this->config->item('api_message_field') 	=> $this->lang->line('api_text_user_not_found'),
            ], self::HTTP_BAD_REQUEST);
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
	 * Verify Mobile User Pin
	 * 	- Singup
	 * 	- Forgot Password
	 * 	- Vefity Existing User for First time login
	 *
	 * @return type
	 */
	function verify_pin()
	{

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

	}


	// --------------------------------------------------------------------

	/**
	 * Signup Mobile User
	 * @return type
	 */
	function signup()
	{

	}

	// --------------------------------------------------------------------

	/**
	 * Login Mobile User
	 *
	 * @return type
	 */
	function login()
	{

	}


	// public function test()
	// {
	// 	// echo 'hello';exit;
	// 	$this->response($this->app_user, self::HTTP_OK);
	// }


}