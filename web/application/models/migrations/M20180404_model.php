<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migration for Customer Object Relation
 */
class M20180404_model extends MY_Model
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
        /**
         * Get the list of Object and Customer IDs from existing database
         */
        $list = $this->db->select('id, customer_id')
                        ->from('dt_objects')
                        ->get()->result();


        $this->load->model('rel_customer_object_model');

        // Use automatic transaction
        $this->db->trans_start();

            foreach($list as $single)
            {
                $rel_data = [
                    'customer_id' => $single->customer_id,
                    'object_id'  => $single->id
                ];
                $this->rel_customer_object_model->insert($rel_data, TRUE);
            }

            // Remove Customer ID from Object Table
            $sql = "ALTER TABLE dt_objects DROP FOREIGN KEY __fkc__customer__policy_object;";
            $this->db->query($sql);

            $sql = "ALTER TABLE `dt_objects` DROP `customer_id`;";
            $this->db->query($sql);


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