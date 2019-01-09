<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * API Auth Class
 *
 * API Authentication library for JWT TOKEN.
 *
 * @author		IP BASTOLA
 * @version		1.0.0
 */

use \Firebase\JWT\JWT;

// --------------------------------------------------------------------

/**
 * API ERROR CODES
 *
 * List of API Error Codes
 *
 */
define('IQB_API_ERR_CODE__TOKEN_EXPIRED',   		1); // Token Expired or Invalid - Prompt Login for new token
define('IQB_API_ERR_CODE__TOKEN_INVALID',   		2); // Invalid Token - Prompt Login for new token
define('IQB_API_ERR_CODE__TOKEN_TOO_EARLY', 		3); // Token nbf Exception - Wait a while and try again
define('IQB_API_ERR_CODE__VALIDATION_ERROR', 		4); // Form Validation Error
define('IQB_API_ERR_CODE__USER_NOT_FOUND', 			5); // User Not Found
define('IQB_API_ERR_CODE__USER_API_KEY_EXPIRED', 	6); // User's API Key Expired

// --------------------------------------------------------------------


class Api_auth
{

	public $status_field;
	public $message_field;
	public $err_code_field;
	public $token_field;
	public $token_error;

	function __construct()
	{
		$this->ci =& get_instance();

		// Load config
		$this->ci->load->config('api');

		// Load DX Auth language
		$this->ci->lang->load('api');

		// Load Model
		$this->ci->load->model('api/app_user_model', 'app_user_model');

		// Initialize
		$this->_init();

	}

	// --------------------------------------------------------------------

	/**
	 * Initialize goodies
	 *
	 * @return void
	 */
	private function _init()
	{
		// Set attributes
		$this->status_field 	= $this->ci->config->item('api_status_field');
		$this->message_field 	= $this->ci->config->item('api_message_field');
		$this->err_code_field 	= $this->ci->config->item('api_err_code_field');
		$this->token_field 		= $this->ci->config->item('api_token_field');
	}

	// --------------------------------------------------------------------

