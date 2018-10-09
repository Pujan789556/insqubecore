<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Email Helper Functions
 *
 * 	New email helper function added for a better email logging/tracking
 * 	i.e. in a local development environment, we will be using log to view
 * 	email instead of sending emails (idea brought by Laravel's email feature)
 */

// ------------------------------------------------------------------------

if ( ! function_exists('send_email'))
{
	/**
	 * Send Email
	 *
	 * If Environment local or mail setting to log, send email to log file
	 * Else send email using CI email library
	 *
	 * @param	array	$options
	 * @return	bool
	 */
	function send_email($options = [])
	{
		$CI =& get_instance();

		// Load email Library
		$CI->load->library('email');

		// Clear any previous attachment
		$CI->email->clear();

		// Configuration
    	$config['mailtype'] = isset($options['mailtype']) ? $options['mailtype'] : 'html';
		$config['validate'] = TRUE;

		// Mailtrap Configruation if any
		if( MAIL_DRIVER === 'mailtrap' || MAIL_DRIVER === 'mail' )
		{
			$mailtrap_config = Array(
				'protocol' 	=> 'smtp',
				'smtp_host' => MAIL_HOST,
				'smtp_port' => MAIL_PORT,
				'smtp_user' => MAIL_USERNAME,
				'smtp_pass' => MAIL_PASSWORD,
				'smtp_crypto' 	=> MAIL_ENCRYPTION,
				'smtp_timeout' 	=> 30,
				'verify_peer' 	=> MAIL_VERIFY_PEER,
				'crlf' 			=> "\r\n",
				'newline' 		=> "\r\n"
			);
			$config = array_merge($config, $mailtrap_config);
		}
		$CI->email->initialize($config);



		//
		// Mail Specific Settings
		//

		// FROM
		$CI->email->from( MAIL_USERNAME, $CI->settings->orgn_name_en );

		// REPLY-TO
		$CI->email->reply_to(MAIL_NO_REPLY ?? MAIL_USERNAME, $CI->settings->orgn_name_en);

		// TO
		$CI->email->to($options['to']);

		// Subject
		$CI->email->subject($options['subject']);

		// Message
		$CI->email->message($options['message']);

		/**
		 * Send or Log
		 */
		if( MAIL_DRIVER === 'log' )
		{
			$CI->email->send(FALSE);
			$log_string = $CI->email->print_debugger();
			//trigger exception in a "try" block
			try {
			  log_email($log_string);
			}
			//catch exception
			catch(Exception $e) {
			  // echo 'Message: ' .$e->getMessage();
			}

			return TRUE;
		}
		else
		{
			return $CI->email->send();
		}
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('log_email'))
{
	/**
	 * Log e-mail
	 *
	 * If Environment local or mail setting to log
	 * Write email into the log file for debugging purpose
	 *
	 * @param	string	$log
	 * @return	bool
	 */
	function log_email($log)
	{
		$log_file = MAIL_LOG_PATH;

		if ( !file_exists($log_file) ) {
            throw new Exception('Exception Occured - [Helper: MY_email_helper][Method: log_email()]: File not found: ' . $log_file);
        }

		$handle = fopen($log_file, 'a+');
	    if ( $handle == false) {
	        throw new Exception('Exception [Helper: MY_email_helper][Method: log_email()]: Could not open file: ' . $log_file);
	    }
	    else
	    {
	    	fwrite( $handle,
				PHP_EOL .
				'===================================================' . PHP_EOL .
				'========== EMAIL-LOG@' . date('Y-m-d H:i:s') . '==========' . PHP_EOL .
				'===================================================' . PHP_EOL );

			fwrite($handle, $log . PHP_EOL);
			fclose($handle);
	    }
	}
}