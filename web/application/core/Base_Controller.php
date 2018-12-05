<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Base Controller
 *
 * InsQuebe Base Controller
 *
 * @package     InsQube
 * @author      IP Bastola
 * @link       	http://www.insqube.com
 */

class Base_Controller extends CI_Controller
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

	/**
	 * Application's Current Quarter of Current Fiscal Year from DB
	 *
	 * @var object
	 */
	public $current_fy_quarter;

	/**
	 * Application's Current Month of Current Fiscal Year from DB
	 *
	 * @var object
	 */
	public $current_fy_month;


	// --------------------------------------------------------------------

	/**
	 * Class constructor
	 */
	public function __construct()
	{
		// Call the CI_Model constructor
        parent::__construct();

        /**
         * Date/Time Zone
         */
        date_default_timezone_set('Asia/Katmandu');

        /**
		 * App Settings
		 */
		$this->_app_settings();
		$this->_app_fiscal_year();

		/**
		 * Loggedin User
		 */
		$this->_app_user();
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
		if( $this->dx_auth->is_logged_in() )
		{
			$this->user = $this->user_model->get_loggedin_user($this->dx_auth->get_user_id());
		}
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
		$today = date('Y-m-d');
        $this->current_fiscal_year = $this->fiscal_year_model->get_fiscal_year($today);

        /**
         * Current Quarter
         */
        $this->current_fy_quarter = $this->fy_quarter_model->get_quarter_by_date($today);

        /**
         * Current Month
         */
        $this->current_fy_month = $this->fy_month_model->get_month_by_date($today);
	}


}

/* End of file Base_Controller.php */
/* Location: /core/Base_Controller.php */