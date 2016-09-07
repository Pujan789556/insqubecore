<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * MY Controller
 *
 * InsQuebe is an insurance application built on CodeIgniter 3
 *
 * @package     InsQube
 * @author      IP Bastola
 * @link       	http://www.insqube.com
 */

class MY_Controller extends CI_Controller
{
	/**
	 * Class constructor
	 */
	public function __construct()
	{
		parent::__construct();

		/**
		 * Define Theme
		 */ 
		define('THEME_URL', site_url('public/themes/AdminLTE-2.3.6/'));

		/**
		 * Check Login?
		 */
	}

	/**
	 *  @TODO: Add Authorization/Authentication Related Functions
	 */



}

/* End of file MY_Controller.php */
/* Location: /core/MY_Controller.php */