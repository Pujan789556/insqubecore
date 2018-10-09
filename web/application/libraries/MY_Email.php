<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CodeIgniter Extended Email Class
 *
 * This class is extended to bypass SSL verification in order to
 * allow Self-signed Certificate installed in Mail-Server.
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author		IP Bastola
 * @link		https://codeigniter.com/user_guide/libraries/email.html
 */
class MY_Email extends CI_Email {

	/**
	 * Used as the User-Agent and X-Mailer headers' value.
	 * Overwrite default with InsQube
	 *
	 * @var	string
	 */
	public $useragent	= 'InsQube';

	/**
	 * SMTP Password
	 *
	 * @var	string
	 */
	public $verify_peer	= TRUE;

	// --------------------------------------------------------------------

	/**
	 * Constructor - Simply call parent construct
	 *
	 *
	 * @param	array	$config = array()
	 * @return	void
	 */
	public function __construct(array $config = array())
	{
		parent::__construct($config);

		// Other Config
		$this->verify_peer = $config['verify_peer'] ?? $this->verify_peer;
	}

	// --------------------------------------------------------------------

	/**
	 * Initialize the Email Data
	 *
	 * @param	bool
	 * @return	MY_Email
	 */
	public function clear($clear_attachments = FALSE)
	{
		// Resett parent's attributes
		parent::clear($clear_attachments);

		// Reset it's attributes
		$this->verify_peer = TRUE;

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * SMTP Connect
	 *
	 * This method implements to bypass ssl verification in order to allow
	 * self-signed certificate.
	 *
	 * @return	string
	 */
	protected function _smtp_connect()
	{
		if (is_resource($this->_smtp_connect))
		{
			return TRUE;
		}

		$ssl = ($this->smtp_crypto === 'ssl') ? 'ssl://' : '';

		$this->_smtp_connect = fsockopen($ssl.$this->smtp_host,
							$this->smtp_port,
							$errno,
							$errstr,
							$this->smtp_timeout);

		/**
		 * Bypass SSL Verification to allow Self-signed Certificate?
		 */
		if( !$this->verify_peer )
		{
			stream_context_set_option($this->_smtp_connect, 'ssl', 'verify_peer', FALSE);
		}


		if ( ! is_resource($this->_smtp_connect))
		{
			$this->_set_error_message('lang:email_smtp_error', $errno.' '.$errstr);
			return FALSE;
		}

		stream_set_timeout($this->_smtp_connect, $this->smtp_timeout);
		$this->_set_error_message($this->_get_smtp_data());

		if ($this->smtp_crypto === 'tls')
		{
			$this->_send_command('hello');
			$this->_send_command('starttls');

			/**
			 * STREAM_CRYPTO_METHOD_TLS_CLIENT is quite the mess ...
			 *
			 * - On PHP <5.6 it doesn't even mean TLS, but SSL 2.0, and there's no option to use actual TLS
			 * - On PHP 5.6.0-5.6.6, >=7.2 it means negotiation with any of TLS 1.0, 1.1, 1.2
			 * - On PHP 5.6.7-7.1.* it means only TLS 1.0
			 *
			 * We want the negotiation, so we'll force it below ...
			 */
			$method = is_php('5.6')
				? STREAM_CRYPTO_METHOD_TLSv1_0_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT
				: STREAM_CRYPTO_METHOD_TLS_CLIENT;
			$crypto = stream_socket_enable_crypto($this->_smtp_connect, TRUE, $method);

			if ($crypto !== TRUE)
			{
				$this->_set_error_message('lang:email_smtp_error', $this->_get_smtp_data());
				return FALSE;
			}
		}

		return $this->_send_command('hello');
	}

}
