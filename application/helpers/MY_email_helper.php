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
		$CI->email->initialize($config);

		//
		// Mail Specific Settings
		//

		// FROM
		$from = isset($options['from']) ? $options['from'] : '';
		$from_email = isset($from['email']) ? $from['email'] : 'noreplay@example.com';
		$from_name 	= isset($from['name']) ? $from['name'] : 'InsQube Administrator';
		if( !empty($from_email) || !empty($from_name) )
		{
			$CI->email->from( $from_email, $from_name );
		}

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
			log_email($log_string);
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
		$log_file = APPPATH.'logs/mail.log';
		$handle = fopen($log_file, 'a+') or die('Cannot open file:  '.$log_file);
		fwrite( $handle, 
				PHP_EOL . 
				'===================================================' . PHP_EOL .
				'========== EMAIL-LOG@' . date('Y-m-d H:i:s') . '==========' . PHP_EOL . 
				'===================================================' . PHP_EOL );
		
		fwrite($handle, $log . PHP_EOL);
		fclose($handle);
	}
}