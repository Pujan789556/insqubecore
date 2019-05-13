
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Upgrade RI Treaty Portfolio Schema
 *
 *  Add Column - treaty_distribution_basis
 *
 */
class Migration_Upgrade_ri_treaty_portfolio_v2 extends CI_Migration {

    public function up()
    {
        $sqls = [

            "ALTER TABLE `ri_setup_treaty_portfolios` ADD `treaty_distribution_basis` TINYINT(1) NULL COMMENT 'Treaty Distribution Basis: 1 - SI, 2: Premium' AFTER `ac_basic`;",
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