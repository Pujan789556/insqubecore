
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Update Opening Balance Table
 *  - add columns : Party Type, Party ID
 *  - add index on added column
 *
 */
class Migration_Update_opening_balance extends CI_Migration {

        public function up()
        {
            $sqls = [

                // Add Columns
                "ALTER TABLE `ac_opening_balances` ADD `party_type` CHAR(1) NULL AFTER `fiscal_yr_id`, ADD `party_id` INT(11) UNSIGNED NULL AFTER `party_type`;",


                // Add Index
                "ALTER TABLE `ac_opening_balances` ADD INDEX `idx_party`( `party_type`, `party_id`);"
            ];

			// Use automatic transaction
            $this->db->trans_start();
                echo "Running Migration up()... " . PHP_EOL;
                foreach($sqls as $sql)
                {
                    echo "QUERY: $sql ... ";
                    echo $this->db->query($sql) ? "OK" : "FAIL";
                    echo PHP_EOL;
                }

            // Commit all transactions on success, rollback else
            $this->db->trans_complete();
            if ($this->db->trans_status() === FALSE)
            {
                echo 'Could not migrate database.' . PHP_EOL;
            }
            else
            {
                echo "uccessfully Migrated." . PHP_EOL;
            }

        }

        public function down()
        {
            $sql = "ALTER TABLE `ac_opening_balances`
                      DROP `party_type`,
                      DROP `party_id`;";

		  	// Use automatic transaction
            $this->db->trans_start();

                echo "Running Migration down()... " . PHP_EOL .
                	 "QUERY: $sql ... ";

            	echo $this->db->query($sql) ? "OK" : "FAIL";

                echo PHP_EOL;

            // Commit all transactions on success, rollback else
            $this->db->trans_complete();
            if ($this->db->trans_status() === FALSE)
            {
                echo 'Could not migrate database.' . PHP_EOL;
            }
            else
            {
                echo "uccessfully Migrated." . PHP_EOL;
            }
        }
}