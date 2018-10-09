<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube Command-Line Controller - Beema Samiti Report
 *
 * All the background Tasks or Command Line Jobs are Carried Out using this controller.
 */
class Cli_bs_report extends Base_Controller
{
    /**
     * Files Upload Path - Data (Beema Samiti Reports)
     */
    public static $data_upload_path = INSQUBE_DATA_ROOT . 'reports/bs/';

    // --------------------------------------------------------------------


	public function __construct()
    {
        // Call the CI_Model constructor
        parent::__construct();

        if(! is_cli() ){
        	show_404();
        	exit(1);
        }

        // Load models
        $this->load->model('bs_report_model');
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
     * Generate all beema samiti queued reports
     *
     *
     * Usage(dev/production):
     *      $ php index.php cli_bs_report generate
     *      $ php index.php cli_bs_report generate
     *
     *      $ CI_ENV=production php index.php cli_bs_report generate
     *      $ CI_ENV=production php index.php cli_bs_report generate
     *
     * @param inte $fiscal_yr_id
     * @param int $fy_quarter
     * @return void
     */
    public function generate()
    {
        $records = $this->bs_report_model->pending_list();

        /**
         * Generate Report Per Type
         */
        foreach($records as $record)
        {
            /**
             * Underwriting Reports
             */
            if( $record->category === IQB_BS_REPORT_CATEGORY_UW )
            {
                switch ($record->type)
                {
                    case IQB_BS_REPORT_TYPE_QUARTELRY:
                        $this->uw_quarterly( $record->id );
                        break;

                    case IQB_BS_REPORT_TYPE_MONTHLY:
                        $this->uw_monthly( $record->id );
                        break;

                    default:
                        # code...
                        break;
                }
            }
            else if( $record->category === IQB_BS_REPORT_CATEGORY_CL )
            {
                switch ($record->type)
                {
                    case IQB_BS_REPORT_TYPE_QUARTELRY:
                        $this->claim_quarterly( $record->id );
                        break;

                    case IQB_BS_REPORT_TYPE_MONTHLY:
                        // @TODO
                        break;

                    default:
                        # code...
                        break;
                }
            }
        }
    }

	// -------------------------------------------------------------------------------------

	/**
	 * Generate Quarterly Report for Underwriting
	 *
	 * @param inte $id
	 * @return void
	 */
	public function uw_quarterly( $id )
	{
        // Valid Record ?
        $id = (int)$id;
        $record = $this->bs_report_model->find($id);
        if(!$record )
        {
            echo "ERROR: Fiscal Year not found!" . PHP_EOL;
            exit(1);
        }

		/**
		 * Valid Fiscal Year?
		 */
		$fy_record = $this->fiscal_year_model->get($record->fiscal_yr_id);
		if(!$fy_record)
		{
			echo "ERROR: Fiscal Year not found!" . PHP_EOL;
			exit(1);
		}

		/**
		 * Fiscal Year, Quarter
		 */
        $fiscal_yr_id = $record->fiscal_yr_id;
        $fy_quarter = $record->fy_quarter_month;


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
        $this->_csv_export($record, $fy_record, $fy_quarter, $SQLS, IQB_BS_REPORT_TYPE_QUARTELRY);
	}

    // -------------------------------------------------------------------------------------

    /**
     * Generate Quarterly Report for Claim
     *
     * @param int $id
     * @return void
     */
    public function claim_quarterly( $id )
    {
        $this->load->model('bs_report_model');

        // Valid Record ?
        $id = (int)$id;
        $record = $this->bs_report_model->find($id);
        if(!$record )
        {
            echo "ERROR: Fiscal Year not found!" . PHP_EOL;
            exit(1);
        }

        /**
         * Valid Fiscal Year?
         */
        $fy_record = $this->fiscal_year_model->get($record->fiscal_yr_id);
        if(!$fy_record)
        {
            echo "ERROR: Fiscal Year not found!" . PHP_EOL;
            exit(1);
        }

        /**
         * Fiscal Year, Quarter
         */
        $fiscal_yr_id = $record->fiscal_yr_id;
        $fy_quarter = $record->fy_quarter_month;


        $fy_code_np     = $fy_record->code_np;
        $this->load->model('portfolio_model');
        $this->load->model('bsrs_heading_model');

        $portfolios             = $this->portfolio_model->dropdown_children();
        $bs_heading_formatted   = [];
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


            /**
             * CLAIM JOIN
             */
            $claim_status = IQB_CLAIM_STATUS_SETTLED;
            $heading_type_id = IQB_BSRS_HEADING_TYPE_ID_CLAIM;
            $claim_index        = count($hd_type_ids);
            $claim_inline_sql = "(
                                    SELECT
                                        CLM.id AS claim_id, CLM.policy_id, CLM.fiscal_yr_id, CLM.fy_quarter,
                                        CLM.settlement_claim_amount + CLM.total_surveyor_fee_amount AS total_claim_amount,
                                        CLM.settlement_claim_amount + CLM.total_surveyor_fee_amount - CLM.cl_treaty_retaintion AS total_ri_amount,
                                        CLM.total_surveyor_fee_amount AS total_surveyor_amount,
                                        P.district_id, H.id as bsrs_heading_id, H.code AS bsrs_code, H.name as bsrs_name
                                    FROM dt_claims AS CLM
                                    JOIN dt_policies P ON CLM.policy_id = P.id
                                    JOIN rel_claim__bsrs_heading REL ON REL.claim_id = CLM.id
                                    JOIN bsrs_headings H ON H.id = REL.bsrs_heading_id
                                    WHERE
                                        CLM.status = '{$claim_status}' AND
                                        P.portfolio_id = {$portfolio_id} AND
                                        H.heading_type_id = {$heading_type_id}
                                ) C0";

            $claim_join_on = [];
            for($i = 0; $i < count($hd_type_ids); $i++ )
            {
                $claim_join_on[] = "C0.policy_id = P{$i}.policy_id";
            }
            $select_sql[]           = "C0.bsrs_code AS bsrs_code{$claim_index}, C0.bsrs_name AS bsrs_name{$claim_index},
                                    C0.total_claim_amount, C0.total_ri_amount, C0.total_surveyor_amount";
            $from_join_sql[]        = "JOIN {$claim_inline_sql} ON " . implode(" AND ", $claim_join_on);
            $sql_group_headings[]   = "C0.bsrs_heading_id";



            $select_sql         = implode(', ', $select_sql);
            $from_join_sql      = implode(PHP_EOL, $from_join_sql);
            $sql_group_headings = implode(', ', $sql_group_headings);

            $SQL_TEST[]     = $select_sql . PHP_EOL . $from_join_sql;


            /**
             * CSV Name:
             *  BS-CLAIM-QUARTERLY-<fy_code_np>-<fy_quarter>-<portfolio_code>.csv
             */
            $filename = 'BS-CLAIM-QUARTERLY-' . $fy_code_np . '-' . $fy_quarter . '-' . $portfolio_codes[$portfolio_id] . '.csv';
            $SQLS[$filename] = "{$select_sql},
                            R.id AS region_id,
                            COUNT(C0.claim_id) AS claim_count
                        {$from_join_sql}
                        JOIN master_districts D ON P0.district_id = D.id
                        JOIN master_regions R ON D.region_id = R.id
                        WHERE
                            C0.fiscal_yr_id = {$fiscal_yr_id} AND
                            C0.fy_quarter = {$fy_quarter}
                        GROUP BY C0.claim_id, {$sql_group_headings};";
        }

