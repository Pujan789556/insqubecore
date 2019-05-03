
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Implement Audit Log on Treaties Table
 *
 */
class Migration_Implement_audit_log_treaties_part1 extends CI_Migration {

    public function up()
    {


        $sqls = [

            // -- Portfolio
            "DROP TRIGGER IF EXISTS `trg_master_portfolio_after_insert`;",
            "DROP TRIGGER IF EXISTS `trg_master_portfolio_after_update`;",
            "DROP TRIGGER IF EXISTS `trg_master_portfolio_after_delete`;",

            // -- Portfoliio Settings
            "DROP TRIGGER IF EXISTS `trg_master_portfolio_settings_after_insert`;",
            "DROP TRIGGER IF EXISTS `trg_master_portfolio_settings_after_update`;",
            "DROP TRIGGER IF EXISTS `trg_master_portfolio_settings_after_delete`;",

            // -- Endorsement Templates
            "DROP TRIGGER IF EXISTS `trg_master_endorsement_templates_after_insert`;",
            "DROP TRIGGER IF EXISTS `trg_master_endorsement_templates_after_update`;",
            "DROP TRIGGER IF EXISTS `trg_master_endorsement_templates_after_delete`;",


            // -- Tariff - Agriculture
            "DROP TRIGGER IF EXISTS `trg_master_tariff_agriculture_after_insert`;",
            "DROP TRIGGER IF EXISTS `trg_master_tariff_agriculture_after_update`;",

            // -- Tariff - MISC BB
            "DROP TRIGGER IF EXISTS `trg_master_tariff_misc_bb_after_insert`;",
            "DROP TRIGGER IF EXISTS `trg_master_tariff_misc_bb_after_update`;",

            // -- Tariff - MISC EPA
            "DROP TRIGGER IF EXISTS `trg_master_tariff_misc_epa_after_insert`;",
            "DROP TRIGGER IF EXISTS `trg_master_tariff_misc_epa_after_update`;",

            // -- Tariff - MOTOR
            "DROP TRIGGER IF EXISTS `trg_master_tariff_motor_after_insert`;",
            "DROP TRIGGER IF EXISTS `trg_master_tariff_motor_after_update`;",

            // -- Tariff - TMI Plans
            "DROP TRIGGER IF EXISTS `trg_master_tmi_plans_after_insert`;",
            "DROP TRIGGER IF EXISTS `trg_master_tmi_plans_after_update`;",

            // -- Treaty Types
            "DROP TRIGGER IF EXISTS `trg_ri_setup_treaty_types_after_insert`;",
            "DROP TRIGGER IF EXISTS `trg_ri_setup_treaty_types_after_update`;",
            "DROP TRIGGER IF EXISTS `trg_ri_setup_treaty_types_after_delete`;",

            // -- Treaties
            "DROP TRIGGER IF EXISTS `trg_ri_setup_treaties_after_insert`;",
            "DROP TRIGGER IF EXISTS `trg_ri_setup_treaties_after_update`;",
            "DROP TRIGGER IF EXISTS `trg_ri_setup_treaties_after_delete`;",


            // -- Treaty Table Restructured
            "ALTER TABLE `ri_setup_treaties` ADD `commission_scales` JSON NULL COMMENT 'Comission Scale: [{name:aaa,scale_min:bbb,scale_max:ccc,rate:ddd},...] ' AFTER `file`;",

            // -- Drop Commission Scale Table
            "DROP TABLE `ri_setup_commission_scale`;"
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