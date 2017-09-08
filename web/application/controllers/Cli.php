<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube Command-Line Controller
 *
 * All the background Tasks or Command Line Jobs are Carried Out using this controller.
 */
class Cli extends CI_Controller
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
     * 		$ php index.php cli > err.log
     * 		$ CI_ENV=production php index.php cli > ~/viz.log
     *
     * @return void
     */
	public function index()
	{
		exit(0);
	}

	// -------------------------------------------------------------------------------------

	/**
	 * Import Today's Exchange Rate from NRB
	 *
	 * If No date is supplied, it bring's today's Exchange rate
	 *
	 * Usage(dev/production):
	 * 		$ php index.php cli exchange_rate
	 * 		$ php index.php cli exchange_rate '2016-02-09'
	 *
	 * 		$ CI_ENV=production php index.php cli exchange_rate
	 * 		$ CI_ENV=production php index.php cli exchange_rate '2016-02-09'
	 *
	 * @param date $date=NULL
	 * @return void
	 */
	public function exchange_rate( $date=NULL )
	{
		$api 	= 'https://nrb.org.np/exportForexJSON.php';
		$date 	= $date ?? date('Y-m-d');

		/**
		 * Valid Date Supplied?
		 */
		if( $date && !is_valid_date_format($date, 'Y-m-d') )
		{
			echo("ERROR \t Invalid Date Format. It should be formatted as 'YYYY-MM-DD'.\n");
			exit(1);
		}

		/**
		 * Load Model
		 */
		$this->load->model('exchange_rate_model');


		/**
		 * Check Duplicate
		 */
		if( $this->exchange_rate_model->check_duplicate(['exchange_date' => $date]) )
    	{
			echo("ERROR - Data already exists for date({$date}).\n");
			exit(1);
    	}


		echo("Building API parameters... ");

	        $date_parts = explode('-', $date);
	        $data = array(
	            'YY'     => $date_parts[0],
	            'MM'     => $date_parts[1],
	            'DD'     => $date_parts[2]
	        );

        echo("OK.\n");


        echo("Performing API call... ");

	        $this->load->library('restclient');
	        $result = $this->restclient->get( $api, $data);

        echo("OK.\n");


        $exchange_rates = $result['Conversion']['Currency'] ?? NULL;
        if($exchange_rates)
        {
        	/**
        	 * Let's Import Into Database
        	 */
        	echo("Formatting data to import... ");

	        	// Remove Date form each array
		        foreach($exchange_rates as &$single)
		        {
		            unset($single['Date']);
		        }

	        echo("OK.\n");

	        /**
	         * Importing into Database
	         */
    		echo("Importing into database... ");

    			$exchange_data = [
    				'exchange_date' 	=> $date,
    				'exchange_rates' 	=> json_encode($exchange_rates)
    			];
        		$done = $this->exchange_rate_model->insert($exchange_data, TRUE);

        	echo( $done ? "OK.\n" : "FAILED.\n");
        }
        else
        {
        	echo("No data found for date({$date}).\n");
        }
	}

}
