
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Implement Audit Log on User Table, Merged User settings to Users table
 *
 */
class Migration_Implement_audit_log_user extends CI_Migration {

    public function up()
    {
        $sqls = [

            "ALTER TABLE `auth_users` ADD `flag_re_login` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Requires Re-login ' AFTER `last_login`, ADD `flag_back_date` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Back Date Enable/Disable' AFTER `flag_re_login`;",


            "DROP TRIGGER IF EXISTS `trg_auth_users_after_insert`;",

            "DROP TABLE `auth_user_settings`;",

            "DROP TABLE `auth_user_profile`;"
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