<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migration Model
 *
 * Tasks:
 * Update Endorsement Attributes
 *      - issured date
 *      - start date
 *      - end date
 *      - customer id
 *      - sold by id
 *  from Policy to Endorsements
 */
class M20180719_model extends MY_Model
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
         * Task 1: Get all Active Policy IDs
         */
        $list = $this->db->select('E.id, E.start_date AS e_start_date, E.customer_id AS e_customer_id, P.customer_id, P.sold_by, P.start_date, P.end_date, P.issued_date')
                         ->from('dt_endorsements E')
                         ->join('dt_policies P', 'E.policy_id = P.id')
                         ->get()
                         ->result();

        echo "Total records identified: " . count($list) . PHP_EOL;

        $this->load->model('policy_model');
        $this->load->model('endorsement_model');

        $success = 0;
        foreach($list as $single)
        {
            if( !empty($single->e_start_date) || !empty($single->e_customer_id) )
            {
                continue;
            }
            $data = [
                'customer_id'   => $single->customer_id,
                'sold_by'       => $single->sold_by,
                'start_date'    => $single->start_date,
                'end_date'      => $single->end_date,
                'issued_date'   => $single->issued_date
            ];

            $done = $this->endorsement_model->update($single->id, $data, TRUE);
            $done ? $success++ : '';
        }

        echo "Successfully migrated records: {$success}." . PHP_EOL;
    }

    // ----------------------------------------------------------------
}