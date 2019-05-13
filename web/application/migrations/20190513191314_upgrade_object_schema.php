
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Upgrade Object Schema
 *
 *  Add Column - amt_max_liability, amt_third_party_liability
 *
 */
class Migration_Upgrade_object_schema extends CI_Migration {

    public function up()
    {
        $sqls = [

            "ALTER TABLE `dt_objects` ADD `amt_max_liability` DECIMAL(20,4) NULL COMMENT 'Maximum Liability Amount For this Object if SI is Zero' AFTER `amt_sum_insured`, ADD `amt_third_party_liability` DECIMAL(20,4) NULL COMMENT 'Third Party Libility Amount for this Object' AFTER `amt_max_liability`;",
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