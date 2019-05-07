
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * RI Schema Upgraded, Audit Log Implemented
 *
 */
class Migration_Ri_schema_upgrade_audit_log_integrated extends CI_Migration {

    public function up()
    {
        $sqls = [

            // Add flag_has_fac column
            "ALTER TABLE `dt_ri_transactions` ADD `flag_has_fac` TINYINT(1) NOT NULL COMMENT 'Has FAC on this transaction?' AFTER `commission_fac`;",

            // Create Index
            "CREATE  INDEX `idx_flag_has_fac` ON `dt_ri_transactions`(`flag_has_fac`);",


            // -- Triggers RI Transactions
            "DROP TRIGGER IF EXISTS `trg_dt_ri_transactions_after_insert`;",
            "DROP TRIGGER IF EXISTS `trg_dt_ri_transactions_after_update`;",
            "DROP TRIGGER IF EXISTS `trg_dt_ri_transactions_after_delete`;",

            // Empty RI Transaction Table
            "DELETE FROM `dt_ri_transactions` WHERE 1;",
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
            // Re-build RI-Transaction
            echo "Re-building RI-Distribution..." . PHP_EOL;
            system("php index.php cli ri_rebuild");

            echo "Successfully migrated." . PHP_EOL;
        }
    }

    public function down()
    {

    }
}