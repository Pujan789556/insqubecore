
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Upgrade Portfolio Settings Schema
 *
 *  Add Column - ri_liability_options
 *
 */
class Migration_Upgrade_portfolio_setting_schema extends CI_Migration {

    public function up()
    {
        $sqls = [

            "ALTER TABLE `master_portfolio_settings` ADD `ri_liability_options` VARCHAR(200) NULL COMMENT 'Re-Insurance Liability Options : [SI|TPL|MAX LIABILITY]' AFTER `portfolio_id`;",
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