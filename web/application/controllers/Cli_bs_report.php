<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube Command-Line Controller - Beema Samiti Report
 *
 * All the background Tasks or Command Line Jobs are Carried Out using this controller.
 */
class Cli_bs_report extends CI_Controller
{
    /**
     * Application Settings from DB
     *
     * @var object
     */
    public $settings;

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

    // --------------------------------------------------------------------


	public function __construct()
    {
        // Call the CI_Model constructor
        parent::__construct();

        if(! is_cli() ){
        	show_404();
        	exit(1);
        }

        /**
         * App Settings
         */
        $this->load->model('setting_model');
        $this->_app_settings();
        $this->_app_fiscal_year();



        // Default Timezone Set: Katmandu
        date_default_timezone_set('Asia/Katmandu');

        // Load models
        $this->load->model('fiscal_year_model');
        $this->load->model('bs_report_model');
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
    }

	// -------------------------------------------------------------------------------------

    /**
     * Default CLI Method
     *
     * Usage(dev/production):
     * 		$ php index.php cli_bs_report > err.log
     * 		$ CI_ENV=production php index.php cli_bs_report > ~/viz.log
     *
     * @return void
     */
	public function index()
	{
		exit(0);
	}


	// -------------------------------------------------------------------------------------

	/**
	 * Generate Quarterly Report for Underwriting
	 *
	 * NOTE: All portfolios except Agriculture (Which is Monthly, having different structure)
	 *
	 * Usage(dev/production):
	 * 		$ php index.php cli_bs_report uw_quarterly '19' '1'
	 * 		$ php index.php cli_bs_report uw_quarterly '19' '1'
	 *
	 * 		$ CI_ENV=production php index.php cli_bs_report uw_quarterly '19' '1'
	 * 		$ CI_ENV=production php index.php cli_bs_report uw_quarterly '19' '1'
	 *
	 * @param inte $fiscal_yr_id
	 * @param int $fy_quarter
	 * @return void
	 */
	public function uw_quarterly( $fiscal_yr_id, $fy_quarter )
	{
		/**
		 * Valid Fiscal Year?
		 */
		$fy_record = $this->fiscal_year_model->get($fiscal_yr_id);
		if(!$fy_record)
		{
			echo "ERROR: Fiscal Year not found!" . PHP_EOL;
			exit(1);
		}

		/**
		 * Valid Quarter?
		 */

		if(!is_valid_fy_quarter($fy_quarter))
		{
			echo "ERROR: Invalid fiscal year quarter. It must be between 1 and 4!" . PHP_EOL;
			exit(1);
		}

		$fy_code_np     = $fy_record->code_np;
		$this->load->model('portfolio_model');
        $this->load->model('bsrs_heading_model');

        $portfolios 			= $this->portfolio_model->dropdown_children();
        $bs_heading_formatted 	= [];
        foreach($portfolios as $portfolio_id => $portfolio_name)
        {
            $bs_headings = $this->bsrs_heading_model->by_portfolio($portfolio_id, 'policy');

            // Let's Group Heading with Heading Type ID
            $heading_type_ids = [];
            foreach($bs_headings as $single)
            {
                $heading_type_ids[] = $single->heading_type_id;
            }

            $heading_type_ids = array_values(array_unique($heading_type_ids));
            if($heading_type_ids)
            {
                $bs_heading_formatted["{$portfolio_id}"] = $heading_type_ids;
            }
        }

       $portfolio_codes = $this->portfolio_model->dropdown_children_code();

        /**
         * Let's build the query
         *
         */
        $SQLS       = [];
        $SQL_TEST   = [];
        foreach($bs_heading_formatted as $portfolio_id => $hd_type_ids)
        {
            $sql_group_headings = [];
            $select_sql         = [];
            $from_join_sql           = [];

            for($i = 0; $i < count($hd_type_ids); $i++ )
            {
                $heading_type_id = $hd_type_ids[$i];
                $inline_sql =   "(
                                    SELECT
                                        P.id as policy_id, P.district_id, H.id as bsrs_heading_id, H.code AS bsrs_code, H.name as bsrs_name
                                    FROM dt_policies AS P
                                    JOIN rel_policy__bsrs_heading REL ON REL.policy_id = P.id
                                    JOIN bsrs_headings H ON H.id = REL.bsrs_heading_id
                                    WHERE P.portfolio_id = {$portfolio_id} AND H.heading_type_id = {$heading_type_id}
                                ) P{$i}";

                if($i == 0 )
                {
                    $select_sql[] = "SELECT P0.bsrs_code AS bsrs_code0, P0.bsrs_name AS bsrs_name0";
                    $from_join_sql[] = "FROM {$inline_sql}";
                }
                else
                {
                    $select_sql[] = "P{$i}.bsrs_code AS bsrs_code{$i}, P{$i}.bsrs_name AS bsrs_name{$i}";
                    $from_join_sql[] = "LEFT JOIN {$inline_sql} ON P0.policy_id = P{$i}.policy_id";
                }

                $sql_group_headings[] = "P{$i}.bsrs_heading_id";
            }


            $select_sql         = implode(', ', $select_sql);
            $from_join_sql      = implode(PHP_EOL, $from_join_sql);
            $sql_group_headings = implode(', ', $sql_group_headings);

            $SQL_TEST[]     = $select_sql . PHP_EOL . $from_join_sql;


            /**
             * CSV Name:
             * 	bs_qr-<fy_code_np>-<fy_quarter>-<portfolio_code>.csv
             */
            $filename = 'bs_qr-' . $fy_code_np . '-' . $fy_quarter . '-' . $portfolio_codes[$portfolio_id] . '.csv';
            $SQLS[$filename] = "{$select_sql},
                            R.id AS region_id,
                            COUNT(P0.policy_id) AS policy_count,
                            SUM(E.net_amt_sum_insured) AS amt_sum_insured,
                            SUM(PINST.amt_basic_premium + PINST.amt_pool_premium) AS amt_total_premium
                        {$from_join_sql}
                        JOIN dt_endorsements E ON E.policy_id = P0.policy_id
                        JOIN master_districts D ON P0.district_id = D.id
                        JOIN master_regions R ON D.region_id = R.id
                        JOIN dt_policy_installments PINST ON PINST.endorsement_id = E.id
                        WHERE
                            PINST.fiscal_yr_id = {$fiscal_yr_id} AND
                            PINST.fy_quarter = {$fy_quarter}
                        GROUP BY P0.policy_id, {$sql_group_headings};";
        }
        $this->_csv_export($fy_record, $fy_quarter, $SQLS, IQB_BS_REPORT_TYPE_QUARTELRY);
	}

    // -------------------------------------------------------------------------------------

    /**
     * Generate Quarterly Report for Underwriting
     *
     * NOTE: Only Agriculture Portfolios
     *
     * Usage(dev/production):
     *      $ php index.php cli_bs_report uw_monthly '18' '11'
     *      $ php index.php cli_bs_report uw_monthly '18' '11'
     *
     *      $ CI_ENV=production php index.php cli_bs_report uw_monthly '18' '11'
     *      $ CI_ENV=production php index.php cli_bs_report uw_monthly '18' '11'
     *
     * @param int $fiscal_yr_id
     * @param int $fy_month_id
     * @return void
     */
    public function uw_monthly( $fiscal_yr_id, $fy_month_id )
    {
        $this->load->model('fy_month_model');
        $this->load->model('bs_agro_category_model');
        $this->load->model('bs_agro_breed_model');
        $this->load->model('portfolio_setting_model');

        /**
         * Valid Fiscal Year?
         */
        $fy_record = $this->fiscal_year_model->get($fiscal_yr_id);
        if(!$fy_record)
        {
            echo "ERROR: Fiscal Year not found!" . PHP_EOL;
            exit(1);
        }


        $fy_month_record = $this->fy_month_model->get($fy_month_id);
        if(!$fy_month_record)
        {
            echo "ERROR: Fiscal Year Month not found!" . PHP_EOL;
            exit(1);
        }


        $fy_code_np     = $fy_record->code_np;
        $month_start    = $fy_month_record->starts_at;
        $month_end      = $fy_month_record->ends_at;


        /**
         * Let's build the query
         *
         */
        $sql    = "SELECT
                        P.id AS policy_id, P.portfolio_id, P.code AS policy_code, P.flag_dc, P.start_date, P.end_date,  O.attributes AS object_attributes,
                        C.full_name AS customer_name, C.contact AS customer_contact,
                        D.code as district_code,
                        E.net_amt_sum_insured AS amt_sum_insured,
                        PINST.amt_basic_premium + PINST.amt_pool_premium AS amt_total_premium
                    FROM dt_policies AS P
                    JOIN dt_objects O ON P.object_id = O.id
                    JOIN master_portfolio PF ON P.portfolio_id = PF.id
                    JOIN dt_customers C ON P.customer_id = C.id
                    JOIN master_districts D ON P.district_id = D.id
                    JOIN master_regions R ON D.region_id = R.id
                    JOIN dt_endorsements E ON E.policy_id = P.id
                    JOIN dt_policy_installments PINST ON PINST.endorsement_id = E.id
                    WHERE
                        P.fiscal_yr_id = {$fiscal_yr_id} AND
                        PF.parent_id = 1 AND
                        PINST.installment_date >= '{$month_start}' AND
                        PINST.installment_date <= '{$month_end}';";







        $bs_category_codes  = $this->bs_agro_category_model->dropdown_codes();
        $bs_breeds          = $this->bs_agro_breed_model->dropdown();

        $data = $this->db->query($sql)->result();

        $csv_data[] = ['NAME', 'DISTRICT', 'POLICY_NO', 'PARTICULARS', 'EFFECTIVE_FROM', 'EFFECTIVE_TO', 'SUMINSURED', 'PREMIUM', 'EXEMPTED_PREMIUM', 'AREA', 'CASTE', 'REMARKS', 'TAG_NO', 'CONTACTNO', 'ADDRESS'];

        foreach($data as $single)
        {
            load_portfolio_helper($single->portfolio_id);

            $attributes = json_decode($single->object_attributes);
            $contact    = json_decode($single->customer_contact);
            $address    = strip_tags(get_contact_widget_two_lines($single->customer_contact));
            $contact_no = $contact->mobile ?? '';



            /**
             * Tariff Record
             */
            try {

                $tariff = _OBJ_AGR_tariff_by_type($single->portfolio_id, $attributes->bs_agro_category_id);

            } catch (Exception $e) {

                die('ERROR: ' . $e->getMessage() . PHP_EOL);
            }

            /**
             * Portfolio Setting Record
             */
            $pfs_record = $this->portfolio_setting_model->get_by_fiscal_yr_portfolio($fiscal_yr_id, $single->portfolio_id);


            $_data = [
                $single->customer_name,
                $single->district_code,
                $single->policy_code,
                $bs_category_codes[$attributes->bs_agro_category_id] ?? '',
                $single->start_date,
                $single->end_date,
            ];

            for($i=0; $i < count($attributes->items->sum_insured); $i++ )
            {


                $sum_insured = $attributes->items->sum_insured[$i];

                /**
                 * Compute Individual Premium Here
                 */
                $default_rate   = floatval($tariff->rate);
                // A = SI X Default Rate %
                $A = ( $sum_insured * $default_rate ) / 100.00;
                if( $single->flag_dc == IQB_POLICY_FLAG_DC_DIRECT )
                {
                    // Direct Discount
                    $direct_discount = ( $A * $pfs_record->direct_discount ) / 100.00 ;
                    $A -= $direct_discount;
                }

                $premium = $A;

                // 75% of total individual premium
                $exempted_premium    = $premium * 75 / 100.00;

                $area       = $attributes->items->area[$i] ?? '';
                $caste      = $bs_breeds[$attributes->items->breed[$i]];
                $remarks    = '';
                $tag_no     = $attributes->items->tag_no[$i] ?? '';

                $_repeat = [$sum_insured, $premium, $exempted_premium, $area, $caste, $remarks, $tag_no, $contact_no, $address];

                $csv_data[] = array_merge($_data, $_repeat);
            }
        }




        echo PHP_EOL . "======================= CSV DATA DUMP - BS MONTHLY REPORT ( ) =======================" . PHP_EOL .
            "START: " . date("Y-m-d H:i:s") . PHP_EOL . PHP_EOL .
            "Exporting Portfolio-wise CSVs..." . PHP_EOL;


        /**
         * CSV Name:
         *  bs_qr-<fy_code_np>-<fy_month_id>-<portfolio_code>.csv
         */
        $filename = 'bs-monthly-' . $fy_code_np . '-' . $fy_month_record->name_en . '-AGR.csv';

        /**
         * Let's Save in CSV file
         */
        $csv_file_path   = INSQUBE_MEDIA_PATH . '/reports/bs/' . $filename;


        $fp = fopen($csv_file_path, 'w') or die("ERROR: Permission Denied. Unable to create file!" . PHP_EOL);
        foreach ($csv_data as $fields)
        {
            fputcsv($fp, $fields);
        }
        fclose($fp);


        /**
         * Let's ZIP the result
         */
        $this->load->dbutil();
        $this->load->helper('file');
        $this->load->library('zip');
        $this->zip->clear_data();
        $this->zip->compression_level = 9; // Highest Compression Level

        // Add to archive
        $this->zip->read_file($csv_file_path);


        // Output Zip File
        $zip_file   = "bs-monthly-{$fy_code_np}-{$fy_month_record->name_en}-AGR.zip";
        $csv_zip_file   = INSQUBE_MEDIA_PATH . '/reports/bs/' . $zip_file;

        // Remove any Zip file already
        @unlink($csv_zip_file);

        echo "Generating CSVs Zip ($zip_file)... " ;

        if( $this->zip->archive($csv_zip_file) )
        {
            // Delete Individual CSV files
            @unlink($csv_file_path);

            /**
             * Save the Report for Downloads
             */
            $report_data = array(
                'type'              => IQB_BS_REPORT_TYPE_MONTHLY,
                'fiscal_yr_id'      => $fy_record->id,
                'fy_quarter_month'  => $fy_month_record->month_id,
                'filename'          => $zip_file,
            );
            $this->bs_report_model->save($report_data);

            echo "OK" . PHP_EOL;
        }else{
            echo "FAIL" . PHP_EOL;
        }

        echo PHP_EOL . "END: " . date("Y-m-d H:i:s").PHP_EOL .
            "---------------------------------------------------------------" . PHP_EOL . PHP_EOL;
    }


	// -------------------------------------------------------------------------------------

	/**
	 * Generate CSVs reports and Zip them and Update report database
	 *
	 * @param object $fy_record
	 * @param int $fy_quarter
	 * @param array $sqls
	 * @return void
	 */
	private function _csv_export($fy_record, $fy_quarter, $sqls, $type)
    {
        $this->load->dbutil();
        $this->load->helper('file');
        $this->load->library('zip');
        $this->zip->clear_data();
        $this->zip->compression_level = 9; // Highest Compression Level
        $fy_code_np     = $fy_record->code_np;

        // $date  = tod

        echo PHP_EOL . "======================= CSV DATA DUMP - BS QUARTERLY REPORT ( ) =======================" . PHP_EOL .
            "START: " . date("Y-m-d H:i:s") . PHP_EOL . PHP_EOL .
            "Exporting Portfolio-wise CSVs..." . PHP_EOL;


        // SQL Options
        $delimiter = ",";
        $newline = "\r\n";
        $enclosure = '"';

        // CSV Files
        $csv_files = array();
        foreach ($sqls as $filename => $sql)
        {

            $query = $this->db->query($sql);


            // Get the Output
            $csv_data = $this->dbutil->csv_from_result($query, $delimiter, $newline, $enclosure);



            // File to write content to
            $csv_files[] = $csv_file = INSQUBE_MEDIA_PATH . '/reports/bs/' . $filename;

            // Write into individual csv files
            // We need these individual files for range query to merge them together
            if ( ! write_file($csv_file, $csv_data))
            {
                echo "ERROR: Unable to write the file ($filename)." . PHP_EOL;
            }
            else
            {
                // Add to archive
                $this->zip->read_file($csv_file);
                echo "SUCCESS: Data exported to CSV file ($filename)." . PHP_EOL;
            }
        }


        // Output Zip File
        $zip_file   = "bs-quarterly-{$fy_code_np}-{$fy_quarter}.zip";
        $csv_zip_file   = INSQUBE_MEDIA_PATH . '/reports/bs/' . $zip_file;

        // Remove any Zip file already
        @unlink($csv_zip_file);

        echo "Generating CSVs Zip ($zip_file)... " ;

        if( $this->zip->archive($csv_zip_file) )
        {
            // Delete Individual CSV files
            foreach($csv_files as $f)
            {
                @unlink($f);
            }

            /**
             * Save the Report for Downloads
             */
            $report_data = array(
                'type' 				=> $type,
                'fiscal_yr_id'  	=> $fy_record->id,
                'fy_quarter_month'  => $fy_quarter,
                'filename'  		=> $zip_file,
            );
            $this->bs_report_model->save($report_data);

            echo "OK" . PHP_EOL;
        }else{
            echo "FAIL" . PHP_EOL;
        }

        echo PHP_EOL . "END: " . date("Y-m-d H:i:s").PHP_EOL .
            "---------------------------------------------------------------" . PHP_EOL . PHP_EOL;
    }

}
