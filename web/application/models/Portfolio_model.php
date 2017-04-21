<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Portfolio_model extends MY_Model
{
    protected $table_name = 'master_portfolio';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id', 'parent_id', 'code'];

    protected $before_insert = ['capitalize_code'];
    protected $before_update = ['capitalize_code'];
    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ["id", "parent_id", "code", "name_en", "name_np", "created_at", "created_by", "updated_at", "updated_by"];

    protected $validation_rules = [
        [
            'field' => 'name_en',
            'label' => 'Portfolio Name(EN)',
            'rules' => 'trim|required|max_length[100]',
            '_type'     => 'text',
            '_required' => true
        ],
        [
            'field' => 'name_np',
            'label' => 'Portfolio Name (NP)',
            'rules' => 'trim|max_length[100]',
            '_type'     => 'text',
            '_required' => false
        ]
    ];


    /**
     * Protect Default Records?
     */
    public static $protect_default = TRUE;
    public static $protect_max_id = 99; // Prevent first 12 records from deletion.

	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @return void
	 */
    public function __construct()
    {
        parent::__construct();

        // Cache clear
        $this->clear_cache();
    }

    // ----------------------------------------------------------------

    public function get_all()
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $list = $this->get_cache('pf_all');
        if(!$list)
        {
            // $list = parent::find_all();

            $list = $this->db->select('L1.*, L2.name_en as parent_name')
                             ->from($this->table_name . ' L1')
                             ->join($this->table_name . ' L2', 'L1.parent_id = L2.id', 'left')
                             ->get()->result();
            $this->write_cache($list, 'pf_all', CACHE_DURATION_DAY);
        }
        return $list;
    }

    // ----------------------------------------------------------------

    public function find($id)
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_var = 'portfolio_s_'.$id;
        $record = $this->get_cache($cache_var);
        if(!$record)
        {
            $record = $this->db->select('L1.id, L1.code, L1.parent_id, L1.name_en, L1.name_np, L2.name_en as parent_name')
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

    public function capitalize_code($data)
    {
        $code_cols = array('code');
        foreach($code_cols as $col)
        {
            if( isset($data[$col]) && !empty($data[$col]) )
            {
                $data[$col] = strtoupper($data[$col]);
            }
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
        // $where is array ['key' => $value]
        return $this->db->where($where)
                        ->count_all_results($this->table_name);
    }

    // ----------------------------------------------------------------

    public function dropdown_parent($field='id')
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $list = $this->get_cache('pf_parent_all');
        if(!$list)
        {
            $records = $this->db->select('id, code, name_en')
                             ->from($this->table_name)
                             ->where('parent_id', '0')
                             ->get()->result();

            $list = [];
            foreach($records as $record)
            {
                $column = $record->{$field};
                $list["{$column}"] = $record->name_en;
            }
            if(!empty($list))
            {
                $this->write_cache($list, 'pf_parent_all', CACHE_DURATION_DAY);
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
        $list = $this->get_cache('pf_dropdown_children_tree');
        if(!$list)
        {
            $records = $this->db->select('N.id, N.parent_id, N.code, N.name_en, P.name_en AS parent_name_en')
                             ->from($this->table_name . ' AS N')
                             ->join($this->table_name . ' AS P', 'P.id = N.parent_id', 'left')
                             ->where('N.parent_id !=', 0)
                             ->get()->result();

            $list = [];
            foreach($records as $record)
            {
                $parent_name = $record->parent_name_en;
                $list["{$parent_name}"]["{$record->id}"] = $record->name_en;
            }
            if(!empty($list))
            {
                $this->write_cache($list, 'pf_dropdown_children_tree', CACHE_DURATION_DAY);
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
            $cache_var = 'pf_children_all';
        }
        else
        {
            $cache_var = 'pf_children_' . $parent_id;
        }

        $list = $this->get_cache($cache_var);
        if(!$list)
        {
            $this->db->select('L1.*, L2.code as parent_code, L2.name_en as parent_name_en, L2.name_np as parent_name_np')
                             ->from($this->table_name . ' L1')
                             ->join($this->table_name . ' L2', 'L1.parent_id = L2.id', 'left');


            // $this->db->select('id, parent_id, code, name_en, name_np')
            //                 ->from($this->table_name);

            if($parent_id)
            {
                $this->db->where('L1.parent_id', $parent_id);
            }
            else
            {
                $this->db->where('L1.parent_id !=', 0);
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
            $list["{$column}"] = $record->parent_code . ' - ' . $record->name_en;
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
            'pf_all',
            'pf_dropdown_children_tree',
            'pf_parent_all',
            'pf_children_*',
            'portfolio_s_*'
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
        $id = intval($id);
        if( !safe_to_delete( get_class(), $id ) )
        {
            return FALSE;
        }

        // Check if we have child Constraint
        if( $this->has_children($id))
        {
            return FALSE;
        }


        // Disable DB Debug for transaction to work
        $this->db->db_debug = FALSE;

        $status = TRUE;

        // Use automatic transaction
        $this->db->trans_start();

            parent::delete($id);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE)
        {
            // get_allenerate an error... or use the log_message() function to log your error
            $status = FALSE;
        }
        else
        {
            $this->log_activity($id, 'D');
        }

        // Enable db_debug if on development environment
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;

        // return result/status
        return $status;
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
            'module' => 'portfolio',
            'module_id' => $id,
            'action' => $action
        ];
        return $this->activity->save($activity_log);
    }
}