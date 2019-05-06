
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Delete Triggers from Object and Customer Table
 *
 */
class Migration_Del_trigger_from_object_and_customer extends CI_Migration {

    public function up()
    {
        $sqls = [

            // -- Customer Triggers
            "DROP TRIGGER IF EXISTS `trg_dt_customers_after_insert`;",
            "DROP TRIGGER IF EXISTS `trg_dt_customers_after_update`;",
            "DROP TRIGGER IF EXISTS `trg_dt_customers_after_delete`;",

            // -- Object Triggers
            "DROP TRIGGER IF EXISTS `trg_dt_objects_after_insert`;",
            "DROP TRIGGER IF EXISTS `trg_dt_objects_after_update`;",
            "DROP TRIGGER IF EXISTS `trg_dt_objects_after_delete`;"
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


    }
}