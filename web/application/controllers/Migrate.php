<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube Command-Line Controller - For DB Migration
 *
 */
class Migrate extends CI_Controller
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
     * 		$ php index.php migrate > err.log
     * 		$ CI_ENV=production php index.php migrate > ~/viz.log
     *
     * @return void
     */
	public function index()
	{
		$this->load->library('migration');

        if ($this->migration->current() === FALSE)
        {
                show_error($this->migration->error_string());
        }
	}
}
