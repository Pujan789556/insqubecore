
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Upgrade RI Treaty Portfolio Schema - column size upgrade
 * qs_quota_percent
 * qs_retention_percent
 *
 */
class Migration_Upgrade_ri_treaty_portfolio extends CI_Migration {

    public function up()
    {
        $sqls = [

            "ALTER TABLE `ri_setup_treaty_portfolios` CHANGE `qs_retention_percent` `qs_retention_percent` DECIMAL(8,3) UNSIGNED NULL DEFAULT NULL COMMENT 'Quota, Quota & Surplus: Retention Percentage', CHANGE `qs_quota_percent` `qs_quota_percent` DECIMAL(8,3) UNSIGNED NULL DEFAULT NULL COMMENT 'Quota, Quota & Surplus: Distribution Percentage';",
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
            echo 'Successfully migrated.' . PHP_EOL;
        }
    }

    public function down()
    {

    }
}