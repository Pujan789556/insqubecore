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
	 * Controller Data
	 *
	 * This data is passed into view for further procession
	 *
	 * @var array
	 */
	public $data = [];

	/**
	 * Application Settings from DB
	 *
	 * @var object
	 */
	public $settings;

	/**
	 * Current User Info
	 *
	 * @var object
	 */
	public $user = NULL;

	/**
	 * Application's Current Fiscal Year from DB
	 *
	 * @var object
	 */
	public $current_fiscal_year;

	// --------------------------------------------------------------------

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
		define('THEME_URL', site_url('public/themes/AdminLTE-2.3.6/'));

		/**
		 * Active Primary Navigation Data
		 */
		$this->active_nav_primary();

		/**
		 * App Settings
		 */
		$this->load->model('setting_model');
		$this->_app_settings();
		$this->_app_fiscal_year();

		/**
		 * Loggedin User
		 */
		$this->load->model('user_model');
		$this->_app_user();
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
	 * Set Application Settings from DB
	 *
	 * @return void
	 */
	private function _app_settings()
	{
		/**
         * Get Cached Result, If no, cache the query result
         */
        $this->settings = $this->setting_model->get(['id' => 1]);
	}

	// --------------------------------------------------------------------

	/**
	 * Set Loggedin User from DB
	 *
	 * @return void
	 */
	private function _app_user()
	{
		/**
         * Get Cached Result, If no, cache the query result
         */
        $this->user = $this->user_model->get_loggedin_user($this->dx_auth->get_user_id());
	}

	// --------------------------------------------------------------------

	/**
	 * Set Current Fiscal Year From DB
	 *
	 * @return void
	 */
	private function _app_fiscal_year()
	{
		/**
         * Get Cached Result, If no, cache the query result
         */
		$this->load->model('fiscal_year_model');
        $this->current_fiscal_year = $this->fiscal_year_model->get_fiscal_year(date('Y-m-d'));
	}



	/**
	 *  @TODO: Add Authorization/Authentication Related Functions
	 */



}

/* End of file MY_Controller.php */
/* Location: /core/MY_Controller.php */