<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Bsrs_heading_model extends MY_Model
{
    protected $table_name = 'bsrs_headings';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    protected $before_insert = [];
    protected $before_update = [];
    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ['id', 'portfolio_id', 'heading_type_id', 'code', 'name', 'created_at', 'created_by', 'updated_at', 'updated_by'];

    protected $validation_rules = [
        [
            'field' => 'heading_id[]',
            'label' => 'Heading ID',
            'rules' => 'trim|integer|max_length[8]',
            '_field'    => 'id',
            '_type'     => 'hidden',
            '_show_label'   => false,
            '_required' => true
        ],
        [
            'field' => 'code[]',
            'label' => 'Beema Samiti Code',
            'rules' => 'trim|required|integer|max_length[4]|callback_check_duplicate_code',
            '_type' => 'text',
            '_field'    => 'code',
            '_show_label'   => false,
            '_required' => true
        ],
        [
            'field' => 'name[]',
            'label' => 'Heading Name',
            'rules' => 'trim|required|max_length[200]|htmlspecialchars',
            '_field' => 'name',
            '_type' => 'text',
            '_show_label'   => false,
            '_required' => true
        ]
    ];


    /**
     * Protect Default Records?
     */
    public static $protect_default = FALSE;
    public static $protect_max_id = 0; // Prevent first 100 records from deletion.

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

    public function by_portfolio_heading_type($portfolio_id, $heading_type_id)
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_var = 'bsrs_hd_pht_' . $portfolio_id . '_' . $heading_type_id;
        $list = $this->get_cache($cache_var);
        if(!$list)
        {
            $where = [
                'portfolio_id' => $portfolio_id,
                'heading_type_id' => $heading_type_id
            ];
            $list = parent::find_many_by($where);
            $this->write_cache($list, $cache_var, CACHE_DURATION_6HRS);
        }
        return $list;
    }

    // ----------------------------------------------------------------

    public function by_portfolio($portfolio_id)
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_var = 'bsrs_hd_p_' . $portfolio_id;
        $list = $this->get_cache($cache_var);
        if(!$list)
        {
            $list = $this->db->select('H.*, HT.name_en AS heading_type_name_en, HT.name_np AS heading_type_name_np')
                             ->from($this->table_name . ' AS H')
                             ->join('bsrs_heading_types HT', 'HT.id = H.heading_type_id')
                             ->where('H.portfolio_id', $portfolio_id)
                             ->order_by('HT.id')
                             ->order_by('H.code')
                             ->get()->result();

            $this->write_cache($list, $cache_var, CACHE_DURATION_6HRS);
        }
        return $list;
    }

    // ----------------------------------------------------------------

    public function get_all()
    {
        // /**
        //  * Get Cached Result, If no, cache the query result
        //  */
        // $list = $this->get_cache('bsrs_hd_all');
        // if(!$list)
        // {
        //     $list = parent::find_all();
        //     $this->write_cache($list, 'bsrs_hd_all', CACHE_DURATION_DAY);
        // }
        // return $list;
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
        // $records = $this->get_all();
        // $list = [];
        // foreach($records as $record)
        // {
        //     $list["{$record->id}"] = $record->name_en . ' (' . $record->name_np . ')';
        // }
        // return $list;
    }

	// --------------------------------------------------------------------

    /**
     * Delete Cache on Update
     */
    public function clear_cache()
    {
        $cache_names = [
            'bsrs_hd_pht_*',
            'bsrs_hd_p_*'
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


        // Enable db_debug if on development environment
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;

        // return result/status
        return $status;
    }
}