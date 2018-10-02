
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Upgrade Portfolio
 *
 *  - Add active column
 *  - Portfolio now have enable/disable features
 *  - Only active portfolios are available while adding policy
 */
class Migration_Upgrade_portfolio extends CI_Migration {

        public function up()
        {
            $sqls = [
                "ALTER TABLE `master_portfolio` ADD `active` TINYINT(1) NOT NULL DEFAULT '0' AFTER `account_id_cr`;",


                // Default - Activate all the portfolios
                "UPDATE `master_portfolio`
                SET
                    `active` = '1',
                    `updated_at` = NOW(),
                    `updated_by` = 1
                WHERE 1;"
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
            $sql = "ALTER TABLE `master_portfolio` DROP `active`;";

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