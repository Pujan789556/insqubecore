
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Add Policy Short Term Related Fields on Endorsement Table
 */
class Migration_Add_spr_fields_on_endorsement extends CI_Migration {

        public function up()
        {
            $sql = "ALTER TABLE `dt_endorsements`
					ADD `flag_short_term` CHAR(1) NULL DEFAULT NULL COMMENT 'Is this short term policy?' AFTER `flag_refund_on_terminate`,
					ADD `short_term_config` TINYINT(1) NULL COMMENT 'Apply Short Term on 1: Both Basic and Pool Premium 2: Basic Only' AFTER `flag_short_term`,
					ADD `short_term_rate` DECIMAL(8,4) NULL COMMENT 'The Rate it applies on Annual Premium' AFTER `short_term_config`;";


			// Use automatic transaction
            $this->db->trans_start();

                echo "Running Migration up()... " . PHP_EOL .
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

        public function down()
        {
            $sql = "ALTER TABLE `dt_endorsements`
					  DROP `flag_short_term`,
					  DROP `short_term_config`,
					  DROP `short_term_rate`;";

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