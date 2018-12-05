<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migration Model
 *
 * Tasks: Add Fiscal Year Quarter, Settlement Date Column on Claim
 */
class M20180507_model extends MY_Model
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
            "ALTER TABLE `dt_claims` ADD `fy_quarter` TINYINT(1) NULL AFTER `fiscal_yr_id`;",
            "ALTER TABLE `dt_claims` CHANGE `fiscal_yr_id` `fiscal_yr_id` INT(3) UNSIGNED NULL DEFAULT NULL;",
            "ALTER TABLE `dt_claims` ADD `settlement_date` DATE NULL AFTER `flag_surveyor_voucher`;"
        ];
        // Use automatic transaction
        $this->db->trans_start();

            foreach($sqls as $sql)
            {
                $this->db->query($sql);
            }

        // Commit all transactions on success, rollback else
        $this->db->trans_complete();

        // Check Transaction Status
        if ($this->db->trans_status() === FALSE)
        {
            // incomplete message
            die('Could not migrate.' . PHP_EOL );
        }
        else
        {
            die('Successfully migrated' . PHP_EOL );
        }
    }

    // ----------------------------------------------------------------
}