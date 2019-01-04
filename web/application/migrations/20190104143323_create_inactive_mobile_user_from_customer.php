
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Createinactive mobile app user from customer
 *
 */
class Migration_Create_inactive_mobile_user_from_customer extends CI_Migration {

        public function up()
        {
            $this->load->library('token');
            $this->load->model('api/app_user_model', 'app_user_model');

            // List of all customer
            $customers = $this->db->select('id, full_name_en, mobile_identity')
                                  ->from('dt_customers')
                                  ->get()->result();


			// Use automatic transaction
            $this->db->trans_start();
                print "Running Migration up()... \n";

                foreach($customers as $single )
                {
                    print "Migration ({$single->full_name_en}): ";

                    $mobile = $single->mobile_identity ? $single->mobile_identity : NULL;
                    $user_data = [
                        'mobile'        => $mobile,
                        'auth_type'     => IQB_API_AUTH_TYPE_CUSTOMER,
                        'auth_type_id'  => $single->id,
                    ];

                    $status = $this->app_user_model->register($user_data, FALSE);
                    if($status)
                    {
                        print "OK\n";
                    }
                    else
                    {
                        print "FAIL\n";
                    }
                }

            // Commit all transactions on success, rollback else
            $this->db->trans_complete();
            if ($this->db->trans_status() === FALSE)
            {
                print "Could not migrate database \n";
            }
            else
            {
                print "Successfully Migrated.\n";
            }

        }

        public function down()
        {

        }
}