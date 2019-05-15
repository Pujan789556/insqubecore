
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Upgrade Endorsement Schema
 *
 *  Create New table for premiums - si, third party liability, Maximum Liability
 *  Copy old data into new table
 *  Delete these unwanted columns, and add new columns to stor si, tpl, max liability
 *
 */
class Migration_Upgrade_endorsement_schema extends CI_Migration {

    public function up()
    {
        // Use automatic transaction
        $this->db->trans_start();
            print "Running Migration up()... \n\r";

            print "Creating New Table - 'dt_premiums'... \n\r";
            $this->_create_premium_table();

            print "Copying data from Endorsement table to Premium Table... \n\r";
            $this->_copy_data_to_premium_table();

            print "Upgrading Endorsement Table - 'dt_endorsements'... \n\r";
            $this->_upgrade_endorsement_table();

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

    private function _create_premium_table()
    {
        $sqls = [

            // Drop If already Exists
            "DROP TABLE IF EXISTS `dt_premiums`;",

            // Table structure for table `dt_premiums`
            "CREATE TABLE `dt_premiums` (
              `id` bigint(20) UNSIGNED NOT NULL,
              `endorsement_id` bigint(20) UNSIGNED NOT NULL,
              `premium_for` tinyint(1) DEFAULT NULL COMMENT '1: Sum Insured, 2: Max Liability, 3: Third Party Liability',
              `gross_full_amt_basic_premium` decimal(20,4) DEFAULT NULL COMMENT 'GROSS FULL - Basic',
              `gross_full_amt_pool_premium` decimal(20,4) DEFAULT NULL COMMENT 'GROSS FULL - Pool',
              `gross_full_amt_commissionable` decimal(20,4) DEFAULT NULL COMMENT 'GROSS FULL - Commissionable',
              `gross_full_amt_agent_commission` decimal(20,4) DEFAULT NULL COMMENT 'GROSS FULL - Agent Commission',
              `gross_full_amt_ri_commission` decimal(20,4) DEFAULT NULL COMMENT 'GROSS FULL - RI Commission',
              `gross_full_amt_direct_discount` decimal(20,4) DEFAULT NULL COMMENT 'GROSS FULL - Direct Discount',
              `gross_computed_amt_basic_premium` decimal(20,4) DEFAULT NULL COMMENT 'Gross Computed Basic - From Latest Object SI after applying compute reference',
              `gross_computed_amt_pool_premium` decimal(20,4) DEFAULT NULL COMMENT 'Gross Computed Pool - From Latest Object SI after applying compute reference',
              `gross_computed_amt_commissionable` decimal(20,4) DEFAULT NULL COMMENT 'GROSS Computed- commissionable amount',
              `gross_computed_amt_agent_commission` decimal(20,4) DEFAULT NULL COMMENT 'GROSS Computed - agent commission',
              `gross_computed_amt_ri_commission` decimal(20,4) DEFAULT NULL COMMENT 'GROSS Computed - ri commission',
              `gross_computed_amt_direct_discount` decimal(20,4) DEFAULT NULL COMMENT 'GROSS Computed - Direct Discount',
              `refund_amt_basic_premium` decimal(20,4) DEFAULT NULL COMMENT 'Refund Basic - From Previous Endorsement after applying compute reference',
              `refund_amt_pool_premium` decimal(20,4) DEFAULT NULL COMMENT 'Refund Pool - From Previous Endorsement after applying compute reference',
              `refund_amt_commissionable` decimal(10,0) DEFAULT NULL COMMENT 'REFUND - commissionable Amount',
              `refund_amt_agent_commission` decimal(10,0) DEFAULT NULL COMMENT 'REFUND - agent commission',
              `refund_amt_ri_commission` decimal(10,0) DEFAULT NULL COMMENT 'REFUND - RI Commission',
              `refund_amt_direct_discount` decimal(20,4) DEFAULT NULL COMMENT 'REFUND - Direct Discount',
              `net_amt_basic_premium` decimal(20,4) DEFAULT NULL COMMENT 'NET: Basic Premium (Without Pool)',
              `net_amt_pool_premium` decimal(20,4) DEFAULT NULL COMMENT 'NET: Pool Premium Amount if Pool Risk is covered in this policy',
              `net_amt_commissionable` decimal(20,4) DEFAULT NULL COMMENT 'NET: Commissionable Amount (If this policy has agent and the selected policy premium is commissionable)',
              `net_amt_agent_commission` decimal(20,4) DEFAULT NULL COMMENT 'NET: Agent Commission Amount',
              `net_amt_ri_commission` decimal(20,4) DEFAULT NULL COMMENT 'NET: RI Commission Amount to Insurance company if Policy category is FAC-Inward',
              `net_amt_direct_discount` decimal(20,4) DEFAULT NULL COMMENT 'NET: Amount - Direct Discount',
              `created_at` datetime NOT NULL,
              `created_by` int(11) UNSIGNED NOT NULL,
              `updated_at` datetime DEFAULT NULL,
              `updated_by` int(11) UNSIGNED DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Endorsement Premiums based on SI, TPL, Max Liability' ROW_FORMAT=DYNAMIC;",

            // Indexes for table `dt_premiums`
            "ALTER TABLE `dt_premiums`
                ADD PRIMARY KEY (`id`),
                ADD KEY `__fkc__premium__endorsement` (`endorsement_id`),
                ADD KEY `idx_premium_for` (`premium_for`) USING BTREE;",


            // AUTO_INCREMENT for table `dt_premiums`
            "ALTER TABLE `dt_premiums`
                MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;",

            // Constraints for table `dt_premiums`
            "ALTER TABLE `dt_premiums`
                ADD CONSTRAINT `__fkc__premium__endorsement` FOREIGN KEY (`endorsement_id`) REFERENCES `dt_endorsements` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;",
        ];

        // Executing queries
        foreach($sqls as $sql)
        {
            print "EXECUTING QUERY: $sql" . "\n\r" . "QUERY STATUS: ";
            print $this->db->query($sql) ? "OK" : "FAIL";
            print "\n\r";
        }
    }

    private function _copy_data_to_premium_table()
    {
        $rows = $this->db->select('E.*')
                         ->from('dt_endorsements E')
                         ->get()->result();

        $data_cols = [
            'gross_full_amt_basic_premium',
            'gross_full_amt_pool_premium',
            'gross_full_amt_commissionable',
            'gross_full_amt_agent_commission',
            'gross_full_amt_ri_commission',
            'gross_full_amt_direct_discount',
            'gross_computed_amt_basic_premium',
            'gross_computed_amt_pool_premium',
            'gross_computed_amt_commissionable',
            'gross_computed_amt_agent_commission',
            'gross_computed_amt_ri_commission',
            'gross_computed_amt_direct_discount',
            'refund_amt_basic_premium',
            'refund_amt_pool_premium',
            'refund_amt_commissionable',
            'refund_amt_agent_commission',
            'refund_amt_ri_commission',
            'refund_amt_direct_discount',
            'net_amt_basic_premium',
            'net_amt_pool_premium',
            'net_amt_commissionable',
            'net_amt_agent_commission',
            'net_amt_ri_commission',
            'net_amt_direct_discount',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by'
        ];

        if($rows)
        {
            $index = 0;
            $batch_data = [];
            foreach($rows as $row)
            {
                $single = [];
                foreach($data_cols as $col)
                {
                    $single[$col] = $row->$col;
                }
                $single['premium_for']    = IQB_PORTFOLIO_LIABILITY_OPTION_SI;
                $single['endorsement_id'] = $row->id;


                $batch_data[] = $single;
                $index++;

                // Insert Every 100 Records
                if($index % 100 == 0)
                {
                    $this->db->insert_batch('dt_premiums', $batch_data);
                    $batch_data = []; // empty batch data
                }
            }
        }
    }

    private function _upgrade_endorsement_table()
    {
        $sqls = [

            // -- add columns
            "ALTER TABLE `dt_endorsements` ADD `amt_max_liability_object` DECIMAL(20,4) NULL COMMENT 'Max Liability of Object which is used for Premium Computation' AFTER `amt_sum_insured_net`, ADD `amt_max_liability_net` DECIMAL(20,4) NULL COMMENT 'RI distributed Max Liability for this Endorsement - NET Max Liability' AFTER `amt_max_liability_object`, ADD `amt_third_party_liability_object` DECIMAL(20,4) NULL COMMENT 'Third Party Liability of Object which is used for Premium Computation' AFTER `amt_max_liability_net`, ADD `amt_third_party_liability_net` DECIMAL(20,4) NULL COMMENT 'RI distributed Third Party Liability for this Endorsement - NET Third Party Liability' AFTER `amt_third_party_liability_object`;",

            // -- Drop Unwanted Columns
            "ALTER TABLE `dt_endorsements`
                DROP `gross_full_amt_basic_premium`,
                DROP `gross_full_amt_pool_premium`,
                DROP `gross_full_amt_commissionable`,
                DROP `gross_full_amt_agent_commission`,
                DROP `gross_full_amt_ri_commission`,
                DROP `gross_full_amt_direct_discount`,
                DROP `gross_computed_amt_basic_premium`,
                DROP `gross_computed_amt_pool_premium`,
                DROP `gross_computed_amt_commissionable`,
                DROP `gross_computed_amt_agent_commission`,
                DROP `gross_computed_amt_ri_commission`,
                DROP `gross_computed_amt_direct_discount`,
                DROP `refund_amt_basic_premium`,
                DROP `refund_amt_pool_premium`,
                DROP `refund_amt_commissionable`,
                DROP `refund_amt_agent_commission`,
                DROP `refund_amt_ri_commission`,
                DROP `refund_amt_direct_discount`,
                DROP `net_amt_basic_premium`,
                DROP `net_amt_pool_premium`,
                DROP `net_amt_commissionable`,
                DROP `net_amt_agent_commission`,
                DROP `net_amt_ri_commission`,
                DROP `net_amt_direct_discount`;"
        ];

        // Executing queries
        foreach($sqls as $sql)
        {
            print "EXECUTING QUERY: $sql" . "\n\r" . "QUERY STATUS: ";
            print $this->db->query($sql) ? "OK" : "FAIL";
            print "\n\r";
        }
    }

    public function down()
    {

    }
}