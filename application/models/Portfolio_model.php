<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Portfolio_model extends MY_Model
{
    protected $table_name = 'master_portfolio';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    protected $before_insert = ['capitalize_code'];
    protected $before_update = ['capitalize_code'];
    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ["id", "parent_id", "code", "name_en", "name_np", "created_at", "created_by", "updated_at", "updated_by"];

    protected $validation_rules = [
        [
            'field' => 'parent_id',
            'label' => 'Parent Portfolio',
            'rules' => 'trim|required|integer|max_length[11]',
            '_type'     => 'dropdown',
            '_default'  => '0',
            '_required' => true
        ],
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
        ],
        [
            'field' => 'code',
            'label' => 'Portfolio Code',
            'rules' => 'trim|required|alpha|max_length[15]|is_unique[master_portfolio.code]|strtoupper',
            '_type'     => 'text',
            '_required' => true
        ]
    ];


    /**
     * Protect Default Records?
     */
    public static $protect_default = TRUE;
    public static $protect_max_id = 50; // Prevent first 12 records from deletion.

	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @return void
	 */
    public function __construct()
    {
        parent::__construct();
    }

    // ----------------------------------------------------------------

    public function get_all()
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $list = $this->get_cache('portfolio_all');
        if(!$list)
        {
            // $list = parent::find_all();

            $list = $this->db->select('L1.*, L2.name_en as parent_name')
                             ->from($this->table_name . ' L1')
                             ->join($this->table_name . ' L2', 'L1.parent_id = L2.id', 'left')
                             ->get()->result();
            $this->write_cache($list, 'portfolio_all', CACHE_DURATION_DAY);
        }
        return $list;
    }

    // ----------------------------------------------------------------

    public function find($id)
    {
        return $this->db->select('L1.*, L2.name_en as parent_name')
                             ->from($this->table_name . ' L1')
                             ->join($this->table_name . ' L2', 'L1.parent_id = L2.id', 'left')
                             ->where('L1.id', $id)
                             ->get()->row();
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

    public function dropdown_parent()
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $list = $this->get_cache('portfolio_parent_all');
        if(!$list)
        {
            $records = $this->db->select('id, name_en')
                             ->from($this->table_name)
                             ->where('parent_id', '0')
                             ->get()->result();

            $list = [];
            foreach($records as $record)
            {
                $list["{$record->id}"] = $record->name_en;
            }
            if(!empty($list))
            {
                $this->write_cache($list, 'portfolio_parent_all', CACHE_DURATION_DAY);
            }
        }
        return $list;
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
            $list["{$record->id}"] = $record->name;
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
            'portfolio_all',
            'portfolio_parent_all'
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