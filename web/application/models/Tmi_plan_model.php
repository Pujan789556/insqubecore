<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tmi_plan_model extends MY_Model
{
    protected $table_name = 'master_tmi_plans';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    protected $before_insert = ['before_insert_update__defaults'];
    protected $before_update = ['before_insert_update__defaults'];
    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ['id', 'parent_id', 'code', 'name', 'tariff_medical', 'tariff_package', 'benefits', 'active', 'created_at', 'created_by', 'updated_at', 'updated_by'];

    protected $validation_rules = [];


    /**
     * Protect Default Records?
     */
    public static $protect_default = TRUE;
    public static $protect_max_id = 1000; // Prevent first 12 records from deletion.

	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @return void
	 */
    public function __construct()
    {
        parent::__construct();

        // Validation Rules
        $this->validation_rules();
    }

    // ----------------------------------------------------------------

    public function validation_rules()
    {
        $parent_dropdown = $this->dropdown_parent();

        $this->validation_rules = [

            'basic' => [
                [
                    'field' => 'parent_id',
                    'label' => 'Parent Plan',
                    'rules' => 'trim|integer|max_length[8]|in_list[' . implode(',', array_keys($parent_dropdown)) . ']',
                    '_type'     => 'dropdown',
                    '_data'     => IQB_BLANK_SELECT + $parent_dropdown,
                    '_required' => true
                ],
                [
                    'field' => 'name',
                    'label' => 'Plan Name',
                    'rules' => 'trim|required|max_length[100]|ucfirst',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'code',
                    'label' => 'Plan Code',
                    'rules' => 'trim|required|alpha|max_length[15]|strtoupper|callback_check_duplicate',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'active',
                    'label' => 'Activate Plan',
                    'rules' => 'trim|integer|in_list[1]',
                    '_type' => 'switch',
                    '_checkbox_value' => '1'
                ]
            ],

            /**
             * Tariff Validation Rules
             *
             * Format: [
             *  {
             *      DayMin:xxx,
             *      DayMax:xxx,
             *      AgeBand5_40Rate:aaa,
             *      AgeBand41_60Rate:bbb,
             *      AgeBand61_70Rate:ccc
             *  },
             *  ...
             * ]
             */
            'tariff' => [
                [
                    'field' => 'tariff[day_min][]',
                    '_key'  => 'day_min',
                    'label' => 'Days (min)',
                    'rules' => 'trim|required|integer|max_length[3]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_required' => true
                ],
                [
                    'field' => 'tariff[day_max][]',
                    '_key'  => 'day_max',
                    'label' => 'Days (max)',
                    'rules' => 'trim|required|integer|max_length[3]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_required' => true
                ],
                [
                    'field' => 'tariff[age_5_40_rate][]',
                    '_key'  => 'age_5_40_rate',
                    'label' => 'Age Band (5-40) Rate (USD)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[8]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_required' => true
                ],
                [
                    'field' => 'tariff[age_41_60_rate][]',
                    '_key'  => 'age_41_60_rate',
                    'label' => 'Age Band (41-60) Rate (USD)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[8]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_required' => true
                ],
                [
                    'field' => 'tariff[age_61_70_rate][]',
                    '_key'  => 'age_61_70_rate',
                    'label' => 'Age Band (61-70) Rate (USD)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[8]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_required' => true
                ],
                [
                    'field' => 'tariff[age_71_79_rate][]',
                    '_key'  => 'age_71_79_rate',
                    'label' => 'Age Band (71 - 79) Rate (USD)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[8]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_required' => true
                ],
                [
                    'field' => 'tariff[age_80_84_rate][]',
                    '_key'  => 'age_80_84_rate',
                    'label' => 'Age Band (80 - 84) Rate (USD)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[8]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_required' => true
                ],
                [
                    'field' => 'tariff[age_85_above_rate][]',
                    '_key'  => 'age_85_above_rate',
                    'label' => 'Age Band (85 - above) Rate (USD)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[8]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_required' => true
                ]
            ],

            /**
             * Benefits Validation Rules
             *
             * Format: [
             *  {
             *      SectionCover:xxx,
             *      Benefit:xxx,
             *      MaxSumInsured:aaa,
             *      Excess:bbb
             *  },
             *  ...
             * ]
             */
            'benefits' => [
                [
                    'field' => 'benefits[section][]',
                    '_key'  => 'section',
                    'label' => 'Section of Cover',
                    'rules' => 'trim|required|max_length[10]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_required' => true
                ],
                [
                    'field' => 'benefits[benefit][]',
                    '_key'  => 'benefit',
                    'label' => 'Benefit',
                    'rules' => 'trim|required|max_length[500]',
                    '_type'     => 'textarea',
                    'rows'      => 4,
                    '_show_label' => false,
                    '_required' => true
                ],
                [
                    'field' => 'benefits[max_sum_insured][]',
                    '_key'  => 'max_sum_insured',
                    'label' => 'Max. Sum Insured (USD)',
                    'rules' => 'trim|required|max_length[200]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_required' => true
                ],
                [
                    'field' => 'benefits[excess][]',
                    '_key'  => 'excess',
                    'label' => 'Excess',
                    'rules' => 'trim|required|max_length[200]',
                    '_type'     => 'text',
                    '_show_label' => false,
                    '_required' => true
                ]
            ]
        ];
    }

    // ----------------------------------------------------------------

    public function get_all()
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $list = $this->get_cache('tmiplan_all');
        if(!$list)
        {
            // $list = parent::find_all();

            $list = $this->db->select('L1.*, L2.name as parent_name')
                             ->from($this->table_name . ' L1')
                             ->join($this->table_name . ' L2', 'L1.parent_id = L2.id', 'left')
                             ->order_by('L1.parent_id')
                             ->order_by('L1.name')
                             ->get()->result();
            $this->write_cache($list, 'tmiplan_all', CACHE_DURATION_DAY);
        }
        return $list;
    }

    // ----------------------------------------------------------------

    public function find($id)
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_var = 'tmiplan_s_'.$id;
        $record = $this->get_cache($cache_var);
        if(!$record)
        {
            $record = $this->db->select('L1.*, L2.name as parent_name')
                             ->from($this->table_name . ' L1')
                             ->join($this->table_name . ' L2', 'L1.parent_id = L2.id', 'left')
                             ->where('L1.id', $id)
                             ->get()->row();

            if($record)
            {
                $this->write_cache($record, $cache_var, CACHE_DURATION_DAY);
            }
        }
        return $record;
    }

    // ----------------------------------------------------------------

    public function update_tariff_benefits($id, $data)
    {
        // add modified details
        $data = $this->modified_on(['fields' => $data]);

        $result = $this->db->set($data)
                        ->where('id', $id)
                        ->update($this->table_name);

        // Clean Cache
        $this->clear_cache();

        return $result;
    }

    // ----------------------------------------------------------------

    /**
     * Trigger - Before Insert/Update
     *
     * The following tasks are carried out before inserting/updating the record:
     *  1. Capitalize Code
     *  2. Nullify Parent ID if empty supplied
     *
     * @param array $data
     * @return array
     */
    public function before_insert_update__defaults($data)
    {
        $code_cols = array('code');
        foreach($code_cols as $col)
        {
            if( isset($data[$col]) && !empty($data[$col]) )
            {
                $data[$col] = strtoupper($data[$col]);
            }
        }

        if( !$data['parent_id'])
        {
            $data['parent_id'] = NULL;
        }
        return $data;
    }

    // ----------------------------------------------------------------

    public function check_duplicate($where, $id=NULL)
    {
        if( $id )
        {
            $this->db->where('id !=', $id);
        }
        return $this->db->where($where)
                        ->count_all_results($this->table_name);
    }

    // ----------------------------------------------------------------

    public function dropdown_parent($field='id')
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $list = $this->get_cache('tmiplan_parent_all');
        if(!$list)
        {
            $records = $this->db->select('id, code, name')
                             ->from($this->table_name)
                             ->where('parent_id', NULL)
                             ->get()->result();

            $list = [];
            foreach($records as $record)
            {
                $column = $record->{$field};
                $list["{$column}"] = $record->name;
            }
            if(!empty($list))
            {
                $this->write_cache($list, 'tmiplan_parent_all', CACHE_DURATION_DAY);
            }
        }
        return $list;
    }

    // ----------------------------------------------------------------

    public function dropdown_children_tree()
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $list = $this->get_cache('tmiplan_dropdown_children_tree');
        if(!$list)
        {
            $records = $this->db->select('N.id, N.parent_id, N.code, N.name, P.name AS parent_name')
                             ->from($this->table_name . ' AS N')
                             ->join($this->table_name . ' AS P', 'P.id = N.parent_id', 'left')
                             ->where('N.parent_id !=', NULL)
                             ->get()->result();

            $list = [];
            foreach($records as $record)
            {
                $parent_name = $record->parent_name;
                $list["{$parent_name}"]["{$record->id}"] = $record->name;
            }
            if(!empty($list))
            {
                $this->write_cache($list, 'tmiplan_dropdown_children_tree', CACHE_DURATION_DAY);
            }
        }
        return $list;
    }

    // ----------------------------------------------------------------

    public function get_code($id)
    {
        $record = $this->find($id);
        return $record ? $record->code : '';
    }

    // ----------------------------------------------------------------

    public function get_children($parent_id=NULL)
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        if(!$parent_id)
        {
            $cache_var = 'tmiplan_children_all';
        }
        else
        {
            $cache_var = 'tmiplan_children_' . $parent_id;
        }

        $list = $this->get_cache($cache_var);
        if(!$list)
        {
            $this->db->select('L1.*, L2.code as parent_code, L2.name as parent_name')
                             ->from($this->table_name . ' L1')
                             ->join($this->table_name . ' L2', 'L1.parent_id = L2.id', 'left');

            if($parent_id)
            {
                $this->db->where('L1.parent_id', $parent_id);
            }
            else
            {
                $this->db->where('L1.parent_id !=', NULL);
            }
            $list = $this->db->get()->result();

            if(!empty($list))
            {
                $this->write_cache($list, $cache_var, CACHE_DURATION_DAY);
            }
        }
        return $list;
    }

    // ----------------------------------------------------------------

    public function dropdown_children($parent_id=NULL, $field='id')
    {
        $records = $this->get_children($parent_id);

        $list = [];
        foreach($records as $record)
        {
            $column = $record->{$field};
            $list["{$column}"] = $record->parent_code . ' - ' . $record->name;
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
            'tmiplan_all',
            'tmiplan_dropdown_children_tree',
            'tmiplan_parent_all',
            'tmiplan_children_*',
            'tmiplan_s_*'
        ];
        // cache name without prefix
        foreach($cache_names as $cache)
        {
            $this->delete_cache($cache);
        }
        return TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Check if this record has children
     */
    public function has_children($id)
    {
        return $this->db->where('parent_id', $id)
                        ->count_all_results($this->table_name);
    }

    // ----------------------------------------------------------------

    public function delete($id = NULL)
    {
        return FALSE;
    }
}