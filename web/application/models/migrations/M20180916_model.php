<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migration Model
 *
 * Tasks: Update District, VDC/Municipality Structure with Latest Data
 */
class M20180916_model extends MY_Model
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
        $this->districts();

        $this->local_bodies();
    }

    public function districts()
    {
        // Before Migration SQLs
        $before_sqls = [

            // Delete Local bodies table
            "DROP TABLE IF EXISTS `master_localbodies`;",

            // District Code Restructure
            "ALTER TABLE `master_districts` CHANGE `code` `code` INT(3) UNSIGNED NOT NULL COMMENT 'District Code';",

            // Increase Existing IDs
            "UPDATE `master_districts`
            SET
                `id`=`id`+300,
                `updated_at` = NOW(),
                `updated_by` = 1
            WHERE 1;",
        ];

        $after_sqls = [

            // Decrease District IDs to point new IDs on Policy Table
            "UPDATE `dt_policies`
            SET
                `district_id`=`district_id`-300,
                `updated_at` = NOW(),
                `updated_by` = 1
            WHERE 1;",

            // Delete Older Data
            "DELETE FROM `master_districts` WHERE `id` >= 300;",

            // Reset Autoincrement Value
            "ALTER TABLE `master_districts` AUTO_INCREMENT = 1;"
        ];

        // Extract Data
        $file = '../migrations/districts.xlsx';
        $data = excel_to_array($file);

        // [A] => S.N.
        // [B] => District
        // [C] => lhNnf
        // [D] => Code
        // [E] => Dist_ID
        // [F] => State_ID
        // [G] => REGION_ID

        // echo '<pre>'; print_r($data); exit;

        // Remove Header
        array_shift($data);


        $batch_data = [];
        foreach($data as $single)
        {
            $batch_data[] = [
                'id'        => $single['E'],
                'name_en'   => $single['B'],
                'name_np'   => $single['C'],
                'code'      => $single['D'],
                'state_id'  => $single['F'],
                'region_id' => $single['G'],
                'created_by'     => 1,
                'created_at'     => date('Y-m-d H:i:s')
            ];
        }

        // echo '<pre>'; print_r($batch_data);exit;


        // Use automatic transaction
        $this->db->trans_start();

            echo "PRE-IMPORT QUERIES". PHP_EOL;
            foreach ($before_sqls as $sql)
            {
                echo "QUERY: $sql ... ";
                echo $this->db->query($sql) ? "OK" : "FAIL";
                echo PHP_EOL;
            }

            // Bulk Import Districts
            echo "IMPORTING DISTRICTS ... ";
            echo $this->db->insert_batch('master_districts', $batch_data) ? "OK" : "FAIL";

            echo "POST-IMPORT QUERIES". PHP_EOL;
            foreach ($after_sqls as $sql)
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
            // Clear Portfolio Cache ( for risk related caches)
            echo "Successfully migrated." . PHP_EOL;
        }
    }

    public function local_bodies()
    {
        $file = '../migrations/m20180916.sql';
        $sqls = explode(';', file_get_contents($file));

        // remove empty array elements
        $sqls = array_filter($sqls);
        // echo '<pre>'; print_r($sqls); exit;


        // Use automatic transaction
        $this->db->trans_start();

            // Run Queries
            foreach ($sqls as $sql)
            {
                $sql = trim($sql);
                if($sql)
                {
                    echo "QUERY: $sql ... ";
                    echo $this->db->query($sql) ? "OK" : "FAIL";
                    echo PHP_EOL;
                }
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
            // Clear Portfolio Cache ( for risk related caches)
            echo "Successfully migrated." . PHP_EOL;
        }
    }
}