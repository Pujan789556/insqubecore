<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Country_model extends MY_Model
{
    protected $table_name = 'master_countries';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    protected $before_insert = ['capitalize_codes'];
    protected $before_update = ['capitalize_codes'];
    protected $after_update  = ['clear_cache'];

    protected $fields = ["id", "name", "picture", "ud_code", "bs_code", "commission_group", "active", "type", "contact", "created_at", "created_by", "updated_at", "updated_by"];

    protected $validation_rules = [
        [
            'field' => 'name',
            'label' => 'Country Name',
            'rules' => 'trim|required|max_length[80]',
            '_type' => 'text',
            '_required' => true
        ],
        [
            'field' => 'alpha2',
            'label' => 'Country Code (alpha 2)',
            'rules' => 'trim|required|alpha|exact_length[2]|is_unique[master_countries.alpha2]',
            '_type' => 'text',
            '_required' => true
        ],
        [
            'field' => 'alpha3',
            'label' => 'Country Code (alpha 3)',
            'rules' => 'trim|required|alpha|exact_length[3]|is_unique[master_countries.alpha3]',
            '_type' => 'text',
            '_required' => true
        ],
        [
            'field' => 'dial_code',
            'label' => 'Dialing Code',
            'rules' => 'trim|required|max_length[20]',
            '_type' => 'text',
            '_required' => true
        ],
        [
            'field' => 'currency_code',
            'label' => 'Currency Code',
            'rules' => 'trim|exact_length[3]',
            '_type' => 'text'
        ],
        [
            'field' => 'currency_name',
            'label' => 'Currency Name',
            'rules' => 'trim|max_length[40]',
            '_type' => 'text'
        ]
    ];

	// --------------------------------------------------------------------

	/**
	 * Constructor
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
        $list = $this->get_cache('countries_all');
        if(!$list)
        {
            $list = parent::find_all();
            $this->write_cache($list, 'countries_all', CACHE_DURATION_DAY);
        }
        return $list;
    }

    // ----------------------------------------------------------------

    public function capitalize_codes($data)
    {
        $code_cols = array('alpha2', 'alpha3', 'currency_code');
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

    // --------------------------------------------------------------------

    public function dropdown( $column='alpha2' )
    {
        $records = $this->get_all();
        $countries = [];
        if( in_array($column, array('alpha2', 'alpha3')))
        {
            foreach($records as $record)
            {
                $countries[$record->{$column}] = $record->name;
            }
        }
        return $countries;
    }

	// --------------------------------------------------------------------

    /**
     * Delete Cache on Update
     */
    public function clear_cache()
    {
        $cache_names = [
            'countries_all'
        ];
    	// cache name without prefix
        foreach($cache_names as $cache)
        {
            $this->delete_cache($cache);
        }
        return TRUE;
    }

    // ----------------------------------------------------------------

    /**
     * Log Activity
     *
     * Log activities
     *      Available Activities: Edit
     *
     * @param integer $id
     * @param string $action
     * @return bool
     */
    public function log_activity($id, $action = 'E')
    {
        $action = is_string($action) ? $action : 'E';
        // Save Activity Log
        $activity_log = [
            'module' => 'country',
            'module_id' => $id,
            'action' => $action
        ];
        return $this->activity->save($activity_log);
    }
}