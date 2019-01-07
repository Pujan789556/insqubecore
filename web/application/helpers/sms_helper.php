<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube SMS Helper Functions
 *
 * This file contains helper functions related to store procedure call
 *
 * @package		InsQube
 * @subpackage	Helpers
 * @category	Helpers
 * @author		IP Bastola <ip.bastola@gmail.com>
 * @link
 */


// ------------------------------------------------------------------------

if ( ! function_exists('send_sms'))
{
    /**
     * Send SMS
     *
     * This helper function is used to send sms on various occassion to the
     * customer or respected stakeholder.
     *
     * This helper function is built to send sms using sparrow sms api
     *
     * @param string $receivers
     * @param string $message
     * @return array
     */
	function send_sms( $receivers, $message )
	{
        $result = [
            "response_code" => 1007,
            "response"      => "Invalid Receiver"
        ];

        /**
         * Build SMS Receivers
         * --------------------
         *
         * If we want to send real SMS, we will cleanup mobile numbers,
         * if dev env, use dev test numbers, else leave as it is
         */
        if( in_array(SMS_MODE, ['sms', 'both']) )
        {
            $receivers = build_sms_receivers($receivers);
        }


        /**
         * Append SMS Signature
         */
        $message = $message . "\n" . SMS_SIGNATURE;


        /**
         * Let's Fire SMS
         */
        if( $receivers && in_array(SMS_MODE, ['sms', 'both']) )
        {
            $CI =& get_instance();
            $CI->load->library('restclient');

            $data = array(
                'token'     => SPARROW_SMS_TOKEN,
                'from'      => SPARROW_SMS_IDENTITY,
                'to'        => $receivers,
                'text'      => $message
            );
            $url = SPARROW_SMS_API;
            $result = $CI->restclient->post( $url, $data);
        }

        /**
         * Log SMS?
         */
        if( in_array(SMS_MODE, ['log', 'both']) )
        {
            $exception_message = '';

            try {
              log_sms($receivers, $message);
            }
            //catch exception
            catch(Exception $e) {
              // echo 'Message: ' .$e->getMessage();
                $exception_message =  $e->getMessage();
            }

            if( SMS_MODE === 'log')
            {
                $result = [
                    "response_code" => 'unknown',
                    "response"      => "SMS was logged for debugging purpose. " . $exception_message
                ];
            }
            else
            {
                $result["response"] .=  " " . $exception_message;
            }
        }
        return $result;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('build_sms_receiver'))
{
    /**
     * Build Receiver Mobile Number
     *
     * If development/stage/test environment, we put test number,
     * else return the original numbers
     *
     * @param string $receivers    comma separated mobile number
     * @return bool
     */
    function build_sms_receivers( $receivers )
    {
        if(APP_ENV !== 'production')
        {
            $receivers = SMS_DEV_TEST_MOBILE;
        }

        $valid_receivers    = [];
        $receivers          = explode(',', $receivers);
        foreach($receivers as $single)
        {
            $single = trim($single);
            if(valid_sms_receiver($single))
            {
                $valid_receivers[] = $single;
            }
        }

        $valid_receivers_str = $valid_receivers ? implode(',', $valid_receivers) : '';
        return $valid_receivers_str;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('valid_sms_receiver'))
{
    /**
     * Valid Mobile Number?
     *
     * @param int $mobile
     * @return bool
     */
    function valid_sms_receiver( $mobile )
    {
        return preg_match('/^(98|97)[0-9]{8}$/', $mobile);
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('log_sms'))
{
    /**
     * Log SMS
     *
     * Log SMS for Development Debugging
     *
     * @param   string  $log
     * @return  bool
     */
    function log_sms($receivers, $message)
    {
        $log_file = SMS_LOG_PATH;

        if ( !file_exists($log_file) ) {
            throw new Exception('Exception Occured - [Helper: sms_helper][Method: log_sms()]: File not found: ' . $log_file);
        }

        $handle = fopen($log_file, 'a+');
        if ( !$handle ) {
            throw new Exception('Exception Occured - [Helper: sms_helper][Method: log_sms()]: Could not open file: ' . $log_file);
        }
        else
        {
            fwrite( $handle,
                PHP_EOL .
                '========== SMS-LOG@' . date('Y-m-d H:i:s') . '==========' . PHP_EOL );

            fwrite($handle, 'RECEIVERS: ' . $receivers . PHP_EOL . PHP_EOL);
            fwrite($handle, 'MESSAGE: ' . $message . PHP_EOL);
            fwrite($handle, '------------------------------------------------------------' . PHP_EOL);
            fclose($handle);
        }
    }
}

// ------------------------------------------------------------------------



