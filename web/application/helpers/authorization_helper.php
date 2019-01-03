<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube API HELPR - Authorization Class
 *
 * This is used for API authorization
 *
 * @package     InsQube
 * @subpackage  Helpers
 * @category    Helpers
 * @author      IP Bastola <ip.bastola@gmail.com>
 * @link
 */
// ------------------------------------------------------------------------

class AUTHORIZATION
{
    public static function validateTimestamp($token)
    {
        $CI =& get_instance();
        $token = self::validateToken($token);
        if ($token != false && (now() - $token->timestamp < ($CI->config->item('token_timeout') * 60))) {
            return $token;
        }
        return false;
    }

    public static function validateToken($token)
    {
        $CI =& get_instance();
        return JWT::decode($token, $CI->config->item('jwt_key'));
    }

    public static function generateToken($data)
    {
        $CI =& get_instance();
        return JWT::encode($data, $CI->config->item('jwt_key'));
    }

}