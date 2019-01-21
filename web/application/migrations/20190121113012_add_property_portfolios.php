
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Createinactive mobile app user from customer
 *
 */
class Migration_Add_property_portfolios extends CI_Migration
{

    public function up()
    {
        $sqls = [

            // Rename Fire Portfolio
            "UPDATE `master_portfolio` SET `parent_id`=NULL, `code`='PROPERTY', `name_en`='Property', `name_np`='सम्पत्ति', `updated_at`=NOW(), `updated_by`=1 WHERE `id` = 4;",

            // HOUSE
            "UPDATE `master_portfolio` SET `parent_id`=4, `code`='HOUSE', `name_en`='House', `name_np`='घर', `updated_at`=NOW(), `updated_by`=1 WHERE `id` = 401;",

            // GENERAL
            "UPDATE `master_portfolio` SET `parent_id`=4, `code`='GNRLPRPT', `name_en`='General Property', `name_np`='सामान्य सम्पत्ति', `updated_at`=NOW(), `updated_by`=1 WHERE `id` = 402;",


            // Short Term
            "UPDATE `master_portfolio` SET `parent_id`=4, `code`='STPRPT', `name_en`='Short Term Property', `name_np`='छोटो अवधिको सम्पत्ति', `updated_at`=NOW(), `updated_by`=1 WHERE `id` = 403;",

            // DELETE if already exists
            // "DELETE FROM `master_portfolio` WHERE id IN (404,405,406,407,408);",

            // ADD Rest Property Portfolios
            "INSERT INTO `master_portfolio`(`id`, `parent_id`, `code`, `name_en`, `name_np`, `bs_ri_code`, `active`, `created_at`, `created_by`) VALUES
                            (404, 4, 'AGRVLPRTP', 'Agreed Valued Property', 'मुल्यांकित सम्पत्ति', 0, 1, NOW(), 1),
                            (405, 4, 'FLTPRPT', 'Floating Property', 'फ्लोटिङ सम्पत्ति', 0, 1, NOW(), 1),
                            (406, 4, 'DCLRPRPT', 'Declaration Property', 'घोषणा सम्पत्ति', 0, 1, NOW(), 1),
                            (407, 4, 'FLTDCLRPRPT', 'Floating Declaration Property', 'फ्लोटिङ घोषणा सम्पत्ति', 0, 1, NOW(), 1),
                            (408, 4, 'REINSTPRPT', 'Reinstate Property', 'पुनर्स्थापना सम्पत्ति', 0, 1, NOW(), 1);",


            // Nullify Risks Configuration
            "UPDATE `master_portfolio` SET `risks`=NULL, `updated_at`=NOW(), `updated_by`=1 WHERE `parent_id` = 4;"
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

            // Default Internal Accounts for Added Portfolios
            $this->_portfolio_accounts();

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

    // Add and assign default portfolio accounts
    private function _portfolio_accounts()
    {
        $portfolio_ac_group_ids = [
            'Direct Premium Income'     => [ IQB_AC_ACCOUNT_GROUP_ID_DIRECT_PREMIUM_INCOME, 'account_id_dpi'] ,
            'Premium Ceded - Treaty'    => [ IQB_AC_ACCOUNT_GROUP_ID_PREMIUM_CEDED, 'account_id_tpc'] ,
            'Premium Ceded - FAC'       => [ IQB_AC_ACCOUNT_GROUP_ID_PREMIUM_CEDED, 'account_id_fpc'],
            'Treaty Commission'         => [IQB_AC_ACCOUNT_GROUP_ID_RCI, 'account_id_rtc'],
            'FAC Commission'            => [IQB_AC_ACCOUNT_GROUP_ID_RCI, 'account_id_rfc'],
            'FAC Premium'               => [IQB_AC_ACCOUNT_GROUP_ID_REINSURANCE_PREMIUM_INCOME, 'account_id_fpi'],
            'FAC Commission Expense'    => [IQB_AC_ACCOUNT_GROUP_ID_RCE, 'account_id_fce'],
            'Portfolio Withdrawl'       => [IQB_AC_ACCOUNT_GROUP_ID_RECEIVABLE_FROM_REINSURER, 'account_id_pw'] ,
            'Portfolio Entry'           => [IQB_AC_ACCOUNT_GROUP_ID_PAYABLE_TO_REINSURER, 'account_id_pe'],
            'Claim Expense'             => [IQB_AC_ACCOUNT_GROUP_ID_CLAIM_EXPENSE, 'account_id_ce'],
            'Claim Receivable'          => [IQB_AC_ACCOUNT_GROUP_ID_RECEIVABLE_FROM_REINSURER, 'account_id_cr']
        ];

        $this->load->model('portfolio_model');
        $this->load->model('ac_account_model');
        $portfolios = $this->db->select('*')
                                ->from('master_portfolio')
                                ->where_in('id', [404, 405, 406, 407, 408])
                                ->get()->result();


        foreach($portfolios as $portfolio)
        {
            foreach($portfolio_ac_group_ids as $prefix=>$ac_group__ac_col)
            {
                $account_group_id   = $ac_group__ac_col[0];
                $account_col        = $ac_group__ac_col[1];

                // Add only if no account id already
                print "{$portfolio->name_en} ({$account_col}) : Saving Default Internal Accounts ... ";
                if( !$portfolio->{$account_col} )
                {
                    $account_data = [
                        'account_group_id'  => $account_group_id,
                        'name'              =>  $prefix . " ({$portfolio->name_en} - {$portfolio->code})",
                        'active'            => 1,
                        'created_at'        => date('Y-m-d H:i:s'),
                        'created_by'        => 1
                    ];
                    $account_id = $this->ac_account_model->insert($account_data, TRUE);

                    // Update Portfolio Account ID
                    if($account_id)
                    {
                        $portfolio_ac_data = [$account_col => $account_id];

                        $done = $this->db->where('id', $portfolio->id)
                                         ->set($portfolio_ac_data)
                                         ->update('master_portfolio');

                        print $done ? "DONE\n\r" : "FAIL\n\r";
                    }
                }
                else
                {
                    print "ALREADY EXIST\n\r";
                }
            }
        }
    }


    public function down()
    {

    }
}