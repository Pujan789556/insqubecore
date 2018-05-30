<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube Command-Line Controller - For DB Migration
 *
 */
class Migrations extends CI_Controller
{

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

}
