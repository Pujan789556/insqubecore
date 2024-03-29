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

class MY_Controller extends Base_Controller
{

	/**
	 * Class constructor
	 */
	public function __construct()
	{
		parent::__construct();

		/**
		 * Check logged in if the controller is not Auth
		 */
		$this->_check_logged_in();

		/**
		 * Define Theme
		 */
		define('THEME_URL', site_url('static/themes/AdminLTE-2.3.6/'));

		/**
		 * Active Primary Navigation Data
		 */
		$this->active_nav_primary();

		/**
		 * Check if system if offline
		 */
		$this->_check_offline();
	}

	// --------------------------------------------------------------------

	/**
	 * Build Primary Navigation Data
	 *
	 * This will build left sidebar active navigation control data
	 * The nav data can be set from the child controler to pass custom nav data.
	 *
	 * The default data will be as:
	 * 	level_0 : controller name (module)
	 * 	level_1 : method name
	 *
	 * The multiple nav levels help us to build the multi-level sidebar menu
	 *
	 * @return void
	 */
	public function active_nav_primary($nav_data = [])
	{
		if( !empty($nav_data))
		{
			$this->data['_nav_primary'] = $nav_data;
		}
		else
		{
			$this->data['_nav_primary'] = [
				'level_0' => $this->router->class,
				'level_1' => $this->router->method
			];
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Check if user is logged in?
	 *
	 * @return void
	 */
	public function _check_logged_in()
	{
		$controller = $this->router->fetch_class();

		if ($controller !== 'auth' && !$this->dx_auth->is_logged_in() )
		{
			$this->dx_auth->deny_access('login');
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Check Offline
	 *
	 * Show offline message and exit if the sytem is set to be offline
	 *
	 * @return void
	 */
	private function _check_offline()
	{
		$controller = $this->router->fetch_class();
		if( $controller !== 'auth' && !$this->dx_auth->is_admin() && $this->settings->flag_offline == IQB_FLAG_ON )
		{
			// Set offline data
			$offline_data = [
				'title' 		=> 'We are Offline!',
				'message' 		=> nl2br($this->settings->offline_message) . '<br/><br/>Please ' . anchor('auth/logout', 'Logout') . ' before you leave.'
			];

			/**
			 * Check if this is an AJAX Request
			 */
			$this->template->set_template('offline');
			if(  $this->input->is_ajax_request() )
			{
				$this->template->json($offline_data, 503);
				exit(1);
			}

			// Echo Message and Exit
			echo $this->load->view('offline/message', $offline_data, TRUE);
			exit(1);
		}
	}

	/**
	 *  @TODO: Add Authorization/Authentication Related Functions
	 */
}

/* End of file MY_Controller.php */
/* Location: /core/MY_Controller.php */