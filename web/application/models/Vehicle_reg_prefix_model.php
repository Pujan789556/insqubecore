<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Vehicle_reg_prefix_model extends MY_Model
{
    protected $table_name = 'master_vehicle_reg_prefixes';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $audit_log = TRUE;

    protected $protected_attributes = ['id'];

    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ['id', 'type', 'name_en', 'name_np', 'created_at', 'created_by', 'updated_at', 'updated_by'];

   protected $validation_rules = [
        [
            'field' => 'type',
            'label' => 'Prefix Type',
            'rules' => 'trim|required|integer|exact_length[1]|in_list[1,2,3]',
            '_type'     => 'dropdown',
            '_data'     => [ '' => 'Select...', '1' => 'Old', '2' => 'New 4-Wheeler', '3' => 'New 2-Wheeler'],
            '_required' => true
        ],
        [
            'field' => 'name_en',
            'label' => 'Name (EN)',
            'rules' => 'trim|required|max_length[80]|callback_check_duplicate_en',
            '_type'     => 'text',
            '_required' => true
        ],
        [
            'field' => 'name_np',
            'label' => 'Name (NP)',
            'rules' => 'trim|required|max_length[80]|callback_check_duplicate_np',
            '_type'     => 'text',
            '_required' => true
        ]
    ];


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
    }

    // ----------------------------------------------------------------

    /**
     * Get Data Rows
     *
     * Get the filtered resulte set for listing purpose
     *
     * @param array $params
     * @return type
     */
    public function rows($params = array())
    {
        $this->db->select('V.*')
                 ->from($this->table_name . ' as V');


        if(!empty($params))
        {
            $next_id = $params['next_id'] ?? NULL;
            if( $next_id )
            {
                $this->db->where(['V.id >=' => $next_id]);
            }


            $type = $params['type'] ?? NULL;
            if( $type )
            {
                $this->db->where(['V.type' =>  $type]);
            }


            $keywords = $params['keywords'] ?? '';
            if( $keywords )
            {
                $this->db->like('V.name_en', $keywords, 'after');
            }
        }
        return $this->db->limit($this->settings->per_page+1)
                    ->get()->result();
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

    public function lookup($keywords)
    {
        // remove whitespace from start and end if any
        $keywords = trim($keywords);

        // remove non-alphanumeric characters, but retain white space
        $keywords = preg_replace("/[^A-Za-z0-9 ]/", '', $keywords);

        // Prepare Cache Key
        $key = preg_replace('/\s+/', '', $keywords); // remove all whitespaces for
        $cache_key = 'vrp_' . $key;

        /**
         * Get Cached Result, If no, cache the query result
         */
        $list = $this->get_cache($cache_key);
        if(!$list)
        {
            $records = $this->db->select('VRP.id, VRP.name_en, VRP.name_np')
                         ->from($this->table_name . ' VRP')
                         ->like('VRP.name_en', $keywords, 'after')
                         ->limit(20)
                         ->get()
                         ->result();

            $list = [];
            foreach($records as $single)
            {
                $list[] = ['key' => $single->id, 'value' => $single->name_en];
            }
            $list ? $this->write_cache($list, $cache_key, CACHE_DURATION_HALF_HR) : '';
        }
        return $list;
    }


    // ----------------------------------------------------------------

    public function exists($where, $id=NULL)
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
     * Delete Cache on Update
     */
    public function clear_cache()
    {
        $cache_names = [
            'vrp_*'
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

            // Delete Primary Record
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
}