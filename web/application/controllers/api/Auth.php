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

		$this->response([
					$this->config->item('rest_status_field_name') => FALSE,
                    $this->config->item('rest_message_field_name') => $this->lang->line('text_rest_forbidden')
                ], self::HTTP_FORBIDDEN);


		// eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJwYXlsb2FkIjp7ImlkIjoxLCJhdXRoX3R5cGUiOjEsImF1dGhfdHlwZV9pZCI6M319.HSjqjU1XKWifbQdmPo3RLE13Fwjn8aPGWsf2ShYAuLQ


		// // Create TOKEN
		// $tokenData = array();
  //       $tokenData['payload'] = [
  //       	'id' => 1,
  //       	'auth_type' => 1,
  //       	'auth_type_id' => 3
  //       ];
  //       $output['token'] = AUTHORIZATION::generateToken($tokenData);


  //       // Verify Token
  //       $jwt_token = AUTHORIZATION::validateToken($output['token']);
  //       if ($jwt_token != false)
  //       {
  //       	$this->response($output, self::HTTP_OK);
  //       }

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