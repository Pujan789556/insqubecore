<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migration Model
 *
 * Tasks: Save Policy Schedule PDF on All active Policies
 */
class M20180822_model extends MY_Model
{

	/**
	 * Constructor
	 *
	 * @return void
	 */
    public function __construct()
    {
        parent::__construct();
    }

    public function migrate()
    {
        $this->update_company_branches();
    }

    /**
     * Migrate Company Branches
     *
     * Create a Headoffice flag on company branch
     *
     * @return void
     */
    public function company_branch()
    {
        $sql = "ALTER TABLE `master_company_branches` ADD `is_head_office` TINYINT(1) NOT NULL DEFAULT '0' AFTER `contact`, ADD INDEX `idx_head_office` (`is_head_office`);";

        // Use automatic transaction
        $this->db->trans_start();


            echo "QUERY: $sql ... ";
            echo $this->db->query($sql) ? "OK" : "FAIL";
            echo PHP_EOL;


        // Commit all transactions on success, rollback else
        $this->db->trans_complete();

        // Check Transaction Status
        if ($this->db->trans_status() === FALSE)
        {
            // incomplete message
            echo 'Could not migrate database.' . PHP_EOL;
        }
        else
        {
            echo "Successfully migrated." . PHP_EOL;
        }
    }

    /**
     * Update Company Branches
     * - create headoffice from company defautl contact
     * - delete contact after import
     * @return void
     */
    public function update_company_branches()
    {

        /**
         * Tasks:
         *      1. Create a Head Office Branch for all Companies which do not have one with it's Contact data
         *      2. Delete column "contact" from Company Table
         *      3. Clear Company and Company Branches Caches
         */

        $sql = "SELECT C.`id`,  C.`contact`, CB.is_head_office, CB.contact AS ho_contact
                FROM `master_companies` C
                LEFT JOIN `master_company_branches` CB ON CB.company_id = C.id AND CB.is_head_office = 1;";


        $rows = $this->db->query($sql)->result();

        $total = count($rows);
        $success = 0;
        $batch_data = [];

        $this->load->model('company_branch_model');
        $this->load->model('company_model');

        echo 'Updating database ... ' . PHP_EOL;

        // Use automatic transaction
        $this->db->trans_start();

            if($rows)
            {

                foreach ($rows as $row)
                {
                    if(!$row->is_head_office)
                    {
                        // Insert New Branch as Head office

                        // Create a New Head Office Contact
                        $batch_data[] = [
                            'company_id'     => $row->id,
                            'name'           => 'Head Office',
                            'is_head_office' => IQB_FLAG_ON,
                            'contact'        => $row->contact,
                            'created_by'     => 1,
                            'created_at'     => date('Y-m-d H:i:s')
                        ];

                    }
                }

                if($batch_data)
                {
                    $this->db->insert_batch('master_company_branches', $batch_data );
                }
            }

            // Remove Columns
            $sql = "ALTER TABLE `master_companies` DROP `contact`;";
            echo "SQL: {$sql} ... ";
            echo $this->db->query($sql) ? 'OK' . PHP_EOL : 'FAIL' . PHP_EOL;



        // Commit all transactions on success, rollback else
        $this->db->trans_complete();

        // Check Transaction Status
        if ($this->db->trans_status() === FALSE)
        {
            // incomplete message
            echo 'Rollback to previous state. Could not migrate database.' . PHP_EOL;
        }
        else
        {
            echo 'Clearing cache...';
            $this->company_model->clear_cache();
            $this->company_branch_model->clear_cache();

            echo 'OK'. PHP_EOL;

            $success = count($batch_data);
            echo "{$success} out of {$total} successfully migrated." . PHP_EOL;
        }
    }

    public function generate_policy_pdfs()
    {

        /**
         * Task 1: Get all Active Policy IDs
         */
        $list = $this->db->select('id')
                         ->from('dt_policies')
                         ->where('status', IQB_POLICY_STATUS_ACTIVE)
                         ->get()
                         ->result();


        echo "Total records identified: " . count($list) . PHP_EOL;

        $this->load->model('policy_model');
        $this->load->model('endorsement_model');
        $this->load->helper('policy');

        $success = 0;
        foreach($list as $single)
        {
            $record  = $this->policy_model->get($single->id);

            if( !_POLICY__schedule_exists($record->code) )
            {
                /**
                 * Save PDF & Render on Browser
                 */
                load_portfolio_helper($record->portfolio_id);
                $schedule_view  = _POLICY__get_schedule_view($record->portfolio_id);
                if(!$schedule_view)
                {
                    return $this->template->json([
                        'status' => 'error',
                        'message' => "No schedule view exists for given portfolio({$record->portfolio_name})."
                    ], 404);
                }
                try {

                    $endorsement_record = $this->endorsement_model->get_first( $record->id);

                } catch (Exception $e) {

                    return $this->template->json([
                        'status' => 'error',
                        'message' => $e->getMessage()
                    ], 404);
                }

                /**
                 * Generate Dynamic HTML for Schedule
                 */
                $data = [
                    'record'                => $record,
                    'endorsement_record'    => $endorsement_record
                ];
                $html = $this->load->view( $schedule_view, $data, TRUE);

                try {

                    // Save PDF
                    _POLICY__schedule_pdf( $record, 'save', $html );

                     if( _POLICY__schedule_exists($record->code) )
                     {
                        $success++;
                        echo "ID : {$record->id} :: Schedule generated - SUCCESS" . PHP_EOL;
                     }
                     else
                     {
                        echo "ID : {$record->id} :: Could not save PDF - FAIL" . PHP_EOL;
                     }

                }
                catch (Exception $e) {

                    echo "ID : {$record->id} :: EXCEPTION :: " . $e->getMessage() . PHP_EOL;
                }
            }
            else
            {
                echo "ID : {$record->id} :: Schedule Already Exists." . PHP_EOL;
            }
        }

        echo "Successfully migrated records: {$success}." . PHP_EOL;


        /**
         * Update Database
         */
        echo "Migrating Database..." . PHP_EOL;
        $sqls = [
            'ALTER TABLE `dt_policies` DROP `schedule_html`;',
            'OPTIMIZE TABLE `dt_policies`;'
        ];


        // Use automatic transaction
        $this->db->trans_start();


            foreach ($sqls as $sql)
            {
                echo "QUERY: $sql ... ";
                echo $this->db->query($sql) ? "OK" : "FAIL";
                echo PHP_EOL;
            }


        // Commit all transactions on success, rollback else
        $this->db->trans_complete();

        // Check Transaction Status
        if ($this->db->trans_status() === FALSE)
        {
            // incomplete message
            echo 'Could not migrate database.' . PHP_EOL;
        }
        else
        {
            echo "Successfully migrated records: {$success}." . PHP_EOL;
        }

        echo "Successfully migrated records: {$success}." . PHP_EOL;
    }
}