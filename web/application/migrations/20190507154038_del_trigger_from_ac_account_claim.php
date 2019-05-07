
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Delete Triggers from AC Accounts and Claim
 *
 */
class Migration_Del_trigger_from_ac_account_claim extends CI_Migration {

    public function up()
    {
        $sqls = [

            // -- Account Triggers
            "DROP TRIGGER IF EXISTS `trg_ac_accounts_after_insert`;",
            "DROP TRIGGER IF EXISTS `trg_ac_accounts_after_update`;",
            "DROP TRIGGER IF EXISTS `trg_ac_accounts_after_delete`;",

            // -- Claim Triggers
            "DROP TRIGGER IF EXISTS `trg_dt_claims_after_insert`;",
            "DROP TRIGGER IF EXISTS `trg_dt_claims_after_update`;",
            "DROP TRIGGER IF EXISTS `trg_dt_claims_after_delete`;",
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
            echo "Successfully migrated." . PHP_EOL;
        }
    }

    public function down()
    {

    }
}