	/**
	 * Perform Login
	 *
	 * On successfull lgoin, it will return JWT Token along with status and message
	 *
	 * @param int $login Mobile
	 * @param string $password User Password
	 * @return array
	 */
	function login($login, $password)
	{
		$result = $this->ci->app_user_model->login($login, $password);

		if( $result !== FALSE )
		{
			// Update history
			$this->_save_login_history($result['id']);

			// Build and Return Token
			return [
                $this->token_field   => $this->_build_token($result),
                $this->status_field  => TRUE,
                $this->message_field => $this->ci->lang->line('api_text_login_success'),
            ];
		}
		else
		{
			// Build and Return Token
			return [
                $this->status_field  => FALSE,
                $this->message_field => $this->ci->lang->line('api_text_login_failed'),
            ];
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Get Token from User Request
	 *
	 * @return string
	 */
	public function get_token()
	{
		return $this->_get_token();
	}


	// --------------------------------------------------------------------

	/**
	 * Build a JWT Token with the supplied paylod data
	 *
	 * @param array $data payload data
	 * @return string
	 */
	public function build_token( $data )
	{
		return $this->_build_token($data);
	}

	// --------------------------------------------------------------------

	/**
	 * Build a JWT Token with the supplied paylod data
	 *
	 * @param array $data payload data
	 * @return string
	 */
    private function _build_token($data)
    {
        $issuedAt   = now();
        $notBefore  = $issuedAt + 0;
        $expiredAt  = $issuedAt + INSQUBE_API_TOKEN_LIFE;
        $payload_variables = [
            "iss" => APP_URL,
            "aud" => INSQUBE_API_URL,
            "iat" => $issuedAt,
            "nbf" => $notBefore,
            'exp' => $expiredAt,
            'data' => $data
        ];
        return JWT::encode($payload_variables, base64_decode(INSQUBE_API_KEY), 'HS256');
    }

    // --------------------------------------------------------------------

	/**
	 * Get Authorization Token from request Header
	 * If no token found, return FALSE
     *
	 * @return mixed
	 */
	private function _get_token()
	{
		$auth_token = FALSE;
		$headers 	= $this->ci->input->request_headers();
		if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization']))
		{
            $auth_token = $headers['Authorization'];
        }
        return $auth_token;
	}

	// --------------------------------------------------------------------

	/**
	 * Decode a Token and Return
	 *
	 * On exception, it returns array with status, message and error_code
	 *
	 * @param string|null $token JWT
	 * @return array|object
	 */
    private function _decode_token( $token = NULL )
    {
        // Fetch Token if Not Supplied
        if(!$token)
        {
            $token = $this->_get_token();
        }

        $decoded_token = FALSE;
        try {

            $decoded_token = JWT::decode($token, base64_decode(INSQUBE_API_KEY), ['HS256']);

            /**
             * User's api_key change on every password change or mobile change
             */
            $mobile 	= $decoded_token->data->mobile;
            $api_key 	= $decoded_token->data->api_key;
            $user 	 	= $this->ci->app_user_model->get_by_mobile($mobile);

            // User Exists?
            if( !$user )
            {
            	$this->token_error = $this->_err_user_not_found();
            	return FALSE;
            }

            // Valid API KEY??
            if( $user->api_key !== $api_key )
            {
            	$this->token_error = $this->_err_user_api_key_expired();
            	return FALSE;
            }

        } catch (\ExpiredException $e) {
            /**
             * Token Has Expired
             *
             * Action: Prompt Login to Generate New Token
             */
            $this->token_error = $this->_err_token_expired();
        }
        catch (\BeforeValidException $e) {
            /**
             * Before Valid: Token Sent too early
             *
             * Action: Wait and Try again
             */
            return $this->_err_token_too_early();
        }
        catch (\Exception $e) {
            /**
             * Other Exception
             *
             * Action: Prompt Login to Generate New Token
             */
            $this->token_error = $this->_err_token_invalid();
        }

        return $decoded_token;
    }

    // --------------------------------------------------------------------

    	/**
    	 * Error on API KEY Expired
    	 *
    	 * @return array
    	 */
        private function _err_user_api_key_expired()
        {
            return [
                $this->status_field     => FALSE,
                $this->message_field    => $this->ci->lang->line('api_text_err_user_api_key_expired'),
                $this->err_code_field    => IQB_API_ERR_CODE__USER_API_KEY_EXPIRED
            ];
        }

        // --------------------------------------------------------------------

        /**
    	 * Error on User NOT FOUND
    	 *
    	 * @return array
    	 */
        private function _err_user_not_found()
        {
            return [
                $this->status_field     => FALSE,
                $this->message_field    => $this->ci->lang->line('api_text_user_not_found'),
                $this->err_code_field    => IQB_API_ERR_CODE__USER_NOT_FOUND
            ];
        }

        // --------------------------------------------------------------------

        /**
    	 * Error on Token Expired
    	 *
    	 * @return array
    	 */
        private function _err_token_expired()
        {
            return [
                $this->status_field     => FALSE,
                $this->message_field    => $this->ci->lang->line('api_text_err_token_expired'),
                $this->err_code_field    => IQB_API_ERR_CODE__TOKEN_EXPIRED
            ];
        }

        // --------------------------------------------------------------------

        /**
    	 * Error on Token Invalid
    	 *
    	 * @return array
    	 */
        private function _err_token_invalid()
        {
            return [
                $this->status_field     => FALSE,
                $this->message_field    => $this->ci->lang->line('api_text_err_token_invalid'),
                $this->err_code_field    => IQB_API_ERR_CODE__TOKEN_INVALID
            ];
        }

        // --------------------------------------------------------------------

        /**
    	 * Error on Token NBF exception
    	 *
    	 * @return array
    	 */
        private function _err_token_too_early()
        {
        	return [
                $this->status_field     => FALSE,
                $this->message_field    => $this->ci->lang->line('api_text_err_token_too_early'),
                $this->err_code_field    => IQB_API_ERR_CODE__TOKEN_TOO_EARLY
            ];
        }

    // --------------------------------------------------------------------

    /**
     * Save Login History on Successfull Login
     *
     * @param int $app_user_id App User ID
     * @return bool
     */
	private function _save_login_history($app_user_id)
	{

		$ip_address = $this->ci->input->ip_address();
		$device_id 	= $this->_device_id();
		$datetime 	= date('Y-m-d H:i:s', time());

		return $this->ci->app_user_model->login_history($app_user_id, $ip_address, $device_id, $datetime);
	}

	// --------------------------------------------------------------------

	/**
	 * Get User's Device ID from Header
	 *
	 * @return string
	 */
	private function _device_id( )
	{
		$device_id = 'Unknown';
		$headers 	= $this->ci->input->request_headers();
		if (array_key_exists('device_id', $headers) && !empty($headers['device_id']))
		{
            $device_id = $headers['device_id'];
        }
        return $device_id;
	}

	// --------------------------------------------------------------------



	/**
	 * Is Authorized?
	 *
	 * Decode and check if a valid token exists
	 *
	 * @return bool
	 */
	function is_authorized( )
	{
		$result = $this->_decode_token();
		return $result !== FALSE;
	}

	// --------------------------------------------------------------------


	/**
	 * Get Token Data
	 * If no key supplied, return the whole payload
	 * If invalid token, return NULL
	 *
	 * @return bool
	 */
	function get_token_data( $key = NULL )
	{
		if( $this->is_authorized() )
		{
			$payload = $this->_decode_token();

			if($key)
			{
				return $payload->data->{$key} ?? NULL;
			}
			return $payload;
		}

		// Invalid Token, return NULL
		return NULL;
	}







	/**
	 * @TODO: The functions below are to be re-written as per API Flow
	 */

	function logout()
	{


	}



	/* End of main function */

}
