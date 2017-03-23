<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ac_account_group_model extends MY_Model
{
    protected $table_name = 'ac_account_groups';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    // protected $before_insert = ['capitalize_code'];
    // protected $before_update = ['capitalize_code'];
    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ['id', 'parent_id', 'range_min', 'range_max', 'name_en', 'name_np', 'created_at', 'created_by', 'updated_at', 'updated_by'];

    protected $validation_rules = [];


    /**
     * Protect Default Records?
     */
    public static $protect_default = TRUE;
    public static $protect_max_id = 12; // Prevent first 12 records from deletion.

	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @return void
	 */
    public function __construct()
    {
        parent::__construct();

        // Set validation rules
        $this->validation_rules();
    }

    // ----------------------------------------------------------------

    /**
     * Set Validation Rules
     *
     * @return void
     */
    public function validation_rules()
    {
        $dropdown_parent = $this->dropdown_parent();

        $this->validation_rules = [
            [
                'field' => 'parent_id',
                'label' => 'Parent Group',
                'rules' => 'trim|integer|max_length[11]|in_list[0,' . implode(',', array_keys($dropdown_parent)) . ']|callback__cb_valid_parent',
                '_type'     => 'dropdown',
                '_data'     => IQB_ZERO_SELECT + $dropdown_parent,
                '_required' => true
            ],
            [
                'field' => 'name_en',
                'label' => 'Account Group Name (EN)',
                'rules' => 'trim|required|max_length[80]',
                '_type'     => 'text',
                '_required' => true
            ],
            [
                'field' => 'name_np',
                'label' => 'Account Group Name (NP)',
                'rules' => 'trim|max_length[100]',
                '_type'     => 'text',
                '_required' => false
            ],
            [
                'field' => 'range_min',
                'label' => 'Account Number Min',
                'rules' => 'trim|required|integer|max_length[6]|callback__cb_valid_range_min',
                '_type'     => 'text',
                '_required' => true
            ],
            [
                'field' => 'range_max',
                'label' => 'Account Number Max',
                'rules' => 'trim|required|integer|max_length[6]|callback__cb_valid_range_max',
                '_type'     => 'text',
                '_required' => true
            ],
        ];



    }

    // --------------------------------------------------------------------

    /**
     * Get Dropdown List
     */
    public function dropdown_tree()
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $records = $this->get_all();

        $list = [];
        foreach ($records as $record)
        {
            if($record->parent_id)
            {
                $list['p_'.$record->parent_id]['children'][] = $record;
            }
            else
            {
                $list['p_'.$record->id]['parent'] = $record;
            }
        }

        $dropdown_tree = [];

        foreach ($list as $p)
        {
            $parent     = $p['parent'];
            $children   = $p['children'] ?? [];
            $dropdown_tree["{$parent->id}"] = $parent->name_en . " [{$parent->range_min}-{$parent->range_max}]";

            foreach($children as $child)
            {
                $dropdown_tree["{$child->id}"] = "|--- " . $child->name_en . " [{$child->range_min}-{$child->range_max}]";
            }

        }
        return $dropdown_tree;
    }

    // --------------------------------------------------------------------

    /**
     * Get Parent Dropdown List
     */
    public function dropdown_parent()
    {
        $cache_name = 'ac_account_group_parent';
        /**
         * Get Cached Result, If no, cache the query result
         */
        $list = $this->get_cache($cache_name);
        if(!$list)
        {
            $records = $this->db->select('`id`, `range_min`, `range_max`, `name_en`, `name_np`')
                        ->from($this->table_name)
                        ->where('parent_id', 0)
                        ->get()->result();

            $list = [];
            foreach($records as $record)
            {
                $list["{$record->id}"] = "[{$record->range_min}-{$record->range_max}] " . $record->name_en;
            }

            $this->write_cache($list, $cache_name, CACHE_DURATION_DAY);
        }

        return $list;
    }

    // --------------------------------------------------------------------

    /**
     * Get Parent Dropdown List
     */
    public function dropdown_children($parent_id)
    {
        $cache_name = 'ac_account_group_children_' . $parent_id;
        /**
         * Get Cached Result, If no, cache the query result
         */
        $list = $this->get_cache($cache_name);
        if(!$list)
        {
            $records = $this->db->select('`id`, `range_min`, `range_max`, `name_en`, `name_np`')
                        ->from($this->table_name)
                        ->where('parent_id', $parent_id)
                        ->get()->result();

            $list = [];
            foreach($records as $record)
            {
                $list["{$record->id}"] = "[{$record->range_min}-{$record->range_max}] " . $record->name_en;
            }

            $this->write_cache($list, $cache_name, CACHE_DURATION_DAY);
        }

        return $list;
    }

    // ----------------------------------------------------------------

    public function valid_range($id, $ac_number)
    {
        $valid = FALSE;

        $record = parent::find($id);
        if($record)
        {
            $valid = $ac_number >= $record->range_min && $ac_number <= $record->range_max;
        }
        return $valid;
    }

    // ----------------------------------------------------------------

    public function get_all()
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $list = $this->get_cache('ac_ag_all');
        if(!$list)
        {
            // $list = $this->db->select('`id`, `parent_id`, `range_min`, `range_max`, `name_en`, `name_np`')
            //             ->from($this->table_name)
            //             ->get()->result();

            $list = $this->db->select('AG.`id`, AG.`parent_id`, AG.`range_min`, AG.`range_max`, AG.`name_en`, AG.`name_np`, AGP.`name_en` as parent_name_en, AGP.`name_np` as parent_name_np')
                 ->from($this->table_name . ' as AG')
                 ->join( $this->table_name . ' AGP', 'AGP.id = AG.parent_id', 'left')
                 ->get()->result();

            $this->write_cache($list, 'ac_ag_all', CACHE_DURATION_DAY);
        }
        return $list;
    }

    // ----------------------------------------------------------------

    public function row($id)
    {
        return $this->db->select('AG.`id`, AG.`parent_id`, AG.`range_min`, AG.`range_max`, AG.`name_en`, AG.`name_np`, AGP.`name_en` as parent_name_en, AGP.`name_np` as parent_name_np')
                 ->from($this->table_name . ' as AG')
                 ->join( $this->table_name . ' AGP', 'AGP.id = AG.parent_id', 'left')
                 ->where('AG.id', $id)
                 ->get()->row();
    }

    // ----------------------------------------------------------------

    public function check_duplicate($where, $id=NULL)
    {
        if( $id )
        {
            $this->db->where('id !=', $id);
        }
        // $where is array ['key' => $value]
        return $this->db->where($where)
                        ->count_all_results($this->table_name);
    }

    // --------------------------------------------------------------------

    /**
     * Get Dropdown List
     */
    public function dropdown()
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $records = $this->get_all();
        $list = [];
        foreach($records as $record)
        {
            $list["{$record->id}"] = "[{$record->range_min}-{$record->range_max}] " . $record->name_en;
        }
        return $list;
    }

	// --------------------------------------------------------------------

    /**
     * Delete Cache on Update
     */
    public function clear_cache()
    {
        $cache_names = [
            'ac_ag_all',
            'ac_account_group_parent',
            'ac_account_group_children_*'
        ];
        // cache name without prefix
        foreach($cache_names as $cache)
        {
            $this->delete_cache($cache);
        }
        return TRUE;
    }

    // ----------------------------------------------------------------

    public function delete($id = NULL)
    {
        return FALSE;
    }

    // ----------------------------------------------------------------

    /**
     * Log Activity
     *
     * Log activities
     *      Available Activities: Create|Edit|Delete
     *
     * @param integer $id
     * @param string $action
     * @return bool
     */
    public function log_activity($id, $action = 'C')
    {
        $action = is_string($action) ? $action : 'C';
        // Save Activity Log
        $activity_log = [
            'module' => 'ac_account_group',
            'module_id' => $id,
            'action' => $action
        ];
        return $this->activity->save($activity_log);
    }
}