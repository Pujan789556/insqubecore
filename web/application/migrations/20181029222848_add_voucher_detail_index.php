
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Add index in Voucher Detail Table
 */
class Migration_Add_voucher_detail_index extends CI_Migration {

        public function up()
        {
            $sql = "ALTER TABLE `ac_voucher_details` ADD INDEX `idx_flag_type`(`flag_type`);";


			// Use automatic transaction
            $this->db->trans_start();

                echo "Running Migration up()... " . PHP_EOL .
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

        public function down()
        {
            $sql = "ALTER TABLE `ac_voucher_details` DROP INDEX `idx_flag_type`;";

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