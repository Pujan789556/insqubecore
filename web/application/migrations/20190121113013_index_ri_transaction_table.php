
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Createinactive mobile app user from customer
 *
 */
class Migration_Index_ri_transaction_table extends CI_Migration
{

    public function up()
    {
        $sqls = [

            // Add index on flag_fac_registered on dt_ri_transactions table
            "ALTER TABLE `dt_ri_transactions` ADD INDEX `idx_flag_fac_registered` (`flag_fac_registered`);",

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