
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Upgrade Treaty Portfolio Schema
 *
 *  Update Column - treaty_distribution_basis to treaty_distribution_for
 *
 */
class Migration_Upgrade_treaty_portfolio_schema_index extends CI_Migration {

    public function up()
    {
        $sqls = [
            // -- Remove Primary Key, Add as a Index
            "ALTER TABLE `ri_setup_treaty_portfolios` DROP PRIMARY KEY, ADD INDEX (`treaty_id`, `portfolio_id`) USING BTREE;",

            // -- Drop FCK
            "ALTER TABLE `ri_setup_treaty_portfolios` DROP FOREIGN KEY __fck__ri_setup_treaty__ri_setup_treaty_portfolio;",

            // -- Drop Index holding treaty_id and portfolio_id
            "ALTER TABLE `ri_setup_treaty_portfolios` DROP INDEX `treaty_id`;",

            // -- Add FCK Back
            "ALTER TABLE `ri_setup_treaty_portfolios` ADD CONSTRAINT `__fck__ri_setup_treaty__ri_setup_treaty_portfolio` FOREIGN KEY (`treaty_id`) REFERENCES `ri_setup_treaties`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;",

            // -- Add Index for treaty distribution for column
            "ALTER TABLE `ri_setup_treaty_portfolios` ADD INDEX(`treaty_distribution_for`);"
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