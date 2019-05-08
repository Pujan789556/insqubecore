
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Schema Change
 * After Implementing Audit Log - flag_complete column is not required
 *
 */
class Migration_Implement_audit_log_ac_crdnt_invoice_voucher extends CI_Migration {

    public function up()
    {
        $sqls = [

            // -- Triggers AC Credit Notes
            "DROP TRIGGER IF EXISTS `trg_ac_credit_notes_after_insert`;",
            "DROP TRIGGER IF EXISTS `trg_ac_credit_notes_after_update`;",

            // Triggers - Invoices
            "DROP TRIGGER IF EXISTS `trg_ac_invoices_after_insert`;",
            "DROP TRIGGER IF EXISTS `trg_ac_invoices_after_update`;",

            // Triggers - Vouchers
            "DROP TRIGGER IF EXISTS `trg_ac_vouchers_after_insert`;",
            "DROP TRIGGER IF EXISTS `trg_ac_vouchers_after_update`;",

            // Triggers - Receipts
            "DROP TRIGGER IF EXISTS `trg_ac_receipts_after_insert`;",
            "DROP TRIGGER IF EXISTS `trg_ac_receipts_after_update`;",

            // Delete Columns
            "ALTER TABLE `ac_credit_notes` DROP `flag_complete`;",
            "ALTER TABLE `ac_invoices` DROP `flag_complete`;",
            "ALTER TABLE `ac_vouchers` DROP `flag_complete`;",
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
            echo 'Successfully migrated.' . PHP_EOL;
        }
    }

    public function down()
    {

    }
}