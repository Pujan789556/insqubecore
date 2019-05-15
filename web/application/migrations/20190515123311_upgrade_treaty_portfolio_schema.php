
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Upgrade Treaty Portfolio Schema
 *
 *  Update Column - treaty_distribution_basis to treaty_distribution_for
 *
 */
class Migration_Upgrade_treaty_portfolio_schema extends CI_Migration {

    public function up()
    {
        $sqls = [

            "ALTER TABLE `ri_setup_treaty_portfolios` CHANGE `treaty_distribution_basis` `treaty_distribution_for` TINYINT(1) NULL DEFAULT NULL COMMENT 'Treaty Distribution For: 1 - SI, 2: Max Liability, 3: Third Party Liability';",
        ];

        // Use automatic transaction
        $this->db->trans_start();
            print "Running Migration up()... \n\r";
            foreach($sqls as $sql)
            {
                print "EXECUTING QUERY: $sql" . "\n\r" . "QUERY STATUS: ";
                print $this->db->query($sql) ? "OK" : "FAIL";
                print "\n\r";
            }

        // Commit all transactions on success, rollback else
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            print "Could not migrate database.\n\r";
        }
        else
        {

            print "Successfully migrated.\n\r";
        }
    }

    public function down()
    {

    }
}