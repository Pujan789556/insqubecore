
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Add Audit Log Table
 *
 */
class Migration_Update_audit_logger_schema extends CI_Migration {

        public function up()
        {
            $sql = "ALTER TABLE `audit_logger` ADD `table_reference` JSON NULL COMMENT 'In Case of Slave Table - Composite Key in JSON Format' AFTER `table_id`;";

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

            $sql = "ALTER TABLE `audit_logger` DROP `table_reference`;";

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