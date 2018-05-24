<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Month_model extends MY_Model
{
    protected $table_name = 'master_months';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    // protected $before_insert = ['capitalize_code'];
    // protected $before_update = ['capitalize_code'];
    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ['id', 'name_en', 'name_np', 'created_at', 'created_by', 'updated_at', 'updated_by'];

    protected $validation_rules = [
        [
            'field' => 'name_en',
            'label' => 'Name (EN)',
            'rules' => 'trim|required|max_length[80]',
            '_type'     => 'text',
            '_required' => true
        ],
        [
            'field' => 'name_np',
            'label' => 'Name (NP)',
            'rules' => 'trim|required|max_length[80]',
            '_type'     => 'text',
            '_required' => true
        ],
    ];


    /**
     * Protect Default Records?
     */
    public static $protect_default = FALSE;
    public static $protect_max_id   = 0; // Prevent first 12 records from deletion.

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

    public function get($id)
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_name = 'mnth_' . $id;

        $record = $this->get_cache($cache_name);
        if(!$record)
        {
            $record = $this->db->select('id, name_en, name_np')
                        ->from($this->table_name)
                        ->where('id', $id)
                        ->get()->row();
            $this->write_cache($record, $cache_name, CACHE_DURATION_MONTH);
        }
        return $record;
    }

    // ----------------------------------------------------------------

    public function get_all()
    {
        $this->clear_cache();
        /**
         * Get Cached Result, If no, cache the query result
         */
        $list = $this->get_cache('mnth_all');
        if(!$list)
        {
            $list = $this->db->select('id, name_en, name_np')
                        ->from($this->table_name)
                        ->get()->result();
            $this->write_cache($list, 'mnth_all', CACHE_DURATION_MONTH);
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
            $list["{$record->id}"] = $record->name_np . "({$record->name_en}) ";
        }
        return $list;
    }

    // --------------------------------------------------------------------

    /**
     * Get Dropdown List - Fiscal Year Month Order
     */
    public function dropdown_fy()
    {
        $dropdown = $this->dropdwon();
        $dd_first_year = [];
        $dd_second_year = [];
        foreach($dropdown as $key=>$value)
        {
            if($key <= 3)
            {
                $dd_second_year[$key] = $value;
            }
            else
            {
                $dd_first_year[$key] = $value;
            }
        }

        $dd = $dd_first_year + $dd_second_year;
        return $dd;
    }

	// --------------------------------------------------------------------

    /**
     * Delete Cache on Update
     */
    public function clear_cache()
    {
        $cache_names = [
            'mnth_*'
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
}