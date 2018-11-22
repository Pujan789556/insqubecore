<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class District_model extends MY_Model
{
    protected $table_name = 'master_districts';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id', "state_id", "code"];

    protected $after_update  = ['clear_cache'];

    protected $fields = ["id", "state_id", "code", "name_en", "name_np", "created_at", "created_by", "updated_at", "updated_by"];

    protected $validation_rules = [
        [
            'field' => 'name_en',
            'label' => 'Name (EN)',
            'rules' => 'trim|required|max_length[80]',
            '_type' => 'text',
            '_required' => true
        ],
        [
            'field' => 'name_np',
            'label' => 'Name (NP)',
            'rules' => 'trim|required|max_length[80]',
            '_type' => 'text',
            '_required' => true
        ]
    ];


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
        $list = $this->get_cache('districts_all');
        if(!$list)
        {
            $list = $this->db->select('D.*, S.name_en as state_name_en, R.name_en as region_name_en')
                            ->from($this->table_name . ' D')
                            ->join('master_states S', 'S.id = D.state_id')
                            ->join('master_regions R', 'R.id = D.region_id')
                            ->order_by('D.code', 'asc')
                            ->get()->result();

            $this->write_cache($list, 'districts_all', CACHE_DURATION_DAY);
        }
        return $list;
    }

    // --------------------------------------------------------------------

    /**
     * Get Dropdown List
     */
    public function dropdown($lang="both")
    {
        $records = $this->get_all();
        $list = [];
        foreach($records as $record)
        {
            $label = $lang === "both"
                        ? $record->name_en . " ({$record->name_np})"
                        : ($lang === "en" ? $record->name_en : $record->name_np);
            $list["{$record->id}"] = $label;
        }
        return $list;
    }


    // ----------------------------------------------------------------

    public function get($id)
    {
        return $this->db->select('D.*, S.name_en as state_name_en, R.name_en as region_name_en')
                            ->from($this->table_name . ' D')
                            ->join('master_states S', 'S.id = D.state_id')
                            ->join('master_regions R', 'R.id = D.region_id')
                            ->where('D.id', $id)
                            ->get()->row();
    }

    // --------------------------------------------------------------------

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

    /**
     * Delete Cache on Update
     */
    public function clear_cache()
    {
        // cache name without prefix
        return $this->delete_cache('districts_all');
    }
}