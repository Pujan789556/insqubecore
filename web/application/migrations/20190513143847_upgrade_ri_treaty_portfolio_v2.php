
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
            print "Running Migration up()... \n\r";
            foreach($sqls as $sql)
            {
                print "EXECUTING QUERY: \n\r\t" . "$sql" . "\n\r" . "QUERY STATUS: ";
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
            // Clear Cache
            $this->load->model('ri_setup_treaty_model');
            $this->ri_setup_treaty_model->clear_cache();

            print "Successfully migrated.\n\r";
        }
    }

    public function down()
    {

    }
}