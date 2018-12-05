<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migration Model
 *
 * Tasks: Update State Module
 *  - added country relation
 *  - upgraded to a full module having CRUD operations
 */
class M20180917_model extends MY_Model
{

	/**
	 * Constructor
	 *
	 * @return void
	 */
    public function __construct()
    {
        parent::__construct();
    }


    public function migrate()
    {
        $sqls = [
                "ALTER TABLE `master_states` ADD `country_id` INT(3) UNSIGNED NOT NULL AFTER `id`;",

                // For Existing Nepal's States
                "UPDATE `master_states` SET `country_id`=152 WHERE `id` <= 7;",

                "ALTER TABLE `master_states` ADD CONSTRAINT `__fkc__state__country` FOREIGN KEY (`country_id`) REFERENCES `master_countries`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE;",

                "ALTER TABLE `master_states` CHANGE `code` `code` VARCHAR(3) NOT NULL COMMENT 'State/Province Code';",

                "ALTER TABLE `master_states` CHANGE `name_en` `name_en` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, CHANGE `name_np` `name_np` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;",

                "ALTER TABLE `master_states` ADD INDEX(`code`);"
        ];

        // Use automatic transaction
        $this->db->trans_start();

            // Run Queries
            foreach ($sqls as $sql)
            {
                echo "QUERY: $sql ... ";
                echo $this->db->query($sql) ? "OK" : "FAIL";
                echo PHP_EOL;
            }

        // Commit all transactions on success, rollback else
        $this->db->trans_complete();

        // Check Transaction Status
        if ($this->db->trans_status() === FALSE)
        {
            // incomplete message
            echo 'Could not migrate database.' . PHP_EOL;
        }
        else
        {
            // Clear Portfolio Cache ( for risk related caches)
            echo "Successfully migrated." . PHP_EOL;
        }
    }
}