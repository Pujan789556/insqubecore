<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube Command-Line Controller - For DB Migration
 *
 */
class Migrations extends CI_Controller
{
	/**
	 * Application Settings from DB
	 *
	 * @var object
	 */
	public $settings;

	// --------------------------------------------------------------------

	public function __construct()
    {
            // Call the CI_Model constructor
            parent::__construct();

            if(! is_cli() ){
            	show_404();
            	exit(1);
            }

            // Default Timezone Set: Katmandu
            date_default_timezone_set('Asia/Katmandu');


            // App settings
            $this->load->model('setting_model');
            $this->_app_settings();

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

	// -------------------------------------------------------------------------------------

    /**
     * Default CLI Method
     *
     * Usage(dev/production):
     * 		$ php index.php migrations > err.log
     * 		$ CI_ENV=production php index.php migrations > ~/viz.log
     *
     * @return void
     */
	public function index()
	{
		exit(0);
	}

	// -------------------------------------------------------------------------------------

	/**
	 * Object - Customer Relation Migration
	 *
	 * Usage
	 * 		$ php index.php migrations m20180404
	 * 		$ CI_ENV=production php index.php migrations m20180404
	 * @return void
	 */
	public function m20180404( )
	{
		$this->load->model('migrations/m20180404_model');
		$this->m20180404_model->migrate();
	}

	// -------------------------------------------------------------------------------------

	/**
	 * Object - Customer Relation Migration
	 *
	 * Usage
	 * 		$ php index.php migrations m20180507
	 * 		$ CI_ENV=production php index.php migrations m20180507
	 * @return void
	 */
	public function m20180507( )
	{
		$this->load->model('migrations/m20180507_model');
		$this->m20180507_model->migrate();
	}

	// -------------------------------------------------------------------------------------

	/**
	 * Object - Item Re Structured
	 *
	 * Usage
	 * 		$ php index.php migrations m20180530
	 * 		$ CI_ENV=production php index.php migrations m20180530
	 * @return void
	 */
	public function m20180530( )
	{
		$this->load->model('migrations/m20180530_model');
		$this->m20180530_model->migrate();
	}

	// -------------------------------------------------------------------------------------

	/**
	 * Save Policy Schedule on All active Policies
	 *
	 * Usage
	 * 		$ php index.php migrations m20180713
	 * 		$ CI_ENV=production php index.php migrations m20180713
	 * @return void
	 */
	public function m20180713( )
	{
		$this->load->model('migrations/m20180713_model');
		$this->m20180713_model->migrate();
	}

	// -------------------------------------------------------------------------------------

	/**
	 * Update Endorsement Attributes
	 * 		- issured date
	 * 		- start date
	 * 		- end date
	 * 		- customer id
	 * 		- sold by id
	 * 	from Policy to Endorsements
	 *
	 *
	 * Usage
	 * 		$ php index.php migrations m20180719
	 * 		$ CI_ENV=production php index.php migrations m20180719
	 * @return void
	 */
	public function m20180719( )
	{
		$this->load->model('migrations/m20180719_model');
		$this->m20180719_model->migrate();
	}

	// -------------------------------------------------------------------------------------

	/**
	 * Save Policy Schedule PDF of all Active Policies
	 *
	 *
	 * Usage
	 * 		$ php index.php migrations m20180822
	 * 		$ CI_ENV=production php index.php migrations m20180822
	 * @return void
	 */
	public function m20180822( )
	{
		$this->load->model('migrations/m20180822_model');
		$this->m20180822_model->migrate();
	}

	// -------------------------------------------------------------------------------------

	/**
	 * Portfolio Risks Update
	 *
	 * 	- Remove Old Riks table and manage risks on JSON inside portfolio table
	 *
	 *
	 * Usage
	 * 		$ php index.php migrations m20180829
	 * 		$ CI_ENV=production php index.php migrations m20180829
	 * @return void
	 */
	public function m20180829( )
	{
		$this->load->model('migrations/m20180829_model');
		$this->m20180829_model->migrate();
	}

	// -------------------------------------------------------------------------------------

	/**
	 * Update District, VDC/Municipality Structure with Latest Data
	 *
	 *
	 *
	 * Usage
	 * 		$ php index.php migrations m20180916
	 * 		$ CI_ENV=production php index.php migrations m20180916
	 * @return void
	 */
	public function m20180916( )
	{
		$this->load->model('migrations/m20180916_model');
		$this->m20180916_model->migrate();
	}

}
