
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Add Opening Balance Table
 *
 */
class Migration_Add_opening_balance extends CI_Migration {

        public function up()
        {
            $sqls = [

                // Table structure for table `ac_opening_balances`
                "CREATE TABLE `ac_opening_balances` (
                  `id` bigint(20) UNSIGNED NOT NULL,
                  `account_id` int(11) UNSIGNED NOT NULL,
                  `fiscal_yr_id` int(3) UNSIGNED NOT NULL,
                  `dr` decimal(24,4) DEFAULT NULL,
                  `cr` decimal(24,4) DEFAULT NULL,
                  `balance` decimal(24,4) DEFAULT NULL,
                  `created_at` datetime NOT NULL,
                  `created_by` int(11) UNSIGNED NOT NULL,
                  `updated_at` datetime DEFAULT NULL,
                  `updated_by` int(11) UNSIGNED DEFAULT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;",


                // Indexes for table `ac_opening_balances`
                "ALTER TABLE `ac_opening_balances`
                  ADD PRIMARY KEY (`id`),
                  ADD KEY `__fck__account__opening_balance` (`account_id`),
                  ADD KEY `__fck__fiscal_yr__opening_balance` (`fiscal_yr_id`);",

                  // AUTO_INCREMENT for table `ac_opening_balances`
                "ALTER TABLE `ac_opening_balances`
                    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;",


                // Constraints for table `ac_opening_balances`
                "ALTER TABLE `ac_opening_balances`
                  ADD CONSTRAINT `__fck__account__opening_balance` FOREIGN KEY (`account_id`) REFERENCES `ac_accounts` (`id`) ON UPDATE CASCADE,
                  ADD CONSTRAINT `__fck__fiscal_yr__opening_balance` FOREIGN KEY (`fiscal_yr_id`) REFERENCES `master_fiscal_yrs` (`id`) ON UPDATE CASCADE;"
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
            $sql = "DROP TABLE `ac_opening_balances`;";

		  	// Use automatic transaction
            $this->db->trans_start();

                echo "Running Migration down()... " . PHP_EOL .
                	 "QUERY: $sql ... ";

            	echo $this->db->query($sql) ? "OK" : "FAIL";

                echo PHP_EOL;

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
}