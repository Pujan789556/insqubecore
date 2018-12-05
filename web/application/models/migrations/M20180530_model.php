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
         * Migrate Portfolio Object Attributes
         */
        $this->agr_crop();
    }

    // ----------------------------------------------------------------

    public function misc_gpa()
    {
        $this->load->helper('ph_misc_gpa');

        $list = $this->db->select('id, portfolio_id, attributes, amt_sum_insured, si_breakdown')
                        ->from('dt_objects')
                        ->where('portfolio_id', IQB_SUB_PORTFOLIO_MISC_GPA_ID)
                        ->get()->result();

        $item_rules = _OBJ_MISC_GPA_validation_rules(IQB_SUB_PORTFOLIO_MISC_GPA_ID)['items'];

        $total_success = 0;


        // Use automatic transaction
        $this->db->trans_start();


            foreach($list as $record)
            {
                $items_formatted    = [];
                $attributes         = json_decode($record->attributes, TRUE);

                if( isset($attributes['items']['sum_insured']) && is_array($attributes['items']['sum_insured']) )
                {
                    $items = $attributes['items'];
                    for($i=0; $i < count($attributes['items']['sum_insured']); $i++)
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

    public function eng_ear()
    {
        $this->load->helper('ph_eng_ear');

        $list = $this->db->select('id, portfolio_id, attributes, amt_sum_insured, si_breakdown')
                        ->from('dt_objects')
                        ->where('portfolio_id', IQB_SUB_PORTFOLIO_ENG_EAR_ID)
                        ->get()->result();

        $item_rules = _OBJ_ENG_EAR_validation_rules(IQB_SUB_PORTFOLIO_ENG_EAR_ID)['items'];

        $total_success = 0;


        // Use automatic transaction
        $this->db->trans_start();


            foreach($list as $record)
            {
                $items_formatted    = [];
                $attributes         = json_decode($record->attributes, TRUE);

                if( isset($attributes['items']['sum_insured']) && is_array($attributes['items']['sum_insured']) )
                {
                    $items = $attributes['items'];
                    for($i=0; $i < count($attributes['items']['sum_insured']); $i++)
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

    public function agr_cattle()
    {
        $this->load->helper('ph_agr');
        $this->load->helper('ph_agr_cattle');

        $list = $this->db->select('id, portfolio_id, attributes, amt_sum_insured, si_breakdown')
                        ->from('dt_objects')
                        ->where('portfolio_id', IQB_SUB_PORTFOLIO_AGR_CATTLE_ID)
                        ->get()->result();

        $item_rules = _OBJ_AGR_CATTLE_validation_rules(IQB_SUB_PORTFOLIO_AGR_CATTLE_ID)['items'];

        $total_success = 0;


        // Use automatic transaction
        $this->db->trans_start();


            foreach($list as $record)
            {
                $items_formatted    = [];
                $attributes         = json_decode($record->attributes, TRUE);

                if( isset($attributes['items']['sum_insured']) && is_array($attributes['items']['sum_insured']) )
                {
                    $items = $attributes['items'];
                    for($i=0; $i < count($attributes['items']['sum_insured']); $i++)
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

    public function agr_crop()
    {
        $this->load->helper('ph_agr');
        $this->load->helper('ph_agr_crop');

        $list = $this->db->select('id, portfolio_id, attributes, amt_sum_insured, si_breakdown')
                        ->from('dt_objects')
                        ->where('portfolio_id', IQB_SUB_PORTFOLIO_AGR_CROP_ID)
                        ->get()->result();

        $item_rules = _OBJ_AGR_CROP_validation_rules(IQB_SUB_PORTFOLIO_AGR_CROP_ID)['items'];

        $total_success = 0;


        // Use automatic transaction
        $this->db->trans_start();


            foreach($list as $record)
            {
                $items_formatted    = [];
                $attributes         = json_decode($record->attributes, TRUE);

                if( isset($attributes['items']['sum_insured']) && is_array($attributes['items']['sum_insured']) )
                {
                    $items = $attributes['items'];
                    for($i=0; $i < count($attributes['items']['sum_insured']); $i++)
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

    public function eng_car()
    {
        $this->load->helper('ph_eng_car');

        $list = $this->db->select('id, portfolio_id, attributes, amt_sum_insured, si_breakdown')
                        ->from('dt_objects')
                        ->where('portfolio_id', IQB_SUB_PORTFOLIO_ENG_CAR_ID)
                        ->get()->result();

        $item_rules = _OBJ_ENG_CAR_validation_rules(IQB_SUB_PORTFOLIO_ENG_CAR_ID)['items'];

        $total_success = 0;


        // Use automatic transaction
        $this->db->trans_start();


            foreach($list as $record)
            {
                $items_formatted    = [];
                $attributes         = json_decode($record->attributes, TRUE);

                if( isset($attributes['items']['sum_insured']) && is_array($attributes['items']['sum_insured']) )
                {
                    $items = $attributes['items'];
                    for($i=0; $i < count($attributes['items']['sum_insured']); $i++)
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


    public function motor_mcy()
    {
        $this->load->helper('ph_motor_helper');

        $list = $this->db->select('id, portfolio_id, attributes, amt_sum_insured, si_breakdown')
                        ->from('dt_objects')
                        ->where('portfolio_id', IQB_SUB_PORTFOLIO_MOTORCYCLE_ID)
                        ->get()->result();
        $total_success = 0;


        // Use automatic transaction
        $this->db->trans_start();


            foreach($list as $record)
            {
                $items_formatted    = [];
                $attributes         = json_decode($record->attributes, TRUE);

                // Update Seating Capacity
                $attributes['seating_capacity'] = intval($attributes['carrying_capacity'] ?? 2);
                unset($attributes['carrying_capacity']);
                unset($attributes['carrying_unit']);

                $done = $this->db->where('id', $record->id)
                                     ->set([
                                            'attributes' => json_encode($attributes),
                                            'updated_at' => date('Y-m-d H:i:s'),
                                            'updated_by' => 1
                                        ])
                                     ->update('dt_objects');

                $done ? $total_success++ : '';

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

    public function eng_bl()
    {
        $this->load->helper('ph_eng_bl');

        $list = $this->db->select('id, portfolio_id, attributes, amt_sum_insured, si_breakdown')
                        ->from('dt_objects')
                        ->where('portfolio_id', IQB_SUB_PORTFOLIO_ENG_BL_ID)
                        ->get()->result();

        $item_rules = _OBJ_ENG_BL_validation_rules(IQB_SUB_PORTFOLIO_ENG_BL_ID)['items'];

        $total_success = 0;


        // Use automatic transaction
        $this->db->trans_start();


            foreach($list as $record)
            {
                $items_formatted    = [];
                $attributes         = json_decode($record->attributes, TRUE);

                if( isset($attributes['items']['description']) && is_array($attributes['items']['description']) )
                {
                    $items = $attributes['items'];
                    for($i=0; $i < count($attributes['items']['description']); $i++)
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


    public function fire_hhp()
    {
        $this->load->helper('ph_fire_hhp');

        $list = $this->db->select('id, portfolio_id, attributes, amt_sum_insured, si_breakdown')
                        ->from('dt_objects')
                        ->where('portfolio_id', IQB_SUB_PORTFOLIO_FIRE_HOUSEHOLDER_ID)
                        ->get()->result();

        $item_rules = _OBJ_FIRE_HHP_validation_rules(IQB_SUB_PORTFOLIO_FIRE_HOUSEHOLDER_ID)['items'];

        $total_success = 0;


        // Use automatic transaction
        $this->db->trans_start();


            foreach($list as $record)
            {
                $items_formatted    = [];
                $attributes         = json_decode($record->attributes, TRUE);

                if( isset($attributes['items']['category']) && is_array($attributes['items']['category']) )
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