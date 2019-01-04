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
define('IQB_API_ERR_CODE__TOKEN_EXPIRED',   1); // Token Expired or Invalid - Prompt Login for new token
define('IQB_API_ERR_CODE__TOKEN_INVALID',   2); // Invalid Token - Prompt Login for new token
define('IQB_API_ERR_CODE__TOKEN_TOO_EARLY', 3); // Token nbf Exception - Wait a while and try again

// --------------------------------------------------------------------


class API_Auth
{

	public $status_field;
	public $message_field;
	public $err_code_field;
	public $token_field;

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
	 * Decode a Token and Return
	 *
	 * On exception, it returns array with status, message and error_code
	 *
	 * @param string|null $token JWT
	 * @return array|object
	 */
	public function validated_token( $token = NULL )
	{
		return $this->_validated_token($token);
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
    private function _validated_token( $token = NULL )
    {
        // Fetch Token if Not Supplied
        if(!$token)
        {
            $token = $this->_get_token();
        }

        try {

            $decoded_token = JWT::decode($token, base64_decode(INSQUBE_API_KEY), ['HS256']);


            /**
             * Token might be valid, but app user might have been deleted?
             */



        } catch (\ExpiredException $e) {
            /**
             * Token Has Expired
             *
             * Action: Prompt Login to Generate New Token
             */
            return $this->_err_token_expired();
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
            return $this->_err_token_invalid();
        }

        return $decoded_token;
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
		$result = $this->_validated_token();
		$status = $result[$this->status_field] ?? NULL;
        if( $status === FALSE )
        {
            return FALSE;
        }
        return TRUE;
	}







	/**
	 * @TODO: The functions below are to be re-written as per API Flow
	 */

	function logout()
	{
		// Trigger event
		$this->user_logging_out($this->ci->session->userdata('DX_user_id'));

		// Delete auto login
		if ($this->ci->input->cookie($this->ci->config->item('DX_autologin_cookie_name'))) {
			$this->_delete_autologin();
		}

		// Destroy session
		$this->ci->session->sess_destroy();
	}

	function register($username, $password, $email, $extra_data = [])
	{
		// Load Models
		$this->ci->load->model('user_model');
		$this->ci->load->model('dx_auth/user_temp', 'user_temp');

		$this->ci->load->helper('url');

		// Default return value
		$insert = FALSE;

		// Hash password using phpass
		$hasher = new PasswordHash(
				$this->ci->config->item('phpass_hash_strength'),
				$this->ci->config->item('phpass_hash_portable'));
		$hashed_password = $hasher->HashPassword($password);

		// New user array
		$new_user = array(
			'username'					=> $username,
			'password'					=> $hashed_password,
			'email'						=> $email,
			'last_ip'					=> $this->ci->input->ip_address()
		);

		// Do we have extra data?
		if( !empty( $extra_data) )
		{
			// Remove Vitals
			unset($extra_data['username'], $extra_data['password'], $extra_data['email']);
			$new_user = !empty( $extra_data) ? array_merge($new_user, $extra_data) : $new_user;
		}

		// Do we need to send email to activate user
		if ($this->ci->config->item('DX_email_activation'))
		{
			// Add activation key to user array
			$new_user['activation_key'] = md5(rand().microtime());

			// Create temporary user in database which means the user still unactivated.
			$insert = $this->ci->user_temp->create_temp($new_user);
		}
		else
		{
			// Create user
			$insert = $this->ci->user_model->create_user($new_user);

			// Trigger event
			// 		This event is not needed as we are creating profile as JSON data in same table
			// 		with Next Wizard Call
			// $this->user_activated($this->ci->db->insert_id());
		}

		if ($insert)
		{
			// Replace password with blank text for email.
			$new_user['password'] = $password;

			// $result = $new_user;

			// Send email based on config

			// Check if user need to activate it's account using email
			if ($this->ci->config->item('DX_email_activation'))
			{
				// Create email
				$from = $this->ci->config->item('DX_webmaster_email');
				$subject = sprintf($this->ci->lang->line('auth_activate_subject'), $this->ci->settings->orgn_name_en);

				// Activation Link
				$new_user['activate_url'] = site_url($this->ci->config->item('DX_activate_uri')."{$new_user['username']}/{$new_user['activation_key']}");

				// Trigger event and get email content
				$this->sending_activation_email($new_user, $message);

				// Send email with activation link
				$this->_email($email, $from, $subject, $message);
			}
			else
			{
				// Check if need to email account details
				if ($this->ci->config->item('DX_email_account_details'))
				{
					// Create email
					$from = $this->ci->config->item('DX_webmaster_email');
					$subject = sprintf($this->ci->lang->line('auth_account_subject'), $this->ci->settings->orgn_name_en);

					// Trigger event and get email content
					$this->sending_account_email($new_user, $message);

					// Send email with account details
					$this->_email($email, $from, $subject, $message);
				}
			}
		}

		return $insert; // newly created user ID or False
	}

	function forgot_password($login)
	{
		// Default return value
		$result = FALSE;

		if ($login)
		{
			// Load Model
			$this->ci->load->model('user_model');
			// Load Helper
			$this->ci->load->helper('url');

			// Get login and check if it's exist
			if ($query = $this->ci->user_model->get_login($login) AND $query->num_rows() == 1)
			{
				// Get User data
				$row = $query->row();

				// Check if there is already new password created but waiting to be activated for this login
				if ( ! $row->newpass_key)
				{
					// Appearantly there is no password created yet for this login, so we create new password
					$data['password'] = $this->_gen_pass();

					// Generate password hash
					$hasher = new PasswordHash(
						$this->ci->config->item('phpass_hash_strength'),
						$this->ci->config->item('phpass_hash_portable'));

					$encode = $hasher->HashPassword($data['password']);

					// Create key
					$data['key'] = md5(rand().microtime());

					// Create new password (but it haven't activated yet)
					$this->ci->user_model->newpass($row->id, $encode, $data['key']);

					// Create reset password link to be included in email
					$data['reset_password_uri'] = site_url($this->ci->config->item('DX_reset_password_uri')."{$row->username}/{$data['key']}");

					// Create email
					$from = $this->ci->config->item('DX_webmaster_email');
					$subject = $this->ci->lang->line('auth_forgot_password_subject');

					// Trigger event and get email content
					$this->sending_forgot_password_email($data, $message);

					// Send instruction email
					$this->_email($row->email, $from, $subject, $message);

					$result = TRUE;
				}
				else
				{
					// There is already new password waiting to be activated
					$this->_auth_error = $this->ci->lang->line('auth_request_sent');
				}
			}
			else
			{
				$this->_auth_error = $this->ci->lang->line('auth_username_or_email_not_exist');
			}
		}

		return $result;
	}

	function activate($username, $key = '')
	{
		// Load Models
		$this->ci->load->model('user_model');
		$this->ci->load->model('dx_auth/user_temp', 'user_temp');

		// Default return value
		$result = FALSE;

		if ($this->ci->config->item('DX_email_activation'))
		{
			// Delete user whose account expired (not activated until expired time)
			$this->ci->user_temp->prune_temp();
		}

		// Activate user
		if ($query = $this->ci->user_temp->activate_user($username, $key) AND $query->num_rows() > 0)
		{
			// Get user
			$row = $query->row_array();

			$del = $row['id'];

			// Unset any unwanted fields
			unset($row['id']); // We don't want to copy the id across
			unset($row['activation_key']);

			// Create user
			if ($this->ci->user_model->create_user($row))
			{
				// Trigger event
				// $this->user_activated($this->ci->db->insert_id());

				// Delete user from temp
				$this->ci->user_temp->delete_user($del);

				$result = TRUE;
			}
		}

		return $result;
	}

	function change_password($old_pass, $new_pass)
	{
		// Load Models
		$this->ci->load->model('user_model');

		// Default return value
		$result = FAlSE;

		// Search current logged in user in database
		if ($query = $this->ci->user_model->get_user_by_id($this->ci->session->userdata('DX_user_id')) AND $query->num_rows() > 0)
		{
			// Get current logged in user
			$row = $query->row();

			// Check if old password correct
			$hasher = new PasswordHash(
					$this->ci->config->item('phpass_hash_strength'),
					$this->ci->config->item('phpass_hash_portable'));

			if ($hasher->CheckPassword($old_pass, $row->password))
			{
				// Success

				// Hash new password using phpass
				$hashed_password = $hasher->HashPassword($new_pass);

				// Replace old password with new password
				$this->ci->user_model->change_password($this->ci->session->userdata('DX_user_id'), $hashed_password);

				// Trigger event
				$this->user_changed_password($this->ci->session->userdata('DX_user_id'), $hashed_password);

				$result = TRUE;
			}
			else
			{
				$this->_auth_error = $this->ci->lang->line('auth_incorrect_old_password');
			}
		}

		return $result;
	}


	/* End of main function */

}
