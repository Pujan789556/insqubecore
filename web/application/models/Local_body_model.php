<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Local_body_model extends MY_Model
{
    protected $table_name = 'master_localbodies';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $audit_log = TRUE;

    protected $protected_attributes = ['id', 'district_id', 'code'];

    protected $after_update  = ['clear_cache'];

    protected $fields = ['id', 'district_id', 'code', 'name_en', 'name_np', 'created_at', 'created_by', 'updated_at', 'updated_by'];

    protected $validation_rules = [
        [
            'field' => 'name_en',
            'label' => 'Name (EN)',
            'rules' => 'trim|required|max_length[150]',
            '_type' => 'text',
            '_required' => true
        ],
        [
            'field' => 'name_np',
            'label' => 'Name (NP)',
            'rules' => 'trim|required|max_length[150]',
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
        $list = $this->get_cache('lcoalbodies_all');
        if(!$list)
        {
            $list = $this->db->select('LB.*, D.name_en as district_name_en')
                            ->from($this->table_name . ' LB')
                            ->join('master_districts D', 'D.id = LB.district_id')
                            ->order_by('LB.code', 'asc')
                            ->get()->result();

            $this->write_cache($list, 'lcoalbodies_all', CACHE_DURATION_DAY);
        }
        return $list;
    }

    // --------------------------------------------------------------------

    /**
     * Get Dropdown List - By State
     *
     * @param int $state_id State ID
     * @param string $origin  Source of Request [main|api]
     */
    public function dropdown_by_state($state_id, $origin = "main")
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_name = 'localbody_st_' . $state_id;
        $dropdown   = $this->get_cache($cache_name);
        if(!$dropdown)
        {
            $list = $this->db->select('LB.id, LB.name_en, LB.name_np, D.name_en AS district_name_en, D.name_np AS district_name_np')
                            ->from($this->table_name . ' LB')
                            ->join('master_districts D', 'D.id = LB.district_id')
                            ->join('master_states S', 'S.id = D.state_id')
                            ->where('S.id', $state_id)
                            ->order_by('LB.name_en')
                            ->get()->result();

            $dropdown = [];
            if($list)
            {
                foreach ($list as $record)
                {
                    $column = $record->id;
                    $dropdown["{$column}"] = $record->name_en . ' - ' . $record->district_name_en . ' (' . $record->name_np . ' - ' . $record->district_name_np . ')';
                }
                $this->write_cache($dropdown, $cache_name, CACHE_DURATION_DAY);
            }
        }

        // build {key:xxx, value:xxx}
        if($origin == 'api')
        {
            $dropdown_api = [];
            foreach($dropdown as $key=>$value)
            {
                $single = new stdClass;
                $single->key = $key;
                $single->value = $value;
                $dropdown_api[] = $single;
            }
            return $dropdown_api;
        }


        return $dropdown;
    }

    // --------------------------------------------------------------------

    /**
     * Get Dropdown List - By District
     *
     * @param int $state_id District ID
     * @param string $lang Language
     * @param string $origin  Source of Request [main|api]
     */
    public function dropdown_by_district($district_id, $lang="both", $origin = "main")
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_name = 'localbody_dst_' . $district_id . '_' . $lang;
        $dropdown   = $this->get_cache($cache_name);
        if(!$dropdown)
        {
            $list = $this->db->select('LB.id, LB.name_en, LB.name_np')
                            ->from($this->table_name . ' LB')
                            ->join('master_districts D', 'D.id = LB.district_id')
                            ->where('D.id', $district_id)
                            ->order_by('LB.name_en')
                            ->get()->result();

            $dropdown = [];
            if($list)
            {
                foreach ($list as $record)
                {
                    $column = $record->id;
                    $label = $lang === "both"
                        ? $record->name_en . " ({$record->name_np})"
                        : ($lang === "en" ? $record->name_en : $record->name_np);
                    $dropdown["{$column}"] = $label;
                }
                $this->write_cache($dropdown, $cache_name, CACHE_DURATION_DAY);
            }
        }

        // build {key:xxx, value:xxx}
        if($origin == 'api')
        {
            $dropdown_api = [];
            foreach($dropdown as $key=>$value)
            {
                $single = new stdClass;
                $single->key = $key;
                $single->value = $value;
                $dropdown_api[] = $single;
            }
            return $dropdown_api;
        }

        return $dropdown;
    }


    // ----------------------------------------------------------------

    public function get($id)
    {
        return $this->db->select('LB.*, D.name_en as district_name_en')
                            ->from($this->table_name . ' LB')
                            ->join('master_districts D', 'D.id = LB.district_id')
                            ->where('LB.id', $id)
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
        $cache_names = [
            'localbody_st_*',
            'localbody_dst_*'
        ];

        // cache name without prefix
        $this->delete_cache($cache_names);

        return TRUE;
    }
}