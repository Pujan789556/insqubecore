<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migration Model
 *
 * Tasks: Portfolio Object's Items Re-formatting
 */
class M20180530_model extends MY_Model
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
         * Fire Portfolio
         */
        $this->fire_fire();



    }

    public function fire_fire()
    {
        $this->load->helper('ph_fire_fire');

        $list = $this->db->select('id, portfolio_id, attributes, amt_sum_insured, si_breakdown')
                        ->from('dt_objects')
                        ->where('portfolio_id', IQB_SUB_PORTFOLIO_FIRE_GENERAL_ID)
                        ->get()->result();

        $item_rules = _OBJ_FIRE_FIRE_manual_item_v_rules();

        $total_success = 0;


        // Use automatic transaction
        $this->db->trans_start();


            foreach($list as $record)
            {
                $items_formatted    = [];
                $attributes         = json_decode($record->attributes, TRUE);

                if( $attributes['item_attached'] == IQB_FLAG_NO && isset($attributes['items']['category']) && is_array($attributes['items']['category']) )
                {
                    $items = $attributes['items'];
                    for($i=0; $i < count($attributes['items']['category']); $i++)
                    {
                        $single = [];
                        foreach($item_rules as $rule)
                        {
                            $key = $rule['_key'];
                            $single[$key] = $items[$key][$i] ?? '';
                        }
                        $items_formatted[] = $single;
                    }

                    if($items_formatted)
                    {
                        $attributes['items'] = $items_formatted;

                        $done = $this->db->where('id', $record->id)
                                     ->set([
                                            'attributes' => json_encode($attributes),
                                            'updated_at' => date('Y-m-d H:i:s'),
                                            'updated_by' => 1
                                        ])
                                     ->update('dt_objects');

                        $done ? $total_success++ : '';
                    }

                }
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
            die("Successfully migrated {$total_success}" . PHP_EOL );
        }
    }

    // ----------------------------------------------------------------
}