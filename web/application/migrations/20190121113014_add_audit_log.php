
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Add Audit Log Table
 *
 */
class Migration_Add_audit_log extends CI_Migration {

        public function up()
        {
            $sqls = [

                // Table structure
                "CREATE TABLE `audit_logger` (
                  `id` bigint(20) UNSIGNED NOT NULL,
                  `table_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Table Name',
                  `table_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Primary Key of  supplied table',
                  `action` char(1) COLLATE utf8_unicode_ci NOT NULL COMMENT 'C: Create, U: Update, D: Delete',
                  `old_data` json DEFAULT NULL COMMENT 'Old Values - Only changed data in JSON format',
                  `new_data` json DEFAULT NULL COMMENT 'Updated/Changed Data - Only changed columns in JSON',
                  `user_id` int(11) UNSIGNED DEFAULT NULL COMMENT 'Who Made this Change?',
                  `action_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When it Happened?'
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Audit Log of Application';",


                // Indexes
                "ALTER TABLE `audit_logger`
                  ADD PRIMARY KEY (`id`),
                  ADD KEY `_fkc__audit_logger__auth_users` (`user_id`),
                  ADD KEY `idx_action_at` (`action_at`) USING BTREE,
                  ADD KEY `idx_action` (`action`) USING BTREE;",

                  // AUTO_INCREMENT for table
                "ALTER TABLE `audit_logger`
                  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;",


                // Constraints for table
                "ALTER TABLE `audit_logger`
                  ADD CONSTRAINT `_fkc__audit_logger__auth_users` FOREIGN KEY (`user_id`) REFERENCES `auth_users` (`id`) ON UPDATE CASCADE;"
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
            $sql = "DROP TABLE `audit_logger`;";

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