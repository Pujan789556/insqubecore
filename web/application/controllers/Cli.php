<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube Command-Line Controller
 *
 * All the background Tasks or Command Line Jobs are Carried Out using this controller.
 */
class Cli extends Base_Controller
{

	public function __construct()
    {
            // Call the CI_Model constructor
            parent::__construct();

            if(! is_cli() ){
            	show_404();
            	exit(1);
            }
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
     * RI - Regeneration Test
     *
     * Usage(dev/production):
     * 		$ php index.php cli ri_rebuild > err.log
     * 		$ CI_ENV=production php index.php cli ri_rebuild
     *
     * @return void
     */
	public function ri_rebuild()
    {
        $this->load->helper('ri');
        $paid_installments = $this->db->select('id')
                                      ->from('dt_policy_installments')
                                      ->where('status', IQB_POLICY_INSTALLMENT_STATUS_PAID)
                                      ->get()->result();


        foreach($paid_installments as $single )
        {
            try {
                $data = RI__distribute( $single->id );
            } catch (Exception $e) {
                print "Installment: {$single->id}, " . $e->getMessage() . "\n\r";
            }

            print "Installment: {$single->id}, RI-Basic:  {$data['ri_transaction_id_basic']}, RI-Pool:  {$data['ri_transaction_id_pool']} \n\r";
        }
    }

	// -------------------------------------------------------------------------------------

	/**
	 * Import Today's Forex Rate from NRB
	 *
	 * If No date is supplied, it bring's today's Exchange rate
	 *
	 * Usage(dev/production):
	 * 		$ php index.php cli forex
	 * 		$ php index.php cli forex '2016-02-09'
	 *
	 * 		$ CI_ENV=production php index.php cli forex
	 * 		$ CI_ENV=production php index.php cli forex '2016-02-09'
	 *
	 * @param date $date=NULL
	 * @return void
	 */
	public function forex( $date=NULL )
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
		$this->load->model('forex_model');


		/**
		 * Check Duplicate
		 */
		if( $this->forex_model->check_duplicate(['exchange_date' => $date]) )
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


        $forex_rates = $result['Conversion']['Currency'] ?? NULL;
        if($forex_rates)
        {
        	/**
        	 * Let's Import Into Database
        	 */
        	echo("Formatting data to import... ");

	        	// Remove Date form each array
		        foreach($forex_rates as &$single)
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
    				'exchange_rates' 	=> json_encode($forex_rates)
    			];
        		$done = $this->forex_model->insert($exchange_data, TRUE);

        	echo( $done ? "OK.\n" : "FAILED.\n");
        }
        else
        {
        	echo("No data found for date({$date}).\n");
        }
	}

}