        // echo '<pre>'; print_r($SQLS);exit;

        $this->_csv_export($record, $fy_record, $fy_quarter, $SQLS, IQB_BS_REPORT_TYPE_QUARTELRY);
    }

    // -------------------------------------------------------------------------------------

    /**
     * Generate Monthly for Underwriting
     *
     * NOTE: Only Agriculture Portfolios
     *
     * @param int $id
     * @return void
     */
    public function uw_monthly( $id )
    {
        // Valid Record ?
        $id = (int)$id;
        $record = $this->bs_report_model->find($id);
        if(!$record )
        {
            echo "ERROR: Fiscal Year not found!" . PHP_EOL;
            exit(1);
        }

        $this->load->model('bs_agro_category_model');
        $this->load->model('bs_agro_breed_model');
        $this->load->model('portfolio_setting_model');

        /**
         * Valid Fiscal Year?
         */
        $fy_record = $this->fiscal_year_model->get($record->fiscal_yr_id);
        if(!$fy_record)
        {
            echo "ERROR: Fiscal Year not found!" . PHP_EOL;
            exit(1);
        }
        $fiscal_yr_id = $record->fiscal_yr_id;


        $fy_month_record = $this->fy_month_model->get_by_fy_month($record->fiscal_yr_id, $record->fy_quarter_month);
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
        $this->load->model('address_model');
        $this->db->select(
                // Policy
                "P.id AS policy_id, P.portfolio_id, P.code AS policy_code, P.flag_dc, P.start_date, P.end_date, " .

                // Object
                "O.attributes AS object_attributes, " .

                // Customer
                "C.full_name AS customer_name, " .

                // Distrct
                "D.code as district_code, " .

                // Endorsement
                "E.net_amt_sum_insured AS amt_sum_insured"
            )
                // Installment
            ->select("PINST.amt_basic_premium + PINST.amt_pool_premium AS amt_total_premium", FALSE)
            ->from('dt_policies P')
            ->join('dt_objects O', "P.object_id = O.id")
            ->join('master_portfolio PF', 'P.portfolio_id = PF.id')
            ->join('dt_customers C', 'P.customer_id = C.id')
            ->join('master_districts D', 'P.district_id = D.id')
            ->join('master_regions R', 'D.region_id = R.id')
            ->join('dt_endorsements E', 'E.policy_id = P.id')
            ->join('dt_policy_installments PINST', 'PINST.endorsement_id = E.id');

            /**
             * Customer Address
             */
            $table_aliases = [
                // Address Table Alias
                'address' => 'ADRC',

                // Country Table Alias
                'country' => 'CNTRYC',

                // State Table Alias
                'state' => 'STATEC',

                // Local Body Table Alias
                'local_body' => 'LCLBDC',

                // Type/Module Table Alias
                'module' => 'C'
            ];
            $this->address_model->module_select(IQB_ADDRESS_TYPE_CUSTOMER, NULL, $table_aliases, 'addr_customer_');

            $params = [
                'P.fiscal_yr_id'            => $fiscal_yr_id,
                'PF.parent_id'              => IQB_MASTER_PORTFOLIO_AGR_ID,
                'PINST.installment_date >=' => $month_start,
                'PINST.installment_date <=' => $month_end
            ];

        /**
         * Get the Data
         */
        $data = $this->db->where($params)
                         ->get()
                         ->result();



        $bs_category_codes  = $this->bs_agro_category_model->dropdown_codes();
        $bs_breeds          = $this->bs_agro_breed_model->dropdown();

        $csv_data[] = ['NAME', 'DISTRICT', 'POLICY_NO', 'PARTICULARS', 'EFFECTIVE_FROM', 'EFFECTIVE_TO', 'SUMINSURED', 'PREMIUM', 'EXEMPTED_PREMIUM', 'AREA', 'CASTE', 'REMARKS', 'TAG_NO', 'CONTACTNO', 'ADDRESS'];

        foreach($data as $single)
        {
            load_portfolio_helper($single->portfolio_id);

            $attributes     = json_decode($single->object_attributes);
            $address_record = parse_address_record($single, 'addr_customer_');

            // $contact    = json_decode($single->customer_contact);
            $address    = strip_tags(address_widget_two_lines($address_record));
            $contact_no = $address_record->mobile ?? '';

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

            for($i=0; $i < count($attributes->items); $i++ )
            {
                $item = $attributes->items[$i];
                $sum_insured = $item->sum_insured;

                /**
                 * Tariff Record
                 */
                try {
                    $tariff = _OBJ_AGR_tariff_by_type($single->portfolio_id, $item->breed);

                } catch (Exception $e) {

                    die('ERROR: ' . $e->getMessage() . PHP_EOL);
                }

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

                $area       = $item->area ?? '';
                $caste      = $bs_breeds[$item->breed];
                $remarks    = '';
                $tag_no     = $item->tag_no ?? '';

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
        $csv_file_path   = self::$data_upload_path . $filename;


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
        $zip_file       = uniqid("bs-monthly-{$fy_code_np}-{$fy_month_record->name_en}-AGR", true). ".zip";
        $csv_zip_file   = self::$data_upload_path . $zip_file;

        // Remove any Zip file already
        @unlink($csv_zip_file);

        // Remove old record file
        if($record->filename)
        {
            @unlink(self::$data_upload_path . $record->filename);
        }


        echo "Generating CSVs Zip ($zip_file)... " ;

        if( $this->zip->archive($csv_zip_file) )
        {
            // Delete Individual CSV files
            @unlink($csv_file_path);

            /**
             * Save the Report for Downloads
             */
            $report_data = array(
                'filename'  => $zip_file,
                'status'    => IQB_FLAG_ON
            );
            $this->bs_report_model->update($record->id, $report_data, TRUE);

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
	private function _csv_export($record, $fy_record, $fy_quarter, $sqls, $type)
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
            $csv_files[] = $csv_file = self::$data_upload_path . $filename;

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
        $zip_file       = uniqid("bs-quarterly-{$fy_code_np}-{$fy_quarter}", true). ".zip";
        $csv_zip_file   = self::$data_upload_path . $zip_file;

        // Remove any Zip file already
        @unlink($csv_zip_file);

        // Remove old record file
        if($record->filename)
        {
            @unlink(self::$data_upload_path . $record->filename);
        }

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
                'filename'  => $zip_file,
                'status' => IQB_FLAG_ON
            );
            $this->bs_report_model->update($record->id, $report_data, TRUE);

            echo "OK" . PHP_EOL;
        }else{
            echo "FAIL" . PHP_EOL;
        }

        echo PHP_EOL . "END: " . date("Y-m-d H:i:s").PHP_EOL .
            "---------------------------------------------------------------" . PHP_EOL . PHP_EOL;
    }

}
