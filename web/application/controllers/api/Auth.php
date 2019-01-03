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
		$data = [
			'id' => 1,
			'auth_type' => 2,
			'auth_type_id' => 3
		];

		// This is your id token
		// $jwt = JWT::encode($token_payload, base64_decode(strtr($key, '-_', '+/')), 'HS256');
	 	$jwt = $this->api_auth->build_token($data);
		print "<pre>";
		print "JWT:\n";
		print_r($jwt);
		// $decoded = JWT::decode($jwt, base64_decode(strtr($key, '-_', '+/')), ['HS256']);
		$decoded = $this->api_auth->validated_token($jwt);
		print "\n\n";
		print "Decoded:\n";
		print_r($decoded);


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
	function verify_mobile()
	{

	}

	// --------------------------------------------------------------------

	/**
	 * Send/Resend Mobile User Pin for Verification
	 * 	- Singup
	 * 	- Forgot Password
	 * 	- Vefity Existing User for First time login
	 *
	 * @return type
	 */
	function pin()
	{

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