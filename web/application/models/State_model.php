<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class State_model extends MY_Model
{
    protected $table_name = 'master_states';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $audit_log = TRUE;

    protected $protected_attributes = ['id'];

    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ['id', 'country_id', 'code', 'name_en', 'name_np', 'created_at', 'created_by', 'updated_at', 'updated_by'];

    protected $validation_rules = [];

	// --------------------------------------------------------------------

    /**
     * Protect Default Records?
     */
    public static $protect_default = FALSE;
    public static $protect_max_id = 0; // Prevent first 12 records from deletion.

    // --------------------------------------------------------------------


	/**
	 * Constructor
	 *
	 * @return void
	 */
    public function __construct()
    {
        parent::__construct();

        // Load dependent model
        $this->load->model('country_model');

        // Build Validation Rules
        $this->validation_rules();
    }

    // --------------------------------------------------------------------

    /**
     * Set the Validation Rules
     *
     * @return void
     */
    public function validation_rules()
    {
        $country_dropdown = $this->country_model->dropdown('id');
        $this->validation_rules = [
            [
                'field' => 'country_id',
                'label' => 'Country',
                'rules' => 'trim|required|integer|max_length[3]',
                '_type' => 'dropdown',
                '_data' => IQB_BLANK_SELECT + $country_dropdown,
                '_required' => true
            ],
            [
                'field' => 'code',
                'label' => 'State / Province Code',
                'rules' => 'trim|required|alpha_numeric|max_length[3]|callback_check_duplicate',
                '_type' => 'text',
                '_required' => true
            ],
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
                'rules' => 'trim|max_length[80]',
                '_type' => 'text',
                '_required' => true
            ]
        ];
    }

    // --------------------------------------------------------------------

    /**
     * Get Data Rows
     *
     * Get the filtered result-set for listing
     *
     * @param array $params
     * @return mixed
     */
    public function rows($params = array())
    {
        $this->db->select('S.*, C.name AS country_name')
                             ->from($this->table_name . ' S')
                             ->join('master_countries C', 'C.id = S.country_id');

        if(!empty($params))
        {
            $next_id = $params['next_id'] ?? NULL;
            if( $next_id )
            {
                $this->db->where(['S.id >=' => $next_id]);
            }

            $country_id = $params['country_id'] ?? NULL;
            if( $country_id )
            {
                $this->db->where(['S.country_id' => $country_id]);
            }

            $code = $params['code'] ?? NULL;
            if( $code )
            {
                $this->db->where(['S.code' =>  $code]);
            }

            $keywords = $params['keywords'] ?? '';
            if( $keywords )
            {
                $this->db->like('S.name_en', $keywords, 'after');
                $this->db->or_like('S.name_np', $keywords, 'after');
            }
        }
        return $this->db->limit($this->settings->per_page+1)
                    ->get()->result();
    }

    // --------------------------------------------------------------------

    /**
     * Get a Single Record
     *
     * @param int $id
     * @return mixed
     */
    public function get($id)
    {
        return $this->db->select('S.*, C.name AS country_name')
                             ->from($this->table_name . ' S')
                             ->join('master_countries C', 'C.id = S.country_id')
                             ->where('S.id', $id)
                             ->get()->row();
    }

    // --------------------------------------------------------------------

    public function get_by_country($country_id)
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_name = 'state_' . $country_id;
        $list       = $this->get_cache($cache_name);
        if(!$list)
        {
            $list = $this->db->select('S.*, C.name AS country_name')
                             ->from($this->table_name . ' S')
                             ->join('master_countries C', 'C.id = S.country_id')
                             ->where('S.country_id', $country_id)
                             ->get()->result();

            $this->write_cache($list, $cache_name, CACHE_DURATION_DAY);
        }
        return $list;
    }

    // ----------------------------------------------------------------

    /**
     * Get Dropdown List
     *
     * @param int $country_id
     * @param string $origin  Source of Request [main|api]
     * @return array
     */
    public function dropdown($country_id, $origin = "main")
    {
        $records = $this->get_by_country($country_id);
        $dropdown = [];
        if($origin == "main")
        {
            foreach($records as $record)
            {
                $column = $record->id;
                $dropdown["{$column}"] = $record->name_en . ' (' . $record->name_np . ')';
            }
        }
        else
        {
            // Build {key:xx, value:xxx} objects
            foreach($records as $record)
            {
                $single         = new stdClass;
                $single->key    = $record->id;
                $single->value  = $record->name_en . ' (' . $record->name_np . ')';
                $dropdown[]     = $single;
            }
        }
        return $dropdown;
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

    public function delete($id = NULL)
    {
        // !!! NOTE !!! DO NOT ALLOW TO DELETE FOR NOW
        return FALSE;

        $id = intval($id);
        if( !safe_to_delete( get_class(), $id ) )
        {
            return FALSE;
        }

        // Disable DB Debug for transaction to work
        $this->db->db_debug = FALSE;
        $status             = TRUE;

        // Use automatic transaction
        $this->db->trans_start();


            parent::delete($id);


        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            // generate an error... or use the log_message() function to log your error
            $status = FALSE;
        }
        // Enable db_debug if on development environment
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;

        // return result/status
        return $status;
    }

    // --------------------------------------------------------------------

    /**
     * Delete Cache on Update/Delete Records
     */
    public function clear_cache()
    {
        $cache_names = [
            'state_*'
        ];

        // cache name without prefix
        $this->delete_cache($cache_names);

        return TRUE;
    }
